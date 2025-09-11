<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPostControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_it_returns_all_posts_of_user(): void
    {
        $someUser = User::factory()->create();

        $post1 = Post::factory()->for($this->user)->create();
        $post2 = Post::factory()->for($this->user)->create();
        Post::factory()->create(['user_id' => $someUser->id]);

        $response = $this->getJson("/api/users/{$this->user->id}/posts");

        $response->assertOk()
            ->assertJsonFragment(['id' => $post1->id])
            ->assertJsonFragment(['id' => $post2->id])
            ->assertJsonMissing(['id' => $someUser->posts()->first()->id]);
    }

    public function test_it_returns_only_active_posts_of_user(): void
    {
        $activePost = Post::factory()->for($this->user)->create();
        $inactivePost = Post::factory()->for($this->user)->create(['status' => 0]);

        $response = $this->getJson("/api/users/{$this->user->id}/posts/active");

        $response->assertOk()
            ->assertJsonFragment(['id' => $activePost->id])
            ->assertJsonMissing(['id' => $inactivePost->id]);
    }
}
