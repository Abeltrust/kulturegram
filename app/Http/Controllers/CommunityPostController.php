<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityPost;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommunityPostController extends Controller
{
    /**
     * Store a new post inside a community.
     */
    public function store(Request $request, Community $community)
    {
        $request->validate([
            'content' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif',
            'video' => 'nullable|mimes:mp4,mov,avi,webm|max:10240',
            'audio' => 'nullable|mimes:mp3,wav,ogg|max:5120',
        ]);

        $post = new CommunityPost();
        $post->user_id = Auth::id();
        $post->community_id = $community->id;
        $post->content = $request->input('content');

        // Handle media uploads
        if ($request->hasFile('image')) {
            $post->image = $request->file('image')->store('posts/images', 'public');
        }
        if ($request->hasFile('video')) {
            $post->video = $request->file('video')->store('posts/videos', 'public');
        }
        if ($request->hasFile('audio')) {
            $post->audio = $request->file('audio')->store('posts/audio', 'public');
        }

        $post->save();

        return redirect()
            ->route('communities.show', $community->id)
            ->with('success', 'Post created successfully!');
    }


    /**
     * Retrieve post details for editing.
     */
    public function edit(Community $community, CommunityPost $post)
    {
        if ((int)$post->user_id !== (int)Auth::id() && !Auth::user()->is_admin && (int)$community->user_id !== (int)Auth::id()) {
            abort(403, 'You are not allowed to edit this post.');
        }

        return response()->json([
            'success' => true,
            'post' => [
                'id' => $post->id,
                'content' => $post->content,
                'community_id' => $community->id,
                'image_url' => $post->image ? asset('storage/' . $post->image) : null,
                'video_url' => $post->video ? asset('storage/' . $post->video) : null,
                'audio_url' => $post->audio ? asset('storage/' . $post->audio) : null,
            ]
        ]);
    }

    /**
     * Update a community post (owner, community creator, or admin).
     */
    public function update(Request $request, Community $community, CommunityPost $post)
    {
        if ((int)$post->user_id !== (int)Auth::id() && !Auth::user()->is_admin && (int)$community->user_id !== (int)Auth::id()) {
            abort(403, 'You are not allowed to update this post.');
        }

        $request->validate([
            'content' => 'required|string|max:2000',
            'remove_image' => 'nullable|boolean',
            'remove_video' => 'nullable|boolean',
            'remove_audio' => 'nullable|boolean',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:20480',
        ]);

        $post->content = $request->input('content');

        // Handle Media Removal
        if ($request->boolean('remove_image') && $post->image) {
            Storage::disk('public')->delete($post->image);
            $post->image = null;
        }
        if ($request->boolean('remove_video') && $post->video) {
            Storage::disk('public')->delete($post->video);
            $post->video = null;
        }
        if ($request->boolean('remove_audio') && $post->audio) {
            Storage::disk('public')->delete($post->audio);
            $post->audio = null;
        }

        // Handle Media Replacement/Upload
        if ($request->hasFile('image')) {
            if ($post->image) Storage::disk('public')->delete($post->image);
            $post->image = $request->file('image')->store('community_posts', 'public');
        } elseif ($request->hasFile('video')) {
            if ($post->video) Storage::disk('public')->delete($post->video);
            $post->video = $request->file('video')->store('community_posts', 'public');
        } elseif ($request->hasFile('audio')) {
            if ($post->audio) Storage::disk('public')->delete($post->audio);
            $post->audio = $request->file('audio')->store('community_posts', 'public');
        }

        $post->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully!',
                'post' => [
                    'id' => $post->id,
                    'content' => $post->content,
                    'image' => $post->image ? asset('storage/' . $post->image) : null,
                    'video' => $post->video ? asset('storage/' . $post->video) : null,
                    'audio' => $post->audio ? asset('storage/' . $post->audio) : null,
                ]
            ]);
        }

        return redirect()
            ->route('communities.show', $community->id)
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Delete a community post (owner, community creator, or admin).
     */
    public function destroy(Request $request, Community $community, CommunityPost $post)
    {
        if ((int)$post->user_id !== (int)Auth::id() && !Auth::user()->is_admin && (int)$community->user_id !== (int)Auth::id()) {
            abort(403, 'You are not allowed to delete this post.');
        }

        // Delete media files if exist
        foreach (['image', 'video', 'audio'] as $media) {
            if ($post->$media) {
                Storage::disk('public')->delete($post->$media);
            }
        }

        $postId = $post->id;
        $post->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully.',
                'post_id' => $postId,
            ]);
        }

        return redirect()
            ->route('communities.show', $community->id)
            ->with('success', 'Post deleted.');
    }

    /**
     * Store a new comment on a post (supports replies).
     */
    public function comment(Request $request, CommunityPost $post)
    {
        $request->validate([
            'body' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $post->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->body,
            'parent_id' => $request->parent_id,
        ]);

        return back()->with('success', 'Comment added successfully!');
    }

    /**
     * Delete a comment (only owner).
     */
    public function deleteComment(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            abort(403, 'You are not allowed to delete this comment.');
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }

    /**
     * Store a new comment via AJAX.
     */
    public function commentAjax(Request $request, CommunityPost $post)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
            'parent_id' => $request->parent_id,
        ]);

        return response()->json([
            'success' => true,
            'content' => $comment->content,
            'user' => [
                'name' => Auth::user()->name,
                'profile_photo_url' => Auth::user()->getProfilePhotoUrlAttribute(),
            ]
        ]);
    }

    /**
     * Tap a community post via AJAX.
     */
    public function tap(Request $request, CommunityPost $post)
    {
        $tap = $post->taps()->where('user_id', Auth::id())->first();

        if ($tap) {
            $tap->delete();
            $tapped = false;
        } else {
            $post->taps()->create(['user_id' => Auth::id()]);
            $tapped = true;
        }

        return response()->json([
            'tapped' => $tapped,
            'count' => $post->taps()->count()
        ]);
    }
}
