<?php

namespace App\Http\Controllers;

use App\Models\Laporans;
use App\Http\Requests\StoreLaporanRequest;
use App\Http\Requests\UpdateLaporanRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;



class LaporansController extends Controller
{
    private const MAX_RADIUS_KM = 100;

    private const ALLOWED_ORDER_BY = [
        'id',
        'created_at',
        'updated_at',
        'waktu_laporan',
        'status',
        'tingkat_keparahan',
        'view_count',
        'is_prioritas',
    ];

    
    private function handleFileUpload(Request $request, string $fieldName, ?string $oldFile = null): ?string
    {
        if (!$request->hasFile($fieldName)) {
            return null;
        }

        $file = $request->file($fieldName);

        
        if ($oldFile) {
            try {
                Storage::disk('public')->delete('laporans/' . $oldFile);
            } catch (\Exception $e) {
                Log::warning("Failed to delete old file: {$oldFile}", ['error' => $e->getMessage()]);
            }
        }

        
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        
        $file->storeAs('laporans', $fileName, 'public');

        return $fileName;
    }

    
    private function deleteFiles(array $files): void
    {
        foreach ($files as $file) {
            if ($file) {
                try {
                    Storage::disk('public')->delete('laporans/' . $file);
                } catch (\Exception $e) {
                    Log::warning("Failed to delete file: {$file}", ['error' => $e->getMessage()]);
                }
            }
        }
    }

    
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Laporans::with(Laporans::FULL_RELATIONS);

            
            if ($request->has('status') && $request->status) {
                $query->byStatus($request->status);
            }

            if ($request->has('kategori_id') && $request->kategori_id) {
                $query->byCategory($request->kategori_id);
            }

            if ($request->has('user_id') && $request->user_id) {
                $query->byUser($request->user_id);
            }

            if ($request->has('prioritas') && $request->boolean('prioritas')) {
                $query->prioritas();
            }

