<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Media;
use App\Models\MediaComment;
use App\Models\CommentLike;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class MediaCommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'media_id' => 'required|integer',
            'media_type' => 'required|string|in:movie,tv',
            'content' => 'required|string|min:1|max:1000',
            'parent_id' => 'nullable|exists:media_comments,id',
        ]);

        // Resolve media title server-side to prevent user spoofing
        $mediaTitle = null;
        $localMedia = Media::where('tmdb_id', $request->media_id)
            ->where('media_type', $request->media_type)
            ->first();

        if ($localMedia && !empty($localMedia->title)) {
            $mediaTitle = $localMedia->title;
        } else {
            try {
                $response = Http::withToken(config('services.tmdb.token'))
                    ->timeout(4)
                    ->get("https://api.themoviedb.org/3/{$request->media_type}/{$request->media_id}");

                if ($response->successful()) {
                    $mediaData = $response->json();
                    $mediaTitle = $request->media_type === 'movie'
                        ? ($mediaData['title'] ?? null)
                        : ($mediaData['name'] ?? null);
                }
            } catch (\Throwable $e) {
                // If API is down, fallback safely below
            }
        }

        if (!$mediaTitle) {
            $mediaTitle = $request->filled('media_title')
                ? strip_tags($request->input('media_title'))
                : ucfirst($request->media_type) . ' #' . $request->media_id;
        }

        MediaComment::create([
            'user_id' => Auth::id(),
            'media_id' => $request->media_id,
            'media_type' => $request->media_type,
            'media_title' => $mediaTitle,
            'content' => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        return back()->with('success', 'Comment posted successfully!');
    }

    public function update(Request $request, MediaComment $comment)
    {
        $this->authorize('update', $comment);

        $request->validate([
            'content' => 'required|string|min:1|max:1000',
        ]);

        $comment->update(['content' => $request->content]);

        return back()->with('success', 'Comment updated successfully!');
    }

    public function destroy(MediaComment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Comment deleted successfully!');
    }

    public function toggleReaction(Request $request, $commentId)
    {
        $comment = MediaComment::find($commentId);
        if (!$comment) {
            return response()->json(['error' => 'Comment not found or has been deleted.'], 404);
        }

        $isDislike = $request->boolean('is_dislike', false);
        $userId = Auth::id();

        $existingReaction = CommentLike::where('user_id', $userId)
            ->where('media_comment_id', $comment->id)
            ->first();

        if ($existingReaction) {
            if ($existingReaction->is_dislike == $isDislike) {
                $existingReaction->delete();
                $existingReaction = null;
            } else {
                $existingReaction->update(['is_dislike' => $isDislike]);
            }
        } else {
            $existingReaction = CommentLike::create([
                'user_id' => $userId,
                'media_comment_id' => $comment->id,
                'is_dislike' => $isDislike,
            ]);
        }

        return response()->json([
            'likes_count' => $comment->likes()->where('is_dislike', false)->count(),
            'dislikes_count' => $comment->likes()->where('is_dislike', true)->count(),
            'user_reaction' => $existingReaction ? ($existingReaction->is_dislike ? 'dislike' : 'like') : null,
        ]);
    }
}
