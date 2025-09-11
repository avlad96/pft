<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCommentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_it_returns_user_comments_for_active_posts(): void
    {
        $activePost = Post::factory()->for($this->user)->create();
        $inactivePost = Post::factory()->for($this->user)->create(['status' => 0]);
        $someUser = User::factory()->create();

        $commentActivePost = Comment::factory()->for($this->user)->for($activePost, 'commentable')->create();

        Comment::factory()->for($this->user)->for($inactivePost, 'commentable')->create();

        $commentFromAnotherUser = Comment::factory()->for($someUser)->for($activePost, 'commentable')->create();
        $reply = Comment::factory()->for($this->user)->for($commentFromAnotherUser, 'commentable')->create();

        $response = $this->getJson("/api/users/{$this->user->id}/comments");

        $response->assertOk()
            ->assertJsonFragment(['id' => $commentActivePost->id])
            ->assertJsonFragment(['id' => $reply->id])
            ->assertJsonMissing(['id' => $inactivePost->id]);
    }

    public function authenticated_user_can_get_their_own_comments(): void
    {
        $this->actingAs($this->user);
        $someUser = User::factory()->create();
        $post = Post::factory()->for($this->user)->create();

        $comment1 = Comment::factory()->for($this->user)->for($post, 'commentable')->create();
        $comment2 = Comment::factory()->for($this->user)->for($post, 'commentable')->create();

        $commentFromAnotherUser = Comment::factory()->for($someUser)->for($post, 'commentable')->create();

        $response = $this->getJson("/api/user/comments");

        $response->assertOk()
            ->assertJsonFragment(['id' => $comment1->id])
            ->assertJsonFragment(['id' => $comment2->id])
            ->assertJsonMissing(['id' => $commentFromAnotherUser->id]);
    }

    public function test_unauthenticated_user_cannot_access_their_comments(): void
    {
        $response = $this->getJson("/api/user/comments");

        $response->assertUnauthorized();
    }
}
