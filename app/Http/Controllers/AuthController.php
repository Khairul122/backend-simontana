<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Registrasi pengguna baru",
     *     description="Mendaftarkan pengguna baru dengan access token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nama", type="string", example="John Doe", description="Nama lengkap pengguna"),
     *             @OA\Property(property="username", type="string", example="johndoe", description="Username unik"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com", description="Email unik"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="Password minimal 6 karakter"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123", description="Konfirmasi password"),
     *             @OA\Property(property="role", type="string", enum={"Admin","PetugasBPBD","OperatorDesa","Warga"}, example="Warga", description="Role pengguna"),
     *             @OA\Property(property="no_telepon", type="string", example="08123456789", description="Nomor telepon (opsional)"),
     *             @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123", description="Alamat (opsional)"),
     *             @OA\Property(property="id_desa", type="integer", example=1, description="ID desa (opsional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registrasi berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registrasi berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama", type="string", example="John Doe"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="Warga"),
     *                 @OA\Property(property="no_telepon", type="string", example="08123456789"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123"),
     *                 @OA\Property(property="id_desa", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", example="2025-12-15T23:22:11.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validasi gagal"),
     *             @OA\Property(property="errors", type="object", example={"username": "Username sudah digunakan"})
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $userData = $this->authService->register($request->validated());

            // Log activity
            $this->logActivity($userData['id'], $userData['role'], 'Registrasi pengguna baru', '/api/auth/register', $request->ip(), $request->userAgent());

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'data' => $userData
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registrasi gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Login pengguna",
     *     description="Login pengguna dan mendapatkan access token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="admin", description="Username atau email"),
     *             @OA\Property(property="password", type="string", format="password", example="password", description="Password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama", type="string", example="John Doe"),
     *                     @OA\Property(property="username", type="string", example="johndoe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="role", type="string", example="Warga"),
     *                     @OA\Property(property="role_label", type="string", example="Warga"),
     *                     @OA\Property(property="no_telepon", type="string", example="08123456789"),
     *                     @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123"),
     *                     @OA\Property(property="id_desa", type="integer", example=1),
     *                     @OA\Property(property="desa", type="string", example="Desa Contoh")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="1|abc123token"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_at", type="string", example="2026-12-15T23:22:11.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Login gagal",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Username atau password salah")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validasi gagal"),
     *             @OA\Property(property="errors", type="object", example={"username": "Username wajib diisi"})
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $loginResult = $this->authService->login($request->username, $request->password);

        if (!$loginResult) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        // Log activity
        $this->logActivity($loginResult['user']['id'], $loginResult['user']['role'], 'Login berhasil', '/api/auth/login', $request->ip(), $request->userAgent());

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => $loginResult
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Logout pengguna",
     *     description="Logout pengguna dan revoke token",
     *     tags={"Authentication"},
     *     security={{"jwt": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logout berhasil")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token tidak valid")
     *         )
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada sesi login'
            ], 401);
        }

        // Log activity before logout
        $this->logActivity($user->id, $user->role, 'Logout', '/api/auth/logout', $request->ip(), $request->userAgent());

        $this->authService->logout($user);

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     summary="Refresh JWT token",
     *     description="Refresh token JWT yang sedang aktif",
     *     tags={"Authentication"},
     *     security={{"jwt": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token berhasil diperbarui",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token berhasil diperbarui"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="new_jwt_token_here"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token tidak valid")
     *         )
     *     )
     * )
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $token = $this->authService->refresh();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui token'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Token berhasil diperbarui',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     summary="Get current user info",
     *     description="Mendapatkan informasi user yang sedang login berdasarkan token",
     *     tags={"Authentication"},
     *     security={{"jwt": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Data user berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data user berhasil diambil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama", type="string", example="John Doe"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="Warga"),
     *                 @OA\Property(property="role_label", type="string", example="Warga"),
     *                 @OA\Property(property="no_telepon", type="string", example="08123456789"),
     *                 @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123"),
     *                 @OA\Property(property="id_desa", type="integer", example=1),
     *                 @OA\Property(property="tokens_count", type="integer", example=2),
     *                 @OA\Property(property="last_login_at", type="string", example="2025-12-15T23:22:11.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token tidak valid")
     *         )
     *     )
     * )
     */
    public function me(Request $request): JsonResponse
    {
        // Get the authenticated user using the JWT guard
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil',
            'data' => $this->authService->getCurrentUser($user)
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/auth/roles",
     *     summary="Get available roles",
     *     description="Mendapatkan daftar role yang tersedia",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar role berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Daftar role tersedia"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="Admin", type="string", example="Administrator"),
     *                 @OA\Property(property="PetugasBPBD", type="string", example="Petugas BPBD"),
     *                 @OA\Property(property="OperatorDesa", type="string", example="Operator Desa"),
     *                 @OA\Property(property="Warga", type="string", example="Warga")
     *             )
     *         )
     *     )
     * )
     */
    public function getRoles(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Daftar role tersedia',
            'data' => Pengguna::getAvailableRoles()
        ], 200);
    }

    /**
     * Helper function untuk log activity
     */
    private function logActivity($userId, $role, $aktivitas, $endpoint, $ipAddress, $deviceInfo): void
    {
        try {
            \DB::table('log_activity')->insert([
                'user_id' => $userId,
                'role' => $role,
                'aktivitas' => $aktivitas,
                'endpoint' => $endpoint,
                'ip_address' => $ipAddress,
                'device_info' => $deviceInfo,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silent fail for logging
        }
    }
}