<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MediaComment;
use Illuminate\Support\Facades\Auth;

class MediaCommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'media_id' => 'required|integer',
            'media_type' => 'required|string|in:movie,tv',
            'media_title' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:media_comments,id'
        ]);

        MediaComment::create([
            'user_id' => Auth::id(),
            'media_id' => $request->media_id,
            'media_type' => $request->media_type,
            'media_title' => $request->media_title,
            'content' => $request->content,
            'parent_id' => $request->parent_id
        ]);

        return back()->with('success', 'Comment posted successfully!');
    }

    public function update(Request $request, MediaComment $comment)
    {
        if (Auth::id() !== $comment->user_id) {
            abort(403);
        }

        $request->validate(['content' => 'required|string|max:1000']);
        $comment->update(['content' => $request->content]);

        return back()->with('success', 'Comment updated successfully!');
    }

    public function destroy(MediaComment $comment)
    {
        if (Auth::id() !== $comment->user_id && !in_array(Auth::user()->role, ['admin', 'superadmin'])) {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted successfully!');
    }

    public function toggleReaction(\Illuminate\Http\Request $request, MediaComment $comment)
    {
        $isDislike = $request->boolean('is_dislike', false);
        $userId = Auth::id();
        $existingReaction = \App\Models\CommentLike::where('user_id', $userId)
            ->where('media_comment_id', $comment->id)
            ->first();

        if ($existingReaction) {
            if ($existingReaction->is_dislike == $isDislike){
                $existingReaction->delete();
                $existingReaction = null;
            } else {
                // User swapped their reaction (e.g., from like to dislike)
                $existingReaction->update(['is_dislike' => $isDislike]);
            }
        } else {
            $existingReaction = \App\Models\CommentLike::create([
                'user_id' => $userId,
                'media_comment_id' => $comment->id,
                'is_dislike' => $isDislike
            ]);
        }
        // Return the updated counts and the user's current status
        return response()->json([
            'likes_count' => $comment->likes()->where('is_dislike', false)->count(),
            'dislikes_count' => $comment->likes()->where('is_dislike', true)->count(),
            'user_reaction' => $existingReaction ? ($existingReaction->is_dislike ? 'dislike' : 'like') : null
        ]);
    }
}
