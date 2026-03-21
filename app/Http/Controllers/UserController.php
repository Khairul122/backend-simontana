<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    public function __construct(private readonly LogActivityService $logActivityService)
    {
    }

    
    public function index(Request $request)
    {
        $user = $this->ensureAuthenticated($request);

        if (!$user) {
            return $this->unauthorized();
        }

        $query = Pengguna::with(['desa.kecamatan.kabupaten.provinsi']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('role') && !empty($request->role)) {
            $query->where('role', $request->role);
        }

        $perPage = $this->clampPerPage((int) $request->input('per_page', 15), 15, 100);
        $users = $query->paginate($perPage);

        return $this->successResponse('Data pengguna berhasil diambil', $users);
    }

    
    public function statistics(Request $request)
    {
        $user = $this->ensureAuthenticated($request);

        if (!$user) {
            return $this->unauthorized();
        }

        $totalUsers = Pengguna::count();

        $usersByRole = Pengguna::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->orderBy('total', 'desc')
            ->get();

        $recentUsers = Pengguna::with('desa.kecamatan.kabupaten.provinsi')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $usersThisMonth = Pengguna::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return $this->successResponse('Statistik pengguna berhasil diambil', [
            'total_users' => $totalUsers,
            'by_role' => $usersByRole,
            'recent_users' => $recentUsers,
            'users_this_month' => $usersThisMonth,
        ]);
    }

    
    public function profile(Request $request)
    {
        $user = $this->ensureAuthenticated($request);

        if (!$user) {
            return $this->unauthorized();
        }

        $user = $user->load(['desa.kecamatan.kabupaten.provinsi']);

        return $this->successResponse('Profil berhasil diambil', [
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
                        ] : null,
                    ] : null,
                ] : null,
            ] : null,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

    
    public function updateProfile(Request $request)
    {
        $user = $this->ensureAuthenticated($request);

        if (!$user) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'no_telepon' => 'sometimes|string|max:15',
            'alamat' => 'sometimes|string|max:500',
            'id_desa' => 'sometimes|integer|exists:desa,id'
        ]);

        $user->update($validated);

        $this->logActivityService->log($user->id, $user->role, 'Update profil', '/api/users/profile', $request->ip(), $request->userAgent());

        return $this->successResponse('Profil berhasil diupdate', [
            'id' => $user->id,
            'nama' => $user->nama,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'role_label' => $user->role_label,
            'no_telepon' => $user->no_telepon,
            'alamat' => $user->alamat,
            'id_desa' => $user->id_desa,
            'updated_at' => $user->updated_at,
        ]);
    }

    
    public function show(Request $request, $id)
    {
        $currentUser = $this->ensureAuthenticated($request);

        if (!$currentUser) {
            return $this->unauthorized();
        }

        $user = Pengguna::with(['desa.kecamatan.kabupaten.provinsi'])->find($id);

        if (!$user) {
            return $this->notFoundResponse('Pengguna tidak ditemukan');
        }

        return $this->successResponse('Data pengguna berhasil diambil', $user);
    }

    
    public function store(CreateUserRequest $request)
    {
        $currentUser = $this->ensureAuthenticated($request);

        if (!$currentUser) {
            return $this->unauthorized();
        }

        $user = Pengguna::create($request->validated());

        $user->load(['desa.kecamatan.kabupaten.provinsi']);

        $this->logActivityService->log($currentUser->id, $currentUser->role, 'Buat pengguna baru', '/api/users', $request->ip(), $request->userAgent());

        return $this->successResponse('Pengguna berhasil ditambahkan', $user, 201);
    }

    
    public function update(UpdateUserRequest $request, $id)
    {
        $currentUser = $this->ensureAuthenticated($request);

        if (!$currentUser) {
            return $this->unauthorized();
        }

        $user = Pengguna::find($id);

        if (!$user) {
            return $this->notFoundResponse('Pengguna tidak ditemukan');
        }

        $user->update($request->validated());

        $user->load(['desa.kecamatan.kabupaten.provinsi']);

        $this->logActivityService->log($currentUser->id, $currentUser->role, 'Update pengguna', '/api/users/' . $id, $request->ip(), $request->userAgent());

        return $this->successResponse('Pengguna berhasil diupdate', $user);
    }

    
    public function destroy(Request $request, $id)
    {
        $currentUser = $this->ensureAuthenticated($request);

        if (!$currentUser) {
            return $this->unauthorized();
        }

        $user = Pengguna::find($id);

        if (!$user) {
            return $this->notFoundResponse('Pengguna tidak ditemukan');
        }

        $this->logActivityService->log($currentUser->id, $currentUser->role, 'Hapus pengguna', '/api/users/' . $id, $request->ip(), $request->userAgent());

        $user->delete();

        return $this->successResponse('Pengguna berhasil dihapus');
    }
}
