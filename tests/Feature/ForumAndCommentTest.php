<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MediaComment;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForumAndCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_forum_thread_and_reply()
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'General',
            'slug' => 'general',
            'description' => 'General discussion',
        ]);

        $response = $this->actingAs($user)->post('/forum/store/thread', [
            'category_id' => $category->id,
            'title' => 'Favorite Movies of 2026',
            'content' => 'What is your top movie this year?',
        ]);

        $thread = Thread::first();
        $this->assertNotNull($thread);
        $this->assertEquals('Favorite Movies of 2026', $thread->title);

        $replyResponse = $this->actingAs($user)->post("/forum/thread/{$thread->id}/reply", [
            'content' => 'My favorite is Dune Part 2.',
        ]);

        $replyResponse->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'content' => 'My favorite is Dune Part 2.',
        ]);
    }

    public function test_user_cannot_delete_other_users_thread_unless_admin()
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $category = Category::create([
            'name' => 'General',
            'slug' => 'general',
        ]);

        $thread = Thread::create([
            'category_id' => $category->id,
            'user_id' => $author->id,
            'title' => 'Author Thread',
            'slug' => 'author-thread',
            'content' => 'Thread content here.',
        ]);

        // Other regular user cannot delete
        $response = $this->actingAs($otherUser)->delete("/forum/thread/{$thread->id}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('threads', ['id' => $thread->id]);

        // Admin can delete
        $adminResponse = $this->actingAs($admin)->delete("/forum/thread/{$thread->id}");
        $adminResponse->assertRedirect(route('forum.index'));
        $this->assertDatabaseMissing('threads', ['id' => $thread->id]);
    }

    public function test_user_cannot_update_other_users_comment()
    {
        $author = User::factory()->create();
        $attacker = User::factory()->create();

        $comment = MediaComment::create([
            'user_id' => $author->id,
            'media_id' => 12345,
            'media_type' => 'movie',
            'media_title' => 'Inception',
            'content' => 'Original comment',
        ]);

        $response = $this->actingAs($attacker)->put("/media/comment/{$comment->id}", [
            'content' => 'Tampered content',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('media_comments', [
            'id' => $comment->id,
            'content' => 'Original comment',
        ]);
    }

    public function test_admin_can_delete_user_comment_but_regular_user_cannot()
    {
        $author = User::factory()->create();
        $regularUser = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $comment = MediaComment::create([
            'user_id' => $author->id,
            'media_id' => 12345,
            'media_type' => 'movie',
            'media_title' => 'Inception',
            'content' => 'Comment to be moderated',
        ]);

        // Regular user cannot delete
        $response = $this->actingAs($regularUser)->delete("/media/comment/{$comment->id}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('media_comments', ['id' => $comment->id]);

        // Admin can delete
        $adminResponse = $this->actingAs($admin)->delete("/media/comment/{$comment->id}");
        $adminResponse->assertRedirect();
        $this->assertDatabaseMissing('media_comments', ['id' => $comment->id]);
    }

    public function test_reaction_returns_404_for_deleted_comment()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/media/comment/99999/react', [
            'is_dislike' => false,
        ]);

        $response->assertStatus(404);
    }
}

