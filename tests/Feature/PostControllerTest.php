<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_index_returns_only_active_posts(): void
    {
        $activePost = Post::factory()->for($this->user)->create();
        $inactivePost = Post::factory()->for($this->user)->create(['status' => 0]);

        $response = $this->getJson('/api/posts');

        $response->assertOk()
            ->assertJsonFragment(['id' => $activePost->id])
            ->assertJsonMissing(['id' => $inactivePost->id]);
    }

    public function test_authenticated_user_can_store_post(): void
    {
        $this->actingAs($this->user);
        $response = $this->postJson('/api/posts', ['body' => 'some post']);

        $response->assertCreated();

        $this->assertDatabaseHas('posts', [
            'user_id' => $this->user->id,
            'body' => 'some post',
        ]);
    }

    public function test_unauthenticated_user_cannot_store_post(): void
    {
        $this->postJson('/api/posts', ['body' => 'some post'])->assertUnauthorized();
    }

    public function test_show_return_post(): void
    {
        $post = Post::factory()->for($this->user)->create();

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $post->id]);
    }

    public function test_owner_can_update_post(): void
    {
        $this->actingAs($this->user);
        $post = Post::factory()->for($this->user)->create();

        $response = $this->patchJson("/api/posts/{$post->id}", ['body' => 'updated post']);

        $response->assertOk();
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'body' => 'updated post']);
    }

    public function test_non_owner_cannot_update_post(): void
    {
        $this->actingAs($this->user);
        $someUser = User::factory()->create();
        $post = Post::factory()->for($someUser)->create();

        $this->patchJson("/api/posts/{$post->id}", ['body' => 'update from other user'])->assertForbidden();
    }

    public function test_owner_can_delete_post(): void
    {
        $this->actingAs($this->user);
        $post = Post::factory()->for($this->user)->create();

        $this->deleteJson("/api/posts/{$post->id}")->assertNoContent();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_non_owner_cannot_delete_post(): void
    {
        $this->actingAs($this->user);
        $someUser = User::factory()->create();
        $post = Post::factory()->for($someUser)->create();

        $this->deleteJson("/api/posts/{$post->id}")->assertForbidden();

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }
}
