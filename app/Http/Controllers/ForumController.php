<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Thread;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ForumController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('threads')->get();
        $recentThreads = Thread::with(['user', 'category'])->latest()->take(10)->get();
        return view('forum.index', compact('categories', 'recentThreads'));
    }

    public function category($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $threads = $category->threads()->with(['user'])->withCount('posts')->latest()->paginate(15);
        return view('forum.category', compact('category', 'threads'));
    }

    public function thread($slug)
    {
        $thread = Thread::where('slug', $slug)->with(['user', 'category', 'posts.user'])->firstOrFail();

        // Increment views only once per session to prevent spam from refreshes and crawlers
        $sessionKey = 'viewed_thread_' . $thread->id;
        if (!session()->has($sessionKey)) {
            $thread->increment('views');
            session()->put($sessionKey, now()->timestamp);
        }

        return view('forum.thread', compact('thread'));
    }

    public function createThread()
    {
        $categories = Category::all();
        return view('forum.create_thread', compact('categories'));
    }

    public function storeThread(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string|min:5|max:10000',
        ]);

        $slug = Str::slug($request->title) . '-' . uniqid();

        $thread = Thread::create([
            'category_id' => $request->category_id,
            'user_id' => Auth::id(),
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
        ]);

        return redirect()->route('forum.thread', $thread->slug)->with('success', 'Thread created successfully!');
    }

    public function destroyThread(Thread $thread)
    {
        $this->authorize('delete', $thread);
        $thread->delete();

        return redirect()->route('forum.index')->with('success', 'Thread deleted successfully.');
    }

    public function storePost(Request $request, $thread_id)
    {
        $request->validate([
            'content' => 'required|string|min:2|max:5000',
        ]);

        $thread = Thread::findOrFail($thread_id);

        Post::create([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        // Touch the thread to update its updated_at timestamp so it floats to top
        $thread->touch();

        return back()->with('success', 'Reply posted successfully!');
    }

    public function destroyPost(Post $post)
    {
        $this->authorize('delete', $post);
        $post->delete();

        return back()->with('success', 'Reply deleted successfully.');
    }
}
