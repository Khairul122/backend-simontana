<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_accepts_email_alias_for_username(): void
    {
        $user = Pengguna::create([
            'nama' => 'Warga Test',
            'username' => 'warga_test',
            'password' => 'password123',
            'role' => 'Warga',
            'email' => 'warga_test@example.com',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Login berhasil')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'nama', 'username', 'role'],
                    'token',
                    'token_type',
                    'expires_in',
                ],
                'request_id',
            ]);
    }

    public function test_login_invalid_credentials_uses_standard_error_contract(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'nonexistent',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'INVALID_CREDENTIALS')
            ->assertJsonStructure([
                'success',
                'message',
                'code',
                'request_id',
            ]);
    }
}
