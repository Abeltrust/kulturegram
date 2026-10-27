<?php

// app/Http/Controllers/LiveStreamController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LiveStream;
use Illuminate\Support\Facades\Auth;

class LiveStreamController extends Controller
{
    public function index()
    {
        $liveStreams = LiveStream::where('is_live', true)
            ->whereNull('completed_at')
            ->latest()
            ->get();

        $endedStreams = LiveStream::where('is_live', false)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subHours(12))
            ->latest('completed_at')
            ->get();

        $upcomingStreams = LiveStream::whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now())
            ->whereNull('completed_at')
            ->latest('scheduled_at')
            ->get();

        return view('live-streaming.index', compact('liveStreams', 'upcomingStreams', 'endedStreams'));
    }

    public function show(LiveStream $stream)
    {
        // Viewers enter the room. 
        // A full implementation would check authorization, handle recording, etc.
        return view('live-streaming.show', compact('stream'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:50',
            'thumbnail' => 'nullable|image|max:2048',
            'scheduled_at' => 'nullable|date|after_or_equal:now',
        ]);

        $isScheduled = $request->filled('scheduled_at');

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('stream-thumbnails', 'public');
        }

        $stream = LiveStream::create([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'user_id' => Auth::id(),
            'thumbnail' => $thumbnailPath,
            'scheduled_at' => $request->scheduled_at,
            'is_live' => !$isScheduled, // If scheduled, it's not live yet.
        ]);

        if ($isScheduled) {
            return redirect()->route('live-stream.index')->with('success', 'Live stream scheduled successfully!');
        }

        return redirect()->route('live-stream.show', $stream)->with('success', 'You are now live!');
    }

    public function end(LiveStream $stream)
    {
        if ($stream->user_id !== Auth::id()) {
            abort(403);
        }
        $stream->is_live = false;
        $stream->completed_at = now();
        $stream->save();

        return redirect()->route('live-stream.index')->with('success', 'Live stream ended.');
    }

    /**
     * End stream via AJAX/Beacon when user leaves page
     */
    public function ajaxEnd(LiveStream $stream)
    {
        if ($stream->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($stream->is_live) {
            $stream->is_live = false;
            $stream->completed_at = now();
            $stream->save();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get live stream messages (clean messages for public, all messages if moderator/host)
     */
    public function getMessages(LiveStream $stream, Request $request)
    {
        $sinceId = $request->query('since_id', 0);
        $userId = Auth::id();
        $isHostOrMod = $userId && ($userId === $stream->user_id || Auth::user()->is_admin);

        $query = \App\Models\LiveStreamMessage::where('live_stream_id', $stream->id)
            ->with('user:id,name,username,avatar')
            ->orderBy('id', 'asc');

        if ($sinceId > 0) {
            $query->where('id', '>', $sinceId);
        }

        if (!$isHostOrMod) {
            // Viewers see clean messages + their own pending messages
            $query->where(function ($q) use ($userId) {
                $q->whereIn('flag_status', ['clean', 'dismissed']);
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
            });
        }

        $messages = $query->limit(50)->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'last_id' => $messages->last()?->id ?? $sinceId,
        ]);
    }

    /**
     * Post a new message with non-blocking AI moderation
     */
    public function postMessage(
        LiveStream $stream,
        Request $request,
        \App\Services\MetaAI\LiveStreamModerationService $moderationService
    ) {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = $moderationService->processMessage(
            $stream,
            Auth::user(),
            $request->input('message')
        );

        $message->load('user:id,name,username,avatar');

        return response()->json([
            'success' => true,
            'message' => $message,
            'moderation' => [
                'flagged' => (bool) ($message->is_flagged || $message->flag_status === 'flagged'),
                'category' => $message->flag_category,
                'reason' => $message->flag_reason,
                'rule' => $message->flag_rule,
            ],
        ]);
    }

    /**
     * Get queue of flagged messages for stream host or moderator
     */
    public function getFlaggedMessages(LiveStream $stream)
    {
        $userId = Auth::id();
        if (!$userId || ($userId !== $stream->user_id && !Auth::user()->is_admin)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $flagged = \App\Models\LiveStreamMessage::where('live_stream_id', $stream->id)
            ->where('flag_status', 'flagged')
            ->with('user:id,name,username,avatar')
            ->latest()
            ->get();

        $stats = [
            'total_messages' => \App\Models\LiveStreamMessage::where('live_stream_id', $stream->id)->count(),
            'flagged_count' => $flagged->count(),
            'dismissed_count' => \App\Models\LiveStreamMessage::where('live_stream_id', $stream->id)->where('flag_status', 'dismissed')->count(),
            'removed_count' => \App\Models\LiveStreamMessage::where('live_stream_id', $stream->id)->where('flag_status', 'removed')->count(),
        ];

        return response()->json([
            'success' => true,
            'flagged_messages' => $flagged,
            'stats' => $stats,
        ]);
    }

    /**
     * Dismiss an AI flag on a message (reinstate message)
     */
    public function dismissFlag(
        LiveStream $stream,
        \App\Models\LiveStreamMessage $message,
        Request $request,
        \App\Services\MetaAI\LiveStreamModerationService $moderationService
    ) {
        $userId = Auth::id();
        if (!$userId || ($userId !== $stream->user_id && !Auth::user()->is_admin)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $success = $moderationService->dismissFlag($message, Auth::user(), $request->input('notes'));

        return response()->json([
            'success' => $success,
            'message' => $message->fresh(),
        ]);
    }

    /**
     * Remove a flagged message permanently from chat view
     */
    public function removeMessage(
        LiveStream $stream,
        \App\Models\LiveStreamMessage $message,
        Request $request,
        \App\Services\MetaAI\LiveStreamModerationService $moderationService
    ) {
        $userId = Auth::id();
        if (!$userId || ($userId !== $stream->user_id && !Auth::user()->is_admin)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $success = $moderationService->removeMessage($message, Auth::user(), $request->input('reason'));

        return response()->json([
            'success' => $success,
            'message' => $message->fresh(),
        ]);
    }
}
