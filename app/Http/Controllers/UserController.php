<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users with pagination",
     *     description="Mendapatkan daftar semua pengguna dengan pagination (Admin only)",
     *     tags={"User Management"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Jumlah data per halaman",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Pencarian berdasarkan nama, username, atau email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter berdasarkan role",
     *         required=false,
     *         @OA\Schema(type="string", enum={"Admin","PetugasBPBD","OperatorDesa","Warga"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data pengguna berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data pengguna berhasil diambil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", items={"type": "object"}),
     *                 @OA\Property(property="total", type="integer", example=100)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Akses ditolak")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        // Hanya admin yang dapat mengakses endpoint ini
        if ($user->role !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        $query = Pengguna::with(['desa.kecamatan.kabupaten.provinsi']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Role filter
        if ($request->has('role') && !empty($request->role)) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna berhasil diambil',
            'data' => $users
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/users/statistics",
     *     summary="Get user statistics",
     *     description="Mendapatkan statistik pengguna (Admin only)",
     *     tags={"User Management"},
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistik pengguna berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statistik pengguna berhasil diambil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_users", type="integer", example=100),
     *                 @OA\Property(property="by_role", type="array", items={"type": "object"}),
     *                 @OA\Property(property="recent_users", type="array", items={"type": "object"}),
     *                 @OA\Property(property="users_this_month", type="integer", example=15)
     *             )
     *         )
     *     )
     * )
     */
    public function statistics(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        // Hanya admin yang dapat mengakses endpoint ini
        if ($user->role !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        // Total users
        $totalUsers = Pengguna::count();

        // Users by role
        $usersByRole = Pengguna::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->orderBy('total', 'desc')
            ->get();

        // Recent users (last 7 days)
        $recentUsers = Pengguna::with('desa')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Users this month
        $usersThisMonth = Pengguna::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Statistik pengguna berhasil diambil',
            'data' => [
                'total_users' => $totalUsers,
                'by_role' => $usersByRole,
                'recent_users' => $recentUsers,
                'users_this_month' => $usersThisMonth
            ]
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/users/profile",
     *     summary="Get user profile",
     *     description="Mendapatkan profil pengguna yang sedang login",
     *     tags={"User Management"},
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profil berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profil berhasil diambil"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function profile(Request $request)
    {
        $user = $request->user ?? null;

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        $user = $user->load(['desa.kecamatan.kabupaten.provinsi']);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diambil',
            'data' => [
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
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/users/profile",
     *     summary="Update user profile",
     *     description="Update profil pengguna yang sedang login",
     *     tags={"User Management"},
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nama", type="string", example="John Doe Updated"),
     *             @OA\Property(property="no_telepon", type="string", example="08123456789"),
     *             @OA\Property(property="alamat", type="string", example="Alamat Updated"),
     *             @OA\Property(property="id_desa", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profil berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profil berhasil diupdate"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user ?? null;

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        $validated = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'no_telepon' => 'sometimes|string|max:15',
            'alamat' => 'sometimes|string|max:500',
            'id_desa' => 'sometimes|integer|exists:desa,id'
        ]);

        $user->update($validated);

        // Log activity
        $this->logActivity($user->id, $user->role, 'Update profil', '/api/users/profile', $request->ip(), $request->userAgent());

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diupdate',
            'data' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'role_label' => $user->role_label,
                'no_telepon' => $user->no_telepon,
                'alamat' => $user->alamat,
                'id_desa' => $user->id_desa,
                'updated_at' => $user->updated_at
            ]
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get specific user",
     *     description="Mendapatkan data pengguna spesifik (Admin only)",
     *     tags={"User Management"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID pengguna",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data pengguna berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data pengguna berhasil diambil"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function show(Request $request, $id)
    {
        try {
            $currentUser = auth()->user();

            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        // Hanya admin yang dapat mengakses endpoint ini
        if ($currentUser->role !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        $user = Pengguna::with(['desa.kecamatan.kabupaten.provinsi'])->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna berhasil diambil',
            'data' => $user
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create new user",
     *     description="Menambah pengguna baru (Admin only)",
     *     tags={"User Management"},
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nama", type="string", example="John Doe"),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="role", type="string", example="Warga"),
     *             @OA\Property(property="no_telepon", type="string", example="08123456789"),
     *             @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123"),
     *             @OA\Property(property="id_desa", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pengguna berhasil ditambahkan",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pengguna berhasil ditambahkan"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function store(CreateUserRequest $request)
    {
        try {
            $currentUser = auth()->user();

            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        // Hanya admin yang dapat mengakses endpoint ini
        if ($currentUser->role !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        $user = Pengguna::create($request->validated());

        // Load relationships for response
        $user->load(['desa.kecamatan.kabupaten.provinsi']);

        // Log activity
        $this->logActivity($user->id, $user->role, 'Buat pengguna baru', '/api/users', $request->ip(), $request->userAgent());

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil ditambahkan',
            'data' => $user
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update user",
     *     description="Update data pengguna (Admin only)",
     *     tags={"User Management"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID pengguna",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nama", type="string", example="John Doe Updated"),
     *             @OA\Property(property="username", type="string", example="johndoe_updated"),
     *             @OA\Property(property="email", type="string", example="john_updated@example.com"),
     *             @OA\Property(property="role", type="string", example="Warga"),
     *             @OA\Property(property="no_telepon", type="string", example="08123456789"),
     *             @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123"),
     *             @OA\Property(property="id_desa", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pengguna berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pengguna berhasil diupdate"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function update(UpdateUserRequest $request, $id)
    {
        try {
            $currentUser = auth()->user();

            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        // Hanya admin yang dapat mengakses endpoint ini
        if ($currentUser->role !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        $user = Pengguna::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan'
            ], 404);
        }

        $user->update($request->validated());

        // Load relationships for response
        $user->load(['desa.kecamatan.kabupaten.provinsi']);

        // Log activity
        $this->logActivity($user->id, $user->role, 'Update pengguna', '/api/users/' . $id, $request->ip(), $request->userAgent());

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil diupdate',
            'data' => $user
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete user",
     *     description="Hapus pengguna (Admin only)",
     *     tags={"User Management"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID pengguna",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pengguna berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pengguna berhasil dihapus")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, $id)
    {
        try {
            $currentUser = auth()->user();

            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        // Hanya admin yang dapat mengakses endpoint ini
        if ($currentUser->role !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        $user = Pengguna::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan'
            ], 404);
        }

        // Log activity before delete
        $this->logActivity($user->id, $user->role, 'Hapus pengguna', '/api/users/' . $id, $request->ip(), $request->userAgent());

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil dihapus'
        ], 200);
    }

    /**
     * Helper function untuk log activity
     */
    private function logActivity($userId, $role, $aktivitas, $endpoint, $ipAddress, $deviceInfo): void
    {
        try {
            DB::table('log_activity')->insert([
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