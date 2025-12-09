<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Pengguna;
use App\Models\LogActivity;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends Controller
{
    /**
     * Register user baru
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:pengguna',
            'password' => 'required|string|min:6',
            'role' => 'required|in:Admin,PetugasBPBD,OperatorDesa,Warga',
            'email' => 'nullable|email|unique:pengguna',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'id_desa' => 'nullable|exists:desa,id_desa'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $pengguna = Pengguna::create([
                'nama' => $request->nama,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'email' => $request->email,
                'no_telepon' => $request->no_telepon,
                'alamat' => $request->alamat,
                'id_desa' => $request->id_desa
            ]);

            // Log activity
            $this->logActivity($pengguna->id, $pengguna->role, 'Register pengguna baru', 'api/auth/register', $request);

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'data' => [
                    'id' => $pengguna->id,
                    'nama' => $pengguna->nama,
                    'username' => $pengguna->username,
                    'role' => $pengguna->role,
                    'email' => $pengguna->email
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registrasi gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $pengguna = Pengguna::where('username', $request->username)->first();

            if (!$pengguna || !Hash::check($request->password, $pengguna->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username atau password salah'
                ], 401);
            }

            $token = $this->generateToken($pengguna);

            // Log activity
            $this->logActivity($pengguna->id, $pengguna->role, 'Login pengguna', 'api/auth/login', $request);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => [
                        'id' => $pengguna->id,
                        'nama' => $pengguna->nama,
                        'username' => $pengguna->username,
                        'role' => $pengguna->role,
                        'email' => $pengguna->email,
                        'no_telepon' => $pengguna->no_telepon,
                        'alamat' => $pengguna->alamat,
                        'id_desa' => $pengguna->id_desa
                    ],
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl', 60) * 60
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            // Log activity
            $this->logActivity($user->id, $user->role, 'Logout pengguna', 'api/auth/logout', $request);

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        try {
            $user = $request->user();
            $token = $this->generateToken($user);

            // Log activity
            $this->logActivity($user->id, $user->role, 'Refresh token', 'api/auth/refresh', $request);

            return response()->json([
                'success' => true,
                'message' => 'Token berhasil diperbarui',
                'data' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl', 60) * 60
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get profile user
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            $user->load('desa');

            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diambil',
                'data' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'username' => $user->username,
                    'role' => $user->role,
                    'email' => $user->email,
                    'no_telepon' => $user->no_telepon,
                    'alamat' => $user->alamat,
                    'desa' => $user->desa
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate JWT token
     */
    private function generateToken($user)
    {
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + (config('jwt.ttl', 60) * 60),
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role
            ]
        ];

        return JWT::encode($payload, config('jwt.secret'), 'HS256');
    }

    /**
     * Log activity helper
     */
    private function logActivity($userId, $role, $activity, $endpoint, $request)
    {
        try {
            LogActivity::create([
                'user_id' => $userId,
                'role' => $role,
                'aktivitas' => $activity,
                'endpoint' => $endpoint,
                'ip_address' => $request->ip(),
                'device_info' => $request->header('User-Agent'),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            // Silent fail for logging
        }
    }
}
