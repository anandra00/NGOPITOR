<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user registration.
     */
    public function test_user_can_register_successfully(): void
    {
        $payload = [
            'name' => 'Kopi Enthusiast',
            'email' => 'kopi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson(route('api.v1.auth.register'), $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User registered successfully',
                'errors' => null,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                    'token_type',
                ],
                'errors',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'kopi@example.com',
            'name' => 'Kopi Enthusiast',
        ]);
    }

    /**
     * Test registration validation failure.
     */
    public function test_registration_fails_when_password_confirmation_does_not_match(): void
    {
        $payload = [
            'name' => 'Kopi Enthusiast',
            'email' => 'kopi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        $response = $this->postJson(route('api.v1.auth.register'), $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation error',
                'data' => null,
            ])
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test registration fails when email is already taken.
     */
    public function test_registration_fails_when_email_already_exists(): void
    {
        User::factory()->create([
            'email' => 'duplicate@example.com',
        ]);

        $payload = [
            'name' => 'Another User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson(route('api.v1.auth.register'), $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation error',
                'data' => null,
            ])
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'barista@example.com',
            'password' => Hash::make('coffee123'),
        ]);

        $payload = [
            'email' => 'barista@example.com',
            'password' => 'coffee123',
        ];

        $response = $this->postJson(route('api.v1.auth.login'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
                'errors' => null,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'token',
                    'token_type',
                ],
                'errors',
            ]);
    }

    /**
     * Test user login fails with wrong password.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'barista@example.com',
            'password' => Hash::make('coffee123'),
        ]);

        $payload = [
            'email' => 'barista@example.com',
            'password' => 'wrong_password',
        ];

        $response = $this->postJson(route('api.v1.auth.login'), $payload);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
                'data' => null,
                'errors' => null,
            ]);
    }

    /**
     * Test authenticated user can access profile with token.
     */
    public function test_authenticated_user_can_fetch_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Roaster',
            'email' => 'jane@example.com',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withToken($token)->getJson(route('api.v1.auth.me'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User profile fetched successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => 'Jane Roaster',
                    'email' => 'jane@example.com',
                ],
                'errors' => null,
            ]);
    }

    /**
     * Test unauthenticated request cannot access profile.
     */
    public function test_unauthenticated_request_is_rejected_on_me_endpoint(): void
    {
        $response = $this->getJson(route('api.v1.auth.me'));

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated',
                'data' => null,
                'errors' => null,
            ]);
    }

    /**
     * Test user can logout and revoke token.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->assertCount(1, $user->tokens);

        $response = $this->withToken($token)->postJson(route('api.v1.auth.logout'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out',
                'data' => null,
                'errors' => null,
            ]);

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
