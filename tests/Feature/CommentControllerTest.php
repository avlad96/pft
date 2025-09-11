<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_it_returns_all_comments_for_a_post_with_replies(): void
    {
        $post = Post::factory()->for($this->user)->create();
        $comment1 = Comment::factory()->for($this->user)->for($post, 'commentable')->create();
        $reply = Comment::factory()->for($this->user)->for($comment1, 'commentable')->create();

        $response = $this->getJson("/api/posts/{$post->id}/comments");

        $response->assertOk()
            ->assertJsonFragment(['id' => $comment1->id])
            ->assertJsonFragment(['id' => $reply->id]);
    }

    public function test_authenticated_user_can_comment_posts(): void
    {
        $this->actingAs($this->user);
        $post = Post::factory()->for($this->user)->create();

        $response = $this->postJson("/api/posts/{$post->id}/comments", ['body' => 'some comment']);

        $response->assertCreated()
            ->assertJsonFragment(['body' => 'some comment']);

        $this->assertDatabaseHas('comments', [
            'user_id' => $this->user->id,
            'body' => 'some comment',
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
        ]);
    }

    public function test_authenticated_user_can_reply_to_comments(): void
    {
        $this->actingAs($this->user);
        $post = Post::factory()->for($this->user)->create();
        $comment = Comment::factory()->for($this->user)->for($post, 'commentable')->create();

        $response = $this->postJson("/api/comments/{$comment->id}/replies", ['body' => 'reply comment']);

        $response->assertCreated()
            ->assertJsonFragment(['body' => 'reply comment']);

        $this->assertDatabaseHas('comments', [
            'user_id' => $this->user->id,
            'body' => 'reply comment',
            'commentable_id' => $comment->id,
            'commentable_type' => Comment::class,
        ]);
    }

    public function test_show_returns_comment_with_user(): void
    {
        $post = Post::factory()->for($this->user)->create();
        $comment = Comment::factory()->for($this->user)->for($post, 'commentable')->create();

        $response = $this->getJson("/api/comments/{$comment->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $comment->id])
            ->assertJsonFragment(['id' => $comment->user->id]);
    }

    public function test_owner_can_update_comment(): void
    {
        $this->actingAs($this->user);
        $post = Post::factory()->for($this->user)->create();
        $comment = Comment::factory()->for($this->user)->for($post, 'commentable')->create(['body' => 'some comment']);

        $response = $this->putJson("/api/comments/{$comment->id}", ['body' => 'updated comment']);

        $response->assertOk()
            ->assertJsonFragment(['body' => 'updated comment']);

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'body' => 'updated comment']);
    }

    public function test_non_owner_cannot_update_comment(): void
    {
        $this->actingAs($this->user);
        $post = Post::factory()->for($this->user)->create();
        $someUser = User::factory()->create();
        $comment = Comment::factory()->for($someUser)->for($post, 'commentable')->create();

        $response = $this->putJson("/api/comments/{$comment->id}", ['body' => 'updated comment']);

        $response->assertForbidden();
    }

    public function test_owner_can_delete_comment(): void
    {
        $this->actingAs($this->user);
        $post = Post::factory()->for($this->user)->create();
        $comment = Comment::factory()->for($this->user)->for($post, 'commentable')->create();

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_non_owner_cannot_delete_comment(): void
    {
        $this->actingAs($this->user);
        $post = Post::factory()->for($this->user)->create();
        $someUser = User::factory()->create();
        $comment = Comment::factory()->for($someUser)->for($post, 'commentable')->create();

        $this->deleteJson("/api/comments/{$comment->id}")->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_it_returns_replies_of_comment(): void
    {
        $post = Post::factory()->for($this->user)->create();
        $comment = Comment::factory()->for($this->user)->for($post, 'commentable')->create();
        $reply1 = Comment::factory()->for($this->user)->for($comment, 'commentable')->create();
        $reply2 = Comment::factory()->for($this->user)->for($comment, 'commentable')->create();

        $response = $this->getJson("/api/comments/{$comment->id}/replies");

        $response->assertOk()
            ->assertJsonFragment(['id' => $reply1->id])
            ->assertJsonFragment(['id' => $reply2->id]);
    }
}
