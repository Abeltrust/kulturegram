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
        $conversations = Auth::user()->conversations()
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
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $messages = $conversation->messages()
            ->with('user')
            ->latest()
            ->limit(100)
            ->get()
            ->reverse();

        // Mark as read
        $conversation->messages()
            ->where('user_id', '!=', Auth::id())
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);

        return view('messages.show', [
            'selectedConversation' => $conversation,
            'messages' => $messages,
            'activeUsers' => $this->getActiveUsers(),
        ]);
    }

    protected function getActiveUsers()
    {
        return User::where('id', '!=', Auth::id())
            ->get()
            ->map(function ($user) {
                $lastMsg = Message::where(function ($q) use ($user) {
                    $q->where('user_id', Auth::id())->whereHas('conversation', function ($c) use ($user) {
                        $c->whereHas('participants', function ($p) use ($user) {
                            $p->where('user_id', $user->id);
                        });
                    });
                })->orWhere(function ($q) use ($user) {
                    $q->where('user_id', $user->id)->whereHas('conversation', function ($c) {
                        $c->whereHas('participants', function ($p) {
                            $p->where('user_id', Auth::id());
                        });
                    });
                })
                    ->latest()
                    ->first();

                return (object) [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'profile_photo_url' => $user->profile_photo_url,
                    'status' => $user->is_online ? 'online' : 'offline',
                    'last_seen' => $user->last_seen,
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

        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $content = trim((string) $request->input('content'));
        if ($content === '' && !$request->hasFile('attachment')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Message cannot be empty'], 422);
            }
            return redirect()->back();
        }

        $message = $conversation->messages()->create([
            'user_id' => Auth::id(),
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
                    'user_name' => Auth::user()->name,
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
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $afterId = (int) $request->query('after_id', 0);

        $newMessages = $conversation->messages()
            ->where('id', '>', $afterId)
            ->with('user')
            ->orderBy('id', 'asc')
            ->get();

        // Mark incoming messages as read
        $conversation->messages()
            ->where('id', '>', $afterId)
            ->where('user_id', '!=', Auth::id())
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);

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
        $existingConversation = Auth::user()->conversations()
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('type', 'direct')
            ->first();

        if ($existingConversation) {
            return redirect()->route('messages.show', $existingConversation);
        }

        $conversation = Conversation::create(['type' => 'direct']);
        $conversation->participants()->attach([Auth::id(), $user->id]);

        return redirect()->route('messages.show', $conversation);
    }
}
