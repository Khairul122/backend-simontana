<?php

namespace App\Http\Controllers;

use App\Models\KategoriBencana;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class KategoriBencanaController extends Controller
{
    private const ALLOWED_SORT_FIELDS = ['id', 'nama_kategori', 'created_at', 'updated_at'];

    public function __construct(private readonly LogActivityService $logActivityService)
    {
    }

    
    public function index(Request $request): JsonResponse
    {
        $query = KategoriBencana::query();

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('nama_kategori', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$searchTerm}%");
        }

        $sortField = $request->input('sort_field', 'id');
        if (!in_array($sortField, self::ALLOWED_SORT_FIELDS, true)) {
            $sortField = 'id';
        }

        $sortDirection = strtolower((string) $request->input('sort_direction', 'asc'));
        if (!in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'asc';
        }

        $query->orderBy($sortField, $sortDirection);

        $perPage = $this->clampPerPage((int) $request->input('per_page', 15), 15, 100);
        $kategoriBencana = $query->paginate($perPage);

        return $this->successResponse('Daftar kategori bencana berhasil diambil', $kategoriBencana);
    }

    
    public function store(Request $request): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);

        if (!$user) {
            return $this->unauthorized();
        }

        if ($user->role !== 'Admin') {
            return $this->forbidden('Akses ditolak: Hanya Admin yang dapat menambahkan kategori bencana');
        }

        $validatedData = $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori_bencana,nama_kategori',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:255'
        ]);

        try {
            $kategoriBencana = KategoriBencana::create($validatedData);

            $this->logActivityService->log($user->id, $user->role, "Menambahkan kategori bencana: {$kategoriBencana->nama_kategori}", '/api/kategori-bencana', $request->ip(), $request->userAgent());

            return $this->successResponse('Kategori bencana berhasil ditambahkan', $kategoriBencana, 201);
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan kategori bencana', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return $this->internalError('Gagal menambahkan kategori bencana');
        }
    }

    
    public function show(int $id): JsonResponse
    {
        $kategoriBencana = KategoriBencana::find($id);

        if (!$kategoriBencana) {
            return $this->notFoundResponse('Kategori bencana tidak ditemukan');
        }

        return $this->successResponse('Detail kategori bencana berhasil diambil', $kategoriBencana);
    }

    
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);

        if (!$user) {
            return $this->unauthorized();
        }

        if ($user->role !== 'Admin') {
            return $this->forbidden('Akses ditolak: Hanya Admin yang dapat mengupdate kategori bencana');
        }

        $kategoriBencana = KategoriBencana::find($id);

        if (!$kategoriBencana) {
            return $this->notFoundResponse('Kategori bencana tidak ditemukan');
        }

        $validatedData = $request->validate([
            'nama_kategori' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('kategori_bencana', 'nama_kategori')->ignore($kategoriBencana->id)
            ],
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:255'
        ]);

        try {
            $oldNamaKategori = $kategoriBencana->nama_kategori;
            $kategoriBencana->update($validatedData);

            $this->logActivityService->log($user->id, $user->role, "Memperbarui kategori bencana: {$oldNamaKategori} -> {$kategoriBencana->nama_kategori}", '/api/kategori-bencana/' . $id, $request->ip(), $request->userAgent());

            return $this->successResponse('Kategori bencana berhasil diperbarui', $kategoriBencana);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui kategori bencana', [
                'error' => $e->getMessage(),
                'kategori_id' => $id,
                'user_id' => $user->id,
            ]);

            return $this->internalError('Gagal memperbarui kategori bencana');
        }
    }

    
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);

        if (!$user) {
            return $this->unauthorized();
        }

        if ($user->role !== 'Admin') {
            return $this->forbidden('Akses ditolak: Hanya Admin yang dapat menghapus kategori bencana');
        }

        $kategoriBencana = KategoriBencana::find($id);

        if (!$kategoriBencana) {
            return $this->notFoundResponse('Kategori bencana tidak ditemukan');
        }

        if ($kategoriBencana->laporans()->count() > 0) {
            return $this->errorResponse('Tidak dapat menghapus kategori bencana karena masih terdapat laporan yang terkait', 400);
        }

        try {
            $namaKategori = $kategoriBencana->nama_kategori;
            $kategoriBencana->delete();

            $this->logActivityService->log($user->id, $user->role, "Menghapus kategori bencana: {$namaKategori}", '/api/kategori-bencana/' . $id, $request->ip(), $request->userAgent());

            return $this->successResponse('Kategori bencana berhasil dihapus', $kategoriBencana);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus kategori bencana', [
                'error' => $e->getMessage(),
                'kategori_id' => $id,
                'user_id' => $user->id,
            ]);

            return $this->internalError('Gagal menghapus kategori bencana');
        }
    }
}
