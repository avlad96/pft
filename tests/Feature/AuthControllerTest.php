<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'ivan',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'created_at', 'updated_at'],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'ivan',
        ]);
    }

    public function test_can_login_with_correct_credentials(): void
    {
        User::factory()->create([
            'name' => 'ivan',
            'password' => '123',
        ]);

        $response = $this->postJson('/api/login', [
            'name' => 'ivan',
            'password' => '123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'created_at', 'updated_at'],
                'token',
            ]);
    }

    public function test_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'name' => 'ivan',
            'password' => '123',
        ]);

        $response = $this->postJson('/api/login', [
            'name' => 'ivan',
            'password' => '321',
        ]);

        $response->assertUnauthorized()
            ->assertJson([
                'message' => 'The provided credentials do not match our records.',
            ]);
    }

    public function test_name_and_password_are_required_for_register(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'password']);
    }

    public function test_name_and_password_are_required_for_login(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'password']);
    }
}
