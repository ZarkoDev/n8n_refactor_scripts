<?php

declare(strict_types=1);

namespace Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLogoutApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful logout with valid token
     */
    public function test_logout_with_valid_token_returns_success(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Logout successful');

        // Verify token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    /**
     * Test logout without token returns unauthorized
     */
    public function test_logout_without_token_returns_unauthorized(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    /**
     * Test logout with invalid token returns unauthorized
     */
    public function test_logout_with_invalid_token_returns_unauthorized(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    /**
     * Test logout with expired token returns unauthorized
     */
    public function test_logout_with_expired_token_returns_unauthorized(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        // Manually expire the token
        $token->accessToken->expires_at = now()->subHour();
        $token->accessToken->save();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }
}
