<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');

        // 1. Regular Events Query
        $eventQuery = Event::with(['organizer', 'attendees'])
            ->upcoming()
            ->latest('event_date');

        // 2. Fetch Live Streams for the "Live Now" section
        $liveStreams = \App\Models\LiveStream::where('is_live', true)
            ->whereNull('completed_at')
            ->with('host')
            ->latest()
            ->get();

        // 3. Fetch Upcoming Streams for the "Upcoming Events" listing
        $upcomingStreams = \App\Models\LiveStream::whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now())
            ->whereNull('completed_at')
            ->with('host')
            ->latest('scheduled_at')
            ->get();

        switch ($filter) {
            case 'attending':
                if (Auth::check()) {
                    $eventQuery->whereHas('attendees', function ($q) {
                        $q->where('user_id', Auth::id());
                    });
                }
                break;
            case 'hosting':
                if (Auth::check()) {
                    $eventQuery->where('organizer_id', Auth::id());
                }
                break;
            case 'nearby':
                // Location filtering mock
                break;
        }

        $events = $eventQuery->paginate(12);

        return view('events.index', compact('events', 'liveStreams', 'upcomingStreams', 'filter'));
    }

    public function show(Event $event)
    {
        $event->load(['organizer', 'attendees']);

        return view('events.show', compact('event'));
    }

    public function meet(Event $event)
    {
        if (!$event->virtual_room_id) {
            return redirect()->route('events.show', $event)->with('error', 'This event does not have a virtual classroom.');
        }

        // Authorization: Only host or attendees
        if (Auth::id() !== $event->organizer_id && !$event->attendees->contains(Auth::id())) {
            return redirect()->route('events.show', $event)->with('error', 'You must be registered to enter the virtual classroom.');
        }

        return view('events.meet', compact('event'));
    }

    public function create(Request $request)
    {
        $community_id = $request->query('community_id');
        return view('events.create', compact('community_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date|after:today',
            'event_time' => 'required',
            'location' => 'required|string|max:255',
            'type' => 'required|string|max:50', // More flexible types for Educational Hub
            'max_attendees' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'is_online' => 'boolean',
            'meeting_link' => 'nullable|url',
            'is_hosted' => 'boolean',
            'hosted_content' => 'nullable|string',
            'requirements' => 'nullable|array',
            'image' => 'nullable|image|max:10240',
            'community_id' => 'nullable|exists:communities,id',
            'accepts_contributions' => 'boolean',
        ]);

        $event = new Event($request->except(['image', 'is_hosted', 'accepts_contributions', 'virtual_room_id']));
        $event->organizer_id = Auth::id();
        $event->price = $request->input('price', 0) ?: 0;
        $event->is_hosted = $request->boolean('is_hosted');
        $event->virtual_room_id = $request->input('virtual_room_id');
        $event->accepts_contributions = $request->boolean('accepts_contributions');

        if ($request->hasFile('image')) {
            $event->image = $request->file('image')->store('events', 'public');
        }

        $event->save();

        if ($event->community_id) {
            return redirect()->route('communities.show', $event->community_id)
                ->with('success', 'Community event created successfully!');
        }

        return redirect()->route('events.index')
            ->with('success', 'Event created successfully!');
    }

    public function join(Event $event)
    {
        $user = Auth::user();

        if ($event->isFull()) {
            return back()->with('error', 'This event is already at full capacity.');
        }

        if ($event->attendees()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'You are already registered for this event.');
        }

        $event->attendees()->attach($user->id);
        $event->increment('attendees_count');

        // Create notification for organizer
        if ($event->organizer_id !== $user->id) {
            $event->organizer->notifications()->create([
                'from_user_id' => $user->id,
                'type' => 'event',
                'title' => 'New Event Registration',
                'message' => $user->name . ' registered for ' . $event->title,
                'data' => ['event_id' => $event->id],
            ]);
        }

        return back()->with('success', 'You have successfully registered for ' . $event->title . '!');
    }

    public function leave(Event $event)
    {
        $user = Auth::user();

        $event->attendees()->detach($user->id);
        $event->decrement('attendees_count');

        return back()->with('success', 'You have successfully cancelled your registration.');
    }

    /**
     * Mock Ticket Purchase Logic
     */
    public function purchaseTicket(Request $request, Event $event)
    {
        if ($event->price <= 0) {
            return $this->join($event);
        }

        $user = Auth::user();

        // 1. Create Transaction (Mocking successful payment)
        $transaction = \App\Models\Transaction::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'host_id' => $event->organizer_id,
            'amount' => $event->price,
            'type' => 'ticket',
            'status' => 'completed', // Mocking success
            'reference' => 'TKT-' . strtoupper(Str::random(10)),
        ]);

        // 2. Add as Attendee
        $event->attendees()->attach($user->id);
        $event->increment('attendees_count');

        // 3. Notify Organizer
        $event->organizer->notifications()->create([
            'from_user_id' => $user->id,
            'type' => 'event',
            'title' => 'New Ticket Purchased',
            'message' => $user->name . ' bought a ticket for ' . $event->title,
            'data' => ['event_id' => $event->id, 'transaction_id' => $transaction->id],
        ]);

        return back()->with('success', 'Ticket purchased successfully! See you at ' . $event->title);
    }

    /**
     * Mock Contribution Logic
     */
    public function sendContribution(Request $request, Event $event)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        if (!$event->accepts_contributions) {
            return back()->with('error', 'This event is not accepting contributions.');
        }

        $user = Auth::user();

        // 1. Create Transaction
        $transaction = \App\Models\Transaction::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'host_id' => $event->organizer_id,
            'amount' => $request->amount,
            'type' => 'contribution',
            'status' => 'completed',
            'reference' => 'CON-' . strtoupper(Str::random(10)),
        ]);

        // 2. Notify Organizer
        $event->organizer->notifications()->create([
            'from_user_id' => $user->id,
            'type' => 'event',
            'title' => 'New Contribution Received',
            'message' => $user->name . ' sent a contribution of $' . $request->amount . ' for ' . $event->title,
            'data' => ['event_id' => $event->id, 'transaction_id' => $transaction->id],
        ]);

        return back()->with('success', 'Thank you for your generous contribution!');
    }
}