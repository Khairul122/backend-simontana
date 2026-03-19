<?php

namespace App\Services;

use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthService
{
    
    public function register(array $userData): array
    {
        $pengguna = Pengguna::create([
            'nama' => $userData['nama'],
            'username' => $userData['username'],
            'email' => $userData['email'],
            'password' => $userData['password'], 
            'role' => $userData['role'],
            'no_telepon' => $userData['no_telepon'] ?? null,
            'alamat' => $userData['alamat'] ?? null,
            'id_desa' => $userData['id_desa'] ?? null,
        ]);

        return [
            'id' => $pengguna->id,
            'nama' => $pengguna->nama,
            'username' => $pengguna->username,
            'email' => $pengguna->email,
            'role' => $pengguna->role,
            'no_telepon' => $pengguna->no_telepon,
            'alamat' => $pengguna->alamat,
            'id_desa' => $pengguna->id_desa,
            'created_at' => $pengguna->created_at
        ];
    }

    
    public function login(string $username, string $password): ?array
    {
        $pengguna = Pengguna::select([
                'id',
                'nama',
                'username',
                'email',
                'password',
                'role',
                'no_telepon',
                'alamat',
                'id_desa',
            ])
            ->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if (!$pengguna || !Hash::check($password, $pengguna->password)) {
            return null;
        }

        try {
            
            $token = JWTAuth::fromUser($pengguna);

            if (!$token) {
                return null;
            }

            return [
                'user' => [
                    'id' => $pengguna->id,
                    'nama' => $pengguna->nama,
                    'username' => $pengguna->username,
                    'email' => $pengguna->email,
                    'role' => $pengguna->role,
                    'role_label' => $pengguna->role_label,
                    'no_telepon' => $pengguna->no_telepon,
                    'alamat' => $pengguna->alamat,
                    'id_desa' => $pengguna->id_desa,
                    'desa' => null,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('jwt.ttl') * 60 
            ];
        } catch (JWTException $e) {
            return null;
        }
    }

    
    public function logout($user): bool
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return true;
        } catch (JWTException $e) {
            return false;
        }
    }

    
    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return $token;
        } catch (JWTException $e) {
            return null;
        }
    }

    
    public function getCurrentUser($user): array
    {
        if (!$user) {
            return [];
        }

        $user->load('desa.kecamatan.kabupaten.provinsi');

        return [
            'id' => $user->id,
            'nama' => $user->nama,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'role_label' => $user->role_label,
            'no_telepon' => $user->no_telepon,
            'alamat' => $user->alamat,
            'id_desa' => $user->id_desa,
            'desa' => $user->desa ? [
                'id' => $user->desa->id,
                'nama' => $user->desa->nama,
                'kecamatan' => $user->desa->kecamatan ? [
                    'id' => $user->desa->kecamatan->id,
                    'nama' => $user->desa->kecamatan->nama,
                    'kabupaten' => $user->desa->kecamatan->kabupaten ? [
                        'id' => $user->desa->kecamatan->kabupaten->id,
                        'nama' => $user->desa->kecamatan->kabupaten->nama,
                        'provinsi' => $user->desa->kecamatan->kabupaten->provinsi ? [
                            'id' => $user->desa->kecamatan->kabupaten->provinsi->id,
                            'nama' => $user->desa->kecamatan->kabupaten->provinsi->nama,
                        ] : null
                    ] : null
                ] : null
            ] : null,
        ];
    }

    
    public function hasRole($user, string $role): bool
    {
        if (!$user) {
            return false;
        }

        return $user->role === $role;
    }

    
    public function hasAnyRole($user, array $roles): bool
    {
        if (!$user) {
            return false;
        }

        return in_array($user->role, $roles);
    }
}
