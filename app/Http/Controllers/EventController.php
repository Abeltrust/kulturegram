<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request, \App\Services\MetaAI\EventDiscoveryService $discoveryService)
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
                if (Auth::check() && Auth::user()->location) {
                    $eventQuery->where('location', 'like', '%' . Auth::user()->location . '%');
                } elseif ($request->filled('location')) {
                    $eventQuery->where('location', 'like', '%' . $request->location . '%');
                }
                break;
        }

        $events = $eventQuery->paginate(12);

        // 4. AI-Curated Events based on user declared cultural profile
        $recommendedEvents = collect();
        if (Auth::check()) {
            $result = $discoveryService->recommendEvents(Auth::user(), 4);
            $recommendedEvents = collect($result['recommendations'] ?? []);
        }

        return view('events.index', compact('events', 'liveStreams', 'upcomingStreams', 'filter', 'recommendedEvents'));
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
        if ($request->boolean('is_online') && (!$request->filled('location') || $request->location === '')) {
            $request->merge(['location' => 'Online']);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date|after_or_equal:today',
            'event_time' => 'required',
            'location' => 'required|string|max:255',
            'type' => 'required|string|max:50', // More flexible types for Educational Hub
            'max_attendees' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'is_online' => 'boolean',
            'meeting_link' => 'nullable|string|max:500',
            'is_hosted' => 'boolean',
            'hosted_content' => 'nullable|string',
            'requirements' => 'nullable|array',
            'image' => 'nullable|image|max:10240',
            'community_id' => 'nullable|exists:communities,id',
            'accepts_contributions' => 'boolean',
        ]);

        $event = new Event($request->except(['image', 'is_hosted', 'accepts_contributions', 'virtual_room_id', 'meeting_link', 'reach_tags', 'ai_reach_metadata', 'ai_flyer_path']));
        $event->organizer_id = Auth::id();
        $event->price = $request->input('price', 0) ?: 0;
        $event->is_hosted = $request->boolean('is_hosted');
        $event->virtual_room_id = $request->filled('virtual_room_id') ? $request->input('virtual_room_id') : null;
        $event->meeting_link = $request->filled('meeting_link') ? $request->input('meeting_link') : null;
        $event->accepts_contributions = $request->boolean('accepts_contributions');

        if ($request->filled('reach_tags')) {
            $event->reach_tags = is_array($request->input('reach_tags')) 
                ? $request->input('reach_tags') 
                : json_decode($request->input('reach_tags'), true);
        }

        if ($request->filled('ai_reach_metadata')) {
            $event->ai_reach_metadata = is_array($request->input('ai_reach_metadata')) 
                ? $request->input('ai_reach_metadata') 
                : json_decode($request->input('ai_reach_metadata'), true);
        }

        if ($request->hasFile('image')) {
            $event->image = $request->file('image')->store('events', 'public');
        } elseif ($request->filled('ai_flyer_path')) {
            $event->image = $request->input('ai_flyer_path');
        }

        $event->save();

        if ($event->community_id) {
            return redirect()->route('communities.show', $event->community_id)
                ->with('success', 'Community event created successfully!');
        }

        return redirect()->route('events.index')
            ->with('success', 'Event created successfully!');
    }

    /**
     * AI Suggested Cultural Description & Concept Generator
     */
    public function aiSuggestDescription(Request $request, \App\Services\MetaAI\EventDiscoveryService $discoveryService)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'key_points' => 'nullable|string|max:2000',
            'description' => 'nullable|string|max:2000',
            'type' => 'nullable|string|max:100',
            'cultural_focus' => 'nullable|string|max:255',
        ]);

        $res = $discoveryService->suggestDescription($request->all());

        return response()->json($res);
    }

    /**
     * AI Cultural Event Flyer Generator
     */
    public function aiGenerateFlyer(Request $request, \App\Services\MetaAI\MuseImageService $imageService)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:100',
            'cultural_context' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'occasion_label' => 'nullable|string|max:150',
            'event_date' => 'nullable|string|max:150',
            'event_time' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'dress_code' => 'nullable|string|max:255',
            'rsvp' => 'nullable|string|max:255',
            'theme_palette' => 'nullable|string|max:100',
            'subject_photo_data_url' => 'nullable|string',
            'logo_data_url' => 'nullable|string',
            'rendered_flyer_data_url' => 'nullable|string',
            'skip_remote_ai' => 'nullable|boolean',
        ]);

        $details = $request->only([
            'subtitle',
            'occasion_label',
            'event_date',
            'event_time',
            'location',
            'dress_code',
            'rsvp',
            'theme_palette',
            'subject_photo_data_url',
            'logo_data_url',
            'rendered_flyer_data_url',
            'skip_remote_ai',
        ]);

        $res = $imageService->generateFlyer(
            $request->input('title'),
            $request->input('description'),
            $request->input('type', 'Cultural Gathering'),
            $request->input('cultural_context'),
            $details
        );

        return response()->json($res);
    }

    /**
     * AI Cultural Reach Optimizer for Event Organizers
     */
    public function aiOptimize(Request $request, \App\Services\MetaAI\EventDiscoveryService $discoveryService)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'is_online' => 'nullable|boolean',
            'community_id' => 'nullable|integer',
        ]);

        $optimization = $discoveryService->optimizeEvent($request->all());

        return response()->json([
            'success' => $optimization['success'] ?? true,
            'data' => $optimization['suggestions'] ?? $optimization,
        ]);
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

    public function edit(Event $event)
    {
        if (Auth::id() !== $event->organizer_id) {
            abort(403, 'Unauthorized. Only the event organizer can edit this event.');
        }

        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        if (Auth::id() !== $event->organizer_id) {
            abort(403, 'Unauthorized. Only the event organizer can update this event.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'location' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'max_attendees' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'is_online' => 'boolean',
            'meeting_link' => 'nullable|string|max:500',
            'is_hosted' => 'boolean',
            'hosted_content' => 'nullable|string',
            'requirements' => 'nullable|array',
            'image' => 'nullable|image|max:10240',
            'community_id' => 'nullable|exists:communities,id',
            'accepts_contributions' => 'boolean',
        ]);

        $event->fill($request->except(['image', 'is_hosted', 'accepts_contributions']));
        $event->price = $request->input('price', 0) ?: 0;
        $event->is_hosted = $request->boolean('is_hosted');
        $event->accepts_contributions = $request->boolean('accepts_contributions');

        if ($request->hasFile('image')) {
            if ($event->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($event->image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($event->image);
            }
            $event->image = $request->file('image')->store('events', 'public');
        }

        $event->save();

        return redirect()->route('events.show', $event)
            ->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        if (Auth::id() !== $event->organizer_id) {
            abort(403, 'Unauthorized. Only the event organizer can delete this event.');
        }

        if ($event->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($event->image)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Event cancelled and removed successfully.');
    }
}
