<?php

namespace App\Http\Controllers;

use App\Models\Laporans;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;



class LaporansController extends Controller
{
    private const MAX_RADIUS_KM = 100;
    private const FULL_RELATIONS = [
        'pelapor.desa.kecamatan.kabupaten.provinsi',
        'kategori',
        'desa.kecamatan.kabupaten.provinsi',
        'verifikator.desa.kecamatan.kabupaten.provinsi',
        'penanggungJawab.desa.kecamatan.kabupaten.provinsi',
        'tindakLanjut.petugas.desa.kecamatan.kabupaten.provinsi',
        'tindakLanjut.laporan.pelapor.desa.kecamatan.kabupaten.provinsi',
        'tindakLanjut.laporan.kategori',
        'tindakLanjut.laporan.desa.kecamatan.kabupaten.provinsi',
        'monitoring.operator.desa.kecamatan.kabupaten.provinsi',
        'monitoring.laporan.pelapor.desa.kecamatan.kabupaten.provinsi',
        'monitoring.laporan.kategori',
        'monitoring.laporan.desa.kecamatan.kabupaten.provinsi',
    ];

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
            $query = Laporans::with(self::FULL_RELATIONS);

            
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

    
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $this->ensureAuthenticated($request);
            if (!$user) {
                return $this->unauthorized();
            }

