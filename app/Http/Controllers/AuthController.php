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
     * @OA\Post(
     *      path="/api/auth/register",
     *      tags={"Authentication"},
     *      summary="Register User Baru",
     *      description="Endpoint untuk mendaftarkan user baru ke dalam sistem SIMONTA BENCANA.",
     *      operationId="register",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"nama","username","password","role"},
     *              @OA\Property(property="nama", type="string", example="John Doe", description="Nama lengkap user"),
     *              @OA\Property(property="username", type="string", example="johndoe", description="Username unik untuk login"),
     *              @OA\Property(property="password", type="string", format="password", example="123456", description="Password minimal 6 karakter"),
     *              @OA\Property(property="role", type="string", enum={"Admin","PetugasBPBD","OperatorDesa","Warga"}, example="Warga", description="Role user"),
     *              @OA\Property(property="email", type="string", format="email", example="john@example.com", description="Email user"),
     *              @OA\Property(property="no_telepon", type="string", example="08123456789", description="Nomor telepon"),
     *              @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123", description="Alamat lengkap"),
     *              @OA\Property(property="id_desa", type="integer", example=1, description="ID desa (jika ada)")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="User berhasil didaftarkan",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Registrasi berhasil"),
     *              @OA\Property(property="data",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="nama", type="string", example="John Doe"),
     *                  @OA\Property(property="username", type="string", example="johndoe"),
     *                  @OA\Property(property="role", type="string", example="Warga"),
     *                  @OA\Property(property="email", type="string", example="john@example.com")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validasi gagal",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
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
     * @OA\Post(
     *      path="/api/auth/login",
     *      tags={"Authentication"},
     *      summary="Login User",
     *      description="Endpoint untuk autentikasi user dan mendapatkan JWT token.",
     *      operationId="login",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"username","password"},
     *              @OA\Property(property="username", type="string", example="admintest", description="Username atau email"),
     *              @OA\Property(property="password", type="string", format="password", example="123456", description="Password user")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Login berhasil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Login berhasil"),
     *              @OA\Property(property="data",
     *                  @OA\Property(property="user",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="nama", type="string", example="Admin Test"),
     *                      @OA\Property(property="username", type="string", example="admintest"),
     *                      @OA\Property(property="role", type="string", example="Admin"),
     *                      @OA\Property(property="email", type="string", example="admin@example.com")
     *                  ),
     *                  @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                  @OA\Property(property="token_type", type="string", example="bearer"),
     *                  @OA\Property(property="expires_in", type="integer", example=3600)
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Username atau password salah",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Username atau password salah")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validasi gagal",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
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
     * @OA\Post(
     *      path="/api/auth/logout",
     *      tags={"Authentication"},
     *      summary="Logout User",
     *      description="Endpoint untuk logout user dan menghapus JWT token.",
     *      operationId="logout",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Logout berhasil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Logout berhasil")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      )
     * )
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
     * @OA\Post(
     *      path="/api/auth/refresh",
     *      tags={"Authentication"},
     *      summary="Refresh JWT Token",
     *      description="Endpoint untuk memperbarui JWT token yang masih valid.",
     *      operationId="refreshToken",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Token berhasil diperbarui",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Token berhasil diperbarui"),
     *              @OA\Property(property="data",
     *                  @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                  @OA\Property(property="token_type", type="string", example="bearer"),
     *                  @OA\Property(property="expires_in", type="integer", example=3600)
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized - Token tidak valid atau kadaluwarsa"
     *      )
     * )
     */
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
     * @OA\Get(
     *      path="/api/auth/profile",
     *      tags={"Authentication"},
     *      summary="Get Profile User",
     *      description="Endpoint untuk mendapatkan profile user yang sedang login.",
     *      operationId="profile",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Profile berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Profile berhasil diambil"),
     *              @OA\Property(property="data",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="nama", type="string", example="Admin Test"),
     *                  @OA\Property(property="username", type="string", example="admintest"),
     *                  @OA\Property(property="role", type="string", example="Admin"),
     *                  @OA\Property(property="email", type="string", example="admin@example.com"),
     *                  @OA\Property(property="no_telepon", type="string", example="08123456789"),
     *                  @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123"),
     *                  @OA\Property(property="desa", type="object",
     *                      @OA\Property(property="id_desa", type="integer", example=1),
     *                      @OA\Property(property="nama_desa", type="string", example="Contoh Desa"),
     *                      @OA\Property(property="kecamatan", type="string", example="Contoh Kecamatan"),
     *                      @OA\Property(property="kabupaten", type="string", example="Contoh Kabupaten")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      )
     * )
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