            if ($request->has('id_desa') && $request->id_desa) {
                $query->where('id_desa', $request->id_desa);
            }

            
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->dateRange($request->start_date, $request->end_date);
            }

            
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('judul_laporan', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%")
                      ->orWhere('alamat_lengkap', 'like', "%{$search}%");
                });
            }

            
            if ($request->has('lat') && $request->has('lng') && $request->has('radius')) {
                $radius = (float) $request->radius;
                if ($radius <= 0) {
                    $radius = 10;
                }

                if ($radius > self::MAX_RADIUS_KM) {
                    $radius = self::MAX_RADIUS_KM;
                }

                $query->byLocationRadius($request->lat, $request->lng, $radius);
            }

            
            $orderBy = $request->get('order_by', 'created_at');
            if (!in_array($orderBy, self::ALLOWED_ORDER_BY, true)) {
                $orderBy = 'created_at';
            }

            $orderDirection = strtolower((string) $request->get('order_direction', 'desc'));
            if (!in_array($orderDirection, ['asc', 'desc'], true)) {
                $orderDirection = 'desc';
            }

            $query->orderBy($orderBy, $orderDirection);

            
            $limit = $this->clampPerPage((int) $request->get('limit', 15), 15, 100);
            $laporans = $query->paginate($limit);

            return $this->successResponse('Data laporan berhasil diambil', $laporans);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil data laporan', ['error' => $e->getMessage()]);
            return $this->internalError('Gagal mengambil data laporan');
        }
    }

    public function byPelapor(Request $request, int $pelaporId): JsonResponse
    {
        try {
            $user = $this->ensureAuthenticated($request);
            if (!$user) {
                return $this->unauthorized();
            }

            if ($user->role === 'Warga' && (int) $user->id !== $pelaporId) {
                return $this->deniedByPolicy('Warga hanya dapat melihat laporan miliknya sendiri');
            }

            $query = Laporans::with(Laporans::FULL_RELATIONS)
                ->byUser($pelaporId);

            if ($request->has('status') && $request->status) {
                $query->byStatus($request->status);
            }

            if ($request->has('kategori_id') && $request->kategori_id) {
                $query->byCategory($request->kategori_id);
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->dateRange($request->start_date, $request->end_date);
            }

            $orderBy = $request->get('order_by', 'created_at');
            if (!in_array($orderBy, self::ALLOWED_ORDER_BY, true)) {
                $orderBy = 'created_at';
            }

            $orderDirection = strtolower((string) $request->get('order_direction', 'desc'));
            if (!in_array($orderDirection, ['asc', 'desc'], true)) {
                $orderDirection = 'desc';
            }

            $query->orderBy($orderBy, $orderDirection);

            $limit = $this->clampPerPage((int) $request->get('limit', 15), 15, 100);
            $laporans = $query->paginate($limit);

            return $this->successResponse('Data laporan pelapor berhasil diambil', $laporans);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data laporan berdasarkan pelapor', [
                'pelapor_id' => $pelaporId,
                'error' => $e->getMessage(),
            ]);

            return $this->internalError('Gagal mengambil data laporan berdasarkan pelapor');
        }
    }

    
    public function store(StoreLaporanRequest $request): JsonResponse
    {
        try {
            $user = $this->ensureAuthenticated($request);
            if (!$user) {
                return $this->unauthorized();
            }

            $data = $request->validated();
            $data['id_pelapor'] = $user->id;
            $data['waktu_laporan'] = $request->waktu_laporan ?? now();
            $data['status'] = $data['status'] ?? 'Draft';
            $data['view_count'] = 0;

            
            $fileFields = ['foto_bukti_1', 'foto_bukti_2', 'foto_bukti_3', 'video_bukti'];
            foreach ($fileFields as $field) {
                $fileName = $this->handleFileUpload($request, $field);
                if ($fileName) {
                    $data[$field] = $fileName;
                }
            }

            $data['alamat_lengkap'] = $request->alamat_laporan ?? null;
            $data['jumlah_korban'] = $request->jumlah_korban ?? 0;
            $data['jumlah_rumah_rusak'] = $request->jumlah_rumah_rusak ?? 0;
            $data['is_prioritas'] = $request->boolean('is_prioritas', false);
            $data['data_tambahan'] = $request->data_tambahan ?? null;

            $laporan = Laporans::create($data);
            $laporan->load(Laporans::FULL_RELATIONS);

            return $this->successResponse('Laporan berhasil dibuat', $laporan, 201);

        } catch (\Exception $e) {
            Log::error('Gagal membuat laporan', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
            ]);

            return $this->internalError('Gagal membuat laporan');
        }
    }

    
    public function show(Laporans $laporan): JsonResponse
    {
        try {
            
            $laporan->incrementViewCount();

            
            $laporan->load(Laporans::FULL_RELATIONS);

            return $this->successResponse('Detail laporan berhasil diambil', $laporan);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil detail laporan', [
                'laporan_id' => $laporan->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->internalError('Gagal mengambil detail laporan');
        }
    }

    
    public function update(UpdateLaporanRequest $request, Laporans $laporan): JsonResponse
    {
        try {
            $user = $this->ensureAuthenticated($request);
            if (!$user) {
                return $this->unauthorized();
            }

            
            if ($user->id !== $laporan->id_pelapor && !$user->hasRole(['Admin', 'PetugasBPBD', 'OperatorDesa'])) {
                return $this->forbidden('Tidak memiliki izin untuk mengubah laporan ini');
            }

            $data = $request->validated();

            
            $fileFields = ['foto_bukti_1', 'foto_bukti_2', 'foto_bukti_3', 'video_bukti'];
            foreach ($fileFields as $field) {
                $fileName = $this->handleFileUpload($request, $field, $laporan->$field);
                if ($fileName) {
                    $data[$field] = $fileName;
                }
            }

            
            $data['alamat_lengkap'] = $request->alamat_laporan ?? $laporan->alamat_lengkap;
            $data['jumlah_korban'] = $request->jumlah_korban ?? $laporan->jumlah_korban;
            $data['jumlah_rumah_rusak'] = $request->jumlah_rumah_rusak ?? $laporan->jumlah_rumah_rusak;

            if ($request->has('is_prioritas')) {
                $data['is_prioritas'] = $request->boolean('is_prioritas');
            }

            if ($request->has('data_tambahan')) {
                $data['data_tambahan'] = $request->data_tambahan;
            }

            
            $isStatusChanging = isset($data['status']) && $data['status'] !== $laporan->status;

            if ($isStatusChanging) {
                $updateData = $data;

                if ($data['status'] === 'Diverifikasi') {
                    $user_id = $user->id;
                    $updateData['id_verifikator'] = $user_id;
                    $updateData['waktu_verifikasi'] = now();

                    if (is_null($laporan->id_penanggung_jawab)) {
                        $updateData['id_penanggung_jawab'] = $user_id;
                    }
                }

                $laporan->update($updateData);
            } else {
                $laporan->update($data);
            }

            
            $laporan->load(Laporans::FULL_RELATIONS);

            return $this->successResponse('Laporan berhasil diperbarui', $laporan);

        } catch (\Exception $e) {
            Log::error('Gagal memperbarui laporan', [
                'laporan_id' => $laporan->id ?? null,
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->internalError('Gagal memperbarui laporan');
        }
    }

    
    public function destroy(Request $request, Laporans $laporan): JsonResponse
    {
        try {
            $user = $this->ensureAuthenticated($request);
            if (!$user) {
                return $this->unauthorized();
            }

            
            if ($user->id !== $laporan->id_pelapor && !$user->hasRole(['Admin', 'OperatorDesa'])) {
                return $this->forbidden('Tidak memiliki izin untuk menghapus laporan ini');
            }

            
            $filesToDelete = [
                $laporan->foto_bukti_1,
                $laporan->foto_bukti_2,
                $laporan->foto_bukti_3,
                $laporan->video_bukti
            ];
            $this->deleteFiles($filesToDelete);

            $laporan->delete(); // Soft delete — baris tetap ada di DB dengan deleted_at terisi

            return $this->successResponse('Laporan berhasil dihapus', null, 200, ['data' => null]);

        } catch (\Exception $e) {
            Log::error('Gagal menghapus laporan', [
                'laporan_id' => $laporan->id ?? null,
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->internalError('Gagal menghapus laporan');
        }
    }

    
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $this->ensureAuthenticated($request);
            if (!$user) {
                return $this->unauthorized();
            }

            $validator = Validator::make($request->all(), [
                'period' => 'nullable|in:all,weekly,monthly,yearly',
                'id_desa' => 'nullable|integer|exists:desa,id',
                'id_pelapor' => 'nullable|integer|exists:pengguna,id',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $validated = $validator->validated();

            $period = $validated['period'] ?? 'all';
            $idDesa = isset($validated['id_desa']) ? (int) $validated['id_desa'] : null;
            $idPelapor = isset($validated['id_pelapor']) ? (int) $validated['id_pelapor'] : null;

            if ($user->role === 'Warga') {
                if ($idPelapor !== null && $idPelapor !== (int) $user->id) {
                    return $this->deniedByPolicy('Warga hanya dapat melihat statistik miliknya sendiri');
                }

                $idPelapor = (int) $user->id;
            }

            $cacheContext = [
                'period' => $period,
                'id_desa' => $idDesa,
                'id_pelapor' => $idPelapor,
            ];

            $cacheKey = 'laporans.statistics.' . md5(json_encode($cacheContext));

            $data = Cache::remember($cacheKey, 300, function () use ($period, $idDesa, $idPelapor) {
                $query = Laporans::query()->whereNull('laporans.deleted_at');

                if ($period !== 'all') {
                    switch ($period) {
                        case 'weekly':
                            $query->where('laporans.created_at', '>=', now()->subDays(7));
                            break;
                        case 'monthly':
                            $query->where('laporans.created_at', '>=', now()->subMonth());
                            break;
                        case 'yearly':
                            $query->where('laporans.created_at', '>=', now()->subYear());
                            break;
                    }
                }

                if ($idDesa !== null) {
                    $query->where('laporans.id_desa', $idDesa);
                }

                if ($idPelapor !== null) {
                    $query->where('laporans.id_pelapor', $idPelapor);
                }

                $statusCounters = (clone $query)
                    ->selectRaw('laporans.status as status, COUNT(*) as total')
                    ->groupBy('laporans.status')
                    ->pluck('total', 'status');

                $total_laporan = (int) $statusCounters->sum();
                $laporan_perlu_verifikasi = (int) ($statusCounters['Draft'] ?? 0) + (int) ($statusCounters['Menunggu Verifikasi'] ?? 0);
                $laporan_ditindak = (int) ($statusCounters['Diverifikasi'] ?? 0) + (int) ($statusCounters['Diproses'] ?? 0);
                $laporan_selesai = (int) ($statusCounters['Selesai'] ?? 0);
                $laporan_ditolak = (int) ($statusCounters['Ditolak'] ?? 0);

                $weeklyBuckets = (clone $query)
                    ->selectRaw('DATE(laporans.created_at) as day, COUNT(*) as total')
                    ->where('laporans.created_at', '>=', now()->subDays(6)->startOfDay())
                    ->groupBy('day')
                    ->pluck('total', 'day');

                $weekly_stats = [];
                for ($i = 6; $i >= 0; $i--) {
                    $day = now()->subDays($i);
                    $dayKey = $day->format('Y-m-d');
                    $weekly_stats[strtolower($day->format('D'))] = (int) ($weeklyBuckets[$dayKey] ?? 0);
                }

                
                $categories_stats = (clone $query)
                    ->join('kategori_bencana', 'laporans.id_kategori_bencana', '=', 'kategori_bencana.id')
                    ->select('kategori_bencana.nama_kategori as category_name', DB::raw('count(*) as count'))
                    ->groupBy('kategori_bencana.id', 'kategori_bencana.nama_kategori')
                    ->orderBy('count', 'desc')
                    ->get()
                    ->keyBy('category_name')
                    ->map(fn($item) => $item->count)
                    ->toArray();

                
                $monthly_trend = (clone $query)
                    ->select(DB::raw('DATE_FORMAT(laporans.created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
                    ->where('laporans.created_at', '>=', now()->subYear())
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->pluck('count', 'month')
                    ->toArray();

                
                $top_pengguna = (clone $query)
                    ->join('pengguna', 'laporans.id_pelapor', '=', 'pengguna.id')
                    ->select('pengguna.nama as pengguna_name', DB::raw('count(*) as laporan_count'))
                    ->whereNull('pengguna.deleted_at')
                    ->groupBy('pengguna.id', 'pengguna.nama')
                    ->orderBy('laporan_count', 'desc')
                    ->limit(5)
                    ->get();

                return [
                    'total_laporan'             => $total_laporan,
                    'laporan_perlu_verifikasi'  => $laporan_perlu_verifikasi,
                    'laporan_ditindak'          => $laporan_ditindak,
                    'laporan_selesai'           => $laporan_selesai,
                    'laporan_ditolak'           => $laporan_ditolak,
                    'laporan_baru'              => $laporan_perlu_verifikasi,
                    'laporan_ditangani'         => $laporan_ditindak,
                    'weekly_stats'              => $weekly_stats,
                    'categories_stats'          => $categories_stats,
                    'monthly_trend'             => $monthly_trend,
                    'top_pengguna'              => $top_pengguna,
                ];
            });

            return $this->successResponse('Statistik laporan berhasil diambil', $data);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil statistik laporan', ['error' => $e->getMessage()]);
            return $this->internalError('Gagal mengambil statistik laporan');
        }
    }
}