            $validator = Validator::make($request->all(), [
                'judul_laporan' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'tingkat_keparahan' => 'required|string|in:Rendah,Sedang,Tinggi,Kritis',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'id_kategori_bencana' => 'required|exists:kategori_bencana,id',
                'id_desa' => 'required|exists:desa,id',
                'alamat' => 'nullable|string|max:500',
                'jumlah_korban' => 'nullable|integer|min:0',
                'jumlah_rumah_rusak' => 'nullable|integer|min:0',
                'is_prioritas' => 'nullable|boolean',
                'data_tambahan' => 'nullable|array',
                'foto_bukti_1' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'foto_bukti_2' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'foto_bukti_3' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'video_bukti' => 'nullable|file|mimes:mp4,avi,mov|max:10240',
                'waktu_laporan' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $validator->validated();
            $data['id_pelapor'] = $user->id;
            $data['waktu_laporan'] = $request->waktu_laporan ?? now();
            $data['status'] = 'Draft';
            $data['view_count'] = 0;

            
            $fileFields = ['foto_bukti_1', 'foto_bukti_2', 'foto_bukti_3', 'video_bukti'];
            foreach ($fileFields as $field) {
                $fileName = $this->handleFileUpload($request, $field);
                if ($fileName) {
                    $data[$field] = $fileName;
                }
            }

            $data['alamat_lengkap'] = $request->alamat_laporan ?? null;
            $data['jumlah_korban'] = $request->jumlah_korban ?? null;
            $data['jumlah_rumah_rusak'] = $request->jumlah_rumah_rusak ?? null;
            $data['is_prioritas'] = $request->boolean('is_prioritas', false);
            $data['data_tambahan'] = $request->data_tambahan ?? null;

            $laporan = Laporans::create($data);
            $laporan->load(self::FULL_RELATIONS);

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

            
            $laporan->load(self::FULL_RELATIONS);

            return $this->successResponse('Detail laporan berhasil diambil', $laporan);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil detail laporan', [
                'laporan_id' => $laporan->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->internalError('Gagal mengambil detail laporan');
        }
    }

    
    public function update(Request $request, Laporans $laporan): JsonResponse
    {
        try {
            $user = $this->ensureAuthenticated($request);
            if (!$user) {
                return $this->unauthorized();
            }

            
            if ($user->id !== $laporan->id_pelapor && !$user->hasRole(['Admin', 'PetugasBPBD', 'OperatorDesa'])) {
                return $this->forbidden('Tidak memiliki izin untuk mengubah laporan ini');
            }

            $validator = Validator::make($request->all(), [
                'judul_laporan' => 'sometimes|string|max:255',
                'deskripsi' => 'sometimes|string',
                'tingkat_keparahan' => 'sometimes|string|in:Rendah,Sedang,Tinggi,Kritis',
                'latitude' => 'sometimes|numeric|between:-90,90',
                'longitude' => 'sometimes|numeric|between:-180,180',
                'id_kategori_bencana' => 'sometimes|exists:kategori_bencana,id',
                'id_desa' => 'sometimes|exists:desa,id',
                'alamat' => 'nullable|string|max:500',
                'jumlah_korban' => 'nullable|integer|min:0',
                'jumlah_rumah_rusak' => 'nullable|integer|min:0',
                'is_prioritas' => 'nullable|boolean',
                'data_tambahan' => 'nullable|array',
                'foto_bukti_1' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'foto_bukti_2' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'foto_bukti_3' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'video_bukti' => 'nullable|file|mimes:mp4,avi,mov|max:10240',
                'waktu_laporan' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $validator->validated();

            
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

            
            $laporan->load(self::FULL_RELATIONS);

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

            $laporan->delete();

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
            $query = Laporans::query();

            
            if ($request->has('period') && $request->period) {
                switch ($request->period) {
                    case 'weekly':
                        $query->where('created_at', '>=', now()->subDays(7));
                        break;
                    case 'monthly':
                        $query->where('created_at', '>=', now()->subMonth());
                        break;
                    case 'yearly':
                        $query->where('created_at', '>=', now()->subYear());
                        break;
                }
            }

            
            $statusCounters = (clone $query)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $total_laporan = (int) $statusCounters->sum();
            $laporan_perlu_verifikasi = (int) ($statusCounters['Draft'] ?? 0) + (int) ($statusCounters['Menunggu Verifikasi'] ?? 0);
            $laporan_ditindak = (int) ($statusCounters['Diverifikasi'] ?? 0) + (int) ($statusCounters['Diproses'] ?? 0);
            $laporan_selesai = (int) ($statusCounters['Selesai'] ?? 0);
            $laporan_ditolak = (int) ($statusCounters['Ditolak'] ?? 0);

            $weeklyBuckets = Laporans::query()
                ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
                ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                ->groupBy('day')
                ->pluck('total', 'day');

            $weekly_stats = [];
            for ($i = 6; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $dayKey = $day->format('Y-m-d');
                $weekly_stats[strtolower($day->format('D'))] = (int) ($weeklyBuckets[$dayKey] ?? 0);
            }

            
            $categories_stats = DB::table('laporans')
                ->join('kategori_bencana', 'laporans.id_kategori_bencana', '=', 'kategori_bencana.id')
                ->select('kategori_bencana.nama_kategori as category_name', DB::raw('count(*) as count'))
                ->when($request->period, function ($q, $period) {
                    return match ($period) {
                        'weekly' => $q->where('laporans.created_at', '>=', now()->subDays(7)),
                        'monthly' => $q->where('laporans.created_at', '>=', now()->subMonth()),
                        'yearly' => $q->where('laporans.created_at', '>=', now()->subYear()),
                        default => $q,
                    };
                })
                ->groupBy('kategori_bencana.id', 'kategori_bencana.nama_kategori')
                ->orderBy('count', 'desc')
                ->get()
                ->keyBy('category_name')
                ->map(function ($item) {
                    return $item->count;
                })
                ->toArray();

            
            $monthly_trend = DB::table('laporans')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', now()->subYear())
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('count', 'month')
                ->toArray();

            
            $top_pengguna = DB::table('laporans')
                ->join('pengguna', 'laporans.id_pelapor', '=', 'pengguna.id')
                ->select('pengguna.nama as pengguna_name', DB::raw('count(*) as laporan_count'))
                ->when($request->period, function ($q, $period) {
                    return match ($period) {
                        'weekly' => $q->where('laporans.created_at', '>=', now()->subDays(7)),
                        'monthly' => $q->where('laporans.created_at', '>=', now()->subMonth()),
                        'yearly' => $q->where('laporans.created_at', '>=', now()->subYear()),
                        default => $q,
                    };
                })
                ->groupBy('pengguna.id', 'pengguna.nama')
                ->orderBy('laporan_count', 'desc')
                ->limit(5)
                ->get();

            return $this->successResponse('Statistik laporan berhasil diambil', [
                'total_laporan' => $total_laporan,
                'laporan_perlu_verifikasi' => $laporan_perlu_verifikasi,
                'laporan_ditindak' => $laporan_ditindak,
                'laporan_selesai' => $laporan_selesai,
                'laporan_ditolak' => $laporan_ditolak,
                'laporan_baru' => $laporan_perlu_verifikasi,
                'laporan_ditangani' => $laporan_ditindak,
                'weekly_stats' => $weekly_stats,
                'categories_stats' => $categories_stats,
                'monthly_trend' => $monthly_trend,
                'top_pengguna' => $top_pengguna,
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil statistik laporan', ['error' => $e->getMessage()]);
            return $this->internalError('Gagal mengambil statistik laporan');
        }
    }

    
}
