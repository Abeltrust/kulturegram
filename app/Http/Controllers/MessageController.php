<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $lockedInIds = $user->lockedInUserIds();

        $conversations = $user->conversations()
            ->whereHas('participants', function ($q) use ($lockedInIds, $user) {
                $q->where('user_id', '!=', $user->id)
                  ->whereIn('user_id', $lockedInIds);
            })
            ->with(['participants', 'latestMessage.user'])
            ->latest('updated_at')
            ->get();

        return view('messages.index', [
            'conversations' => $conversations,
            'activeUsers' => $this->getActiveUsers(),
        ]);
    }

    public function show(Conversation $conversation)
    {
        $user = Auth::user();
        $lockedInIds = $user->lockedInUserIds();

        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $otherUser = $conversation->participants()->where('user_id', '!=', $user->id)->first();
        if ($otherUser && !in_array((int) $otherUser->id, $lockedInIds, true)) {
            return redirect()->route('messages.index')
                ->with('error', "You can only interact with members you are Locked-In with. Please Lock-In to {$otherUser->name} first.");
        }

        $messages = $conversation->messages()
            ->with('user')
            ->latest()
            ->limit(100)
            ->get()
            ->reverse();

        // Mark as read
        $conversation->messages()
            ->where('user_id', '!=', $user->id)
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);

        return view('messages.show', [
            'selectedConversation' => $conversation,
            'messages' => $messages,
            'activeUsers' => $this->getActiveUsers(),
            'otherUser' => $otherUser,
        ]);
    }

    protected function getActiveUsers()
    {
        $user = Auth::user();
        $lockedInIds = $user->lockedInUserIds();

        if (empty($lockedInIds)) {
            return collect();
        }

        return User::whereIn('id', $lockedInIds)
            ->get()
            ->map(function ($targetUser) use ($user) {
                $lastMsg = Message::where(function ($q) use ($targetUser, $user) {
                    $q->where('user_id', $user->id)->whereHas('conversation', function ($c) use ($targetUser) {
                        $c->whereHas('participants', function ($p) use ($targetUser) {
                            $p->where('user_id', $targetUser->id);
                        });
                    });
                })->orWhere(function ($q) use ($targetUser, $user) {
                    $q->where('user_id', $targetUser->id)->whereHas('conversation', function ($c) use ($user) {
                        $c->whereHas('participants', function ($p) use ($user) {
                            $p->where('user_id', $user->id);
                        });
                    });
                })
                    ->latest()
                    ->first();

                return (object) [
                    'id' => $targetUser->id,
                    'name' => $targetUser->name,
                    'username' => $targetUser->username,
                    'avatar' => $targetUser->avatar,
                    'profile_photo_url' => $targetUser->profile_photo_url,
                    'status' => $targetUser->is_online ? 'online' : 'offline',
                    'last_seen' => $targetUser->last_seen,
                    'last_message' => $lastMsg?->content ?? '',
                    'last_status' => $lastMsg?->status ?? null,
                ];
            });
    }

    public function store(Request $request, Conversation $conversation)
    {
        $request->validate([
            'content' => 'nullable|string|max:1000',
            'attachment.*' => 'nullable|file|max:51200',
        ]);

        $user = Auth::user();

        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $otherUser = $conversation->participants()->where('user_id', '!=', $user->id)->first();
        if ($otherUser && !$user->isLockedInWith($otherUser)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => "You can only interact with members you are Locked-In with. Please Lock-In to {$otherUser->name} first.",
                ], 403);
            }
            return redirect()->back()
                ->with('error', "You can only interact with members you are Locked-In with.");
        }

        $content = trim((string) $request->input('content'));
        if ($content === '' && !$request->hasFile('attachment')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Message cannot be empty'], 422);
            }
            return redirect()->back();
        }

        $message = $conversation->messages()->create([
            'user_id' => $user->id,
            'content' => $content,
            'status' => 'sent',
        ]);

        if ($request->hasFile('attachment')) {
            foreach ($request->file('attachment') as $file) {
                $path = $file->store('messages/' . $conversation->id, 'private');

                $message->update([
                    'attachment' => $path,
                    'type' => $file->getClientMimeType(),
                ]);
            }
        }

        $conversation->touch();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'user_id' => $message->user_id,
                    'user_name' => $user->name,
                    'is_me' => true,
                    'time' => $message->created_at->format('H:i'),
                    'full_time' => $message->created_at->format('M d, H:i'),
                    'status' => $message->status,
                ],
            ]);
        }

        return redirect()->back();
    }

    public function updates(Conversation $conversation, Request $request)
    {
        $user = Auth::user();

        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $otherUser = $conversation->participants()->where('user_id', '!=', $user->id)->first();
        if ($otherUser && !$user->isLockedInWith($otherUser)) {
            return response()->json(['messages' => []]);
        }

        $afterId = (int) $request->query('after_id', 0);

        $newMessages = $conversation->messages()
            ->where('id', '>', $afterId)
            ->with('user:id,name,username,avatar')
            ->orderBy('id', 'asc')
            ->get();

        if ($newMessages->isEmpty()) {
            return response()->json(['messages' => []]);
        }

        // Mark incoming messages as read ONLY when unread messages from counterparty exist
        $unreadIncomingIds = $newMessages->where('user_id', '!=', $user->id)
            ->where('status', '!=', 'read')
            ->pluck('id');

        if ($unreadIncomingIds->isNotEmpty()) {
            $conversation->messages()
                ->whereIn('id', $unreadIncomingIds)
                ->update(['status' => 'read']);
        }

        return response()->json([
            'messages' => $newMessages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'content' => $msg->content,
                    'user_id' => $msg->user_id,
                    'user_name' => $msg->user->name,
                    'is_me' => $msg->user_id === Auth::id(),
                    'time' => $msg->created_at->format('H:i'),
                    'full_time' => $msg->created_at->format('M d, H:i'),
                    'status' => $msg->status,
                ];
            }),
        ]);
    }

    public function startConversation(User $user)
    {
        $authUser = Auth::user();

        if ($authUser->id === $user->id) {
            return redirect()->route('messages.index')
                ->with('error', 'You cannot message yourself.');
        }

        if (!$authUser->isLockedInWith($user)) {
            return redirect()->route('profile.user', $user)
                ->with('error', "You can only interact in messages with members you are Locked-In with. Click 'Lock In' to connect first.");
        }

        $existingConversation = $authUser->conversations()
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('type', 'direct')
            ->first();

        if ($existingConversation) {
            return redirect()->route('messages.show', $existingConversation);
        }

        $conversation = Conversation::create(['type' => 'direct']);
        $conversation->participants()->attach([$authUser->id, $user->id]);

        return redirect()->route('messages.show', $conversation);
    }
}
