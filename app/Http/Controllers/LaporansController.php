<?php

namespace App\Http\Controllers;

use App\Models\Laporans;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



class LaporansController extends Controller
{
    
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
                
                \Log::warning("Failed to delete old file: {$oldFile}", ['error' => $e->getMessage()]);
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
                    
                    \Log::warning("Failed to delete file: {$file}", ['error' => $e->getMessage()]);
                }
            }
        }
    }
    
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Laporans::with([
                'pelapor:id,nama,email,alamat,no_telepon',
                'kategori:id,nama_kategori,deskripsi',
                'desa:id,nama,id_kecamatan',
                'desa.kecamatan:id,nama,id_kabupaten',
                'desa.kecamatan.kabupaten:id,nama,id_provinsi',
                'desa.kecamatan.kabupaten.provinsi:id,nama',
                'tindakLanjut:id_tindaklanjut,laporan_id,id_petugas,tanggal_tanggapan,status,created_at',
                'tindakLanjut.petugas:id,nama',
                'tindakLanjut.laporan:id,id_pelapor,judul_laporan,deskripsi,tingkat_keparahan,latitude,longitude,jumlah_korban,jumlah_rumah_rusak,is_prioritas,view_count,status,waktu_laporan,waktu_verifikasi,waktu_selesai,catatan_verifikasi,data_tambahan,foto_bukti_1,foto_bukti_2,foto_bukti_3,video_bukti,id_kategori_bencana,id_desa,alamat_lengkap',
                'tindakLanjut.laporan.pelapor:id,nama,email,no_telepon',
                'monitoring:id_monitoring,id_laporan,id_operator,waktu_monitoring,hasil_monitoring,koordinat_gps,created_at'
            ]);

            
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
                $query->byLocationRadius($request->lat, $request->lng, $request->radius);
            }

            
            $orderBy = $request->get('order_by', 'created_at');
            $orderDirection = $request->get('order_direction', 'desc');
            $query->orderBy($orderBy, $orderDirection);

            
            $limit = $request->get('limit', 15);
            $laporans = $query->paginate($limit);

            return $this->successResponse('Data laporan berhasil diambil', $laporans->items(), 200, [
                'pagination' => [
                    'current_page' => $laporans->currentPage(),
                    'last_page' => $laporans->lastPage(),
                    'per_page' => $laporans->perPage(),
                    'total' => $laporans->total(),
                    'from' => $laporans->firstItem(),
                    'to' => $laporans->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil data laporan: ' . $e->getMessage(), 500, ['data' => null]);
        }
    }

    
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'judul_laporan' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'tingkat_keparahan' => 'required|string|in:Rendah,Sedang,Tinggi,Sangat Tinggi',
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

            $data = $request->all();
            $data['id_pelapor'] = auth()->id();
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
            $laporan->load([
                'pelapor:id,nama,email,alamat,no_telepon',
                'kategori:id,nama_kategori,deskripsi',
                'desa:id,nama,id_kecamatan',
                'desa.kecamatan:id,nama,id_kabupaten',
                'desa.kecamatan.kabupaten:id,nama,id_provinsi',
                'desa.kecamatan.kabupaten.provinsi:id,nama'
            ]);

            return $this->successResponse('Laporan berhasil dibuat', $laporan, 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Gagal membuat laporan: ' . $e->getMessage(), 500, ['data' => null]);
        }
    }

    
    public function show(Laporans $laporan): JsonResponse
    {
        try {
            
            $laporan->incrementViewCount();

            
            $laporan->load([
                'pelapor:id,nama,email,alamat,no_telepon',
                'kategori:id,nama_kategori,deskripsi',
                'desa:id,nama,id_kecamatan',
                'desa.kecamatan:id,nama,id_kabupaten',
                'desa.kecamatan.kabupaten:id,nama,id_provinsi',
                'desa.kecamatan.kabupaten.provinsi:id,nama',
                'tindakLanjut:id_tindaklanjut,laporan_id,id_petugas,tanggal_tanggapan,status,created_at',
                'tindakLanjut.petugas:id,nama',
                'tindakLanjut.laporan:id,id_pelapor,judul_laporan,deskripsi,tingkat_keparahan,latitude,longitude,jumlah_korban,jumlah_rumah_rusak,is_prioritas,view_count,status,waktu_laporan,waktu_verifikasi,waktu_selesai,catatan_verifikasi,data_tambahan,foto_bukti_1,foto_bukti_2,foto_bukti_3,video_bukti,id_kategori_bencana,id_desa,alamat_lengkap',
                'tindakLanjut.laporan.pelapor:id,nama,email,no_telepon',
                'monitoring:id_monitoring,id_laporan,id_operator,waktu_monitoring,hasil_monitoring,koordinat_gps,created_at'
            ]);

            return $this->successResponse('Detail laporan berhasil diambil', $laporan);

        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil detail laporan: ' . $e->getMessage(), 500, ['data' => null]);
        }
    }

    
    public function update(Request $request, Laporans $laporan): JsonResponse
    {
        try {
            
            if (auth()->id() !== $laporan->id_pelapor && !auth()->user()->hasRole(['Admin', 'PetugasBPBD', 'OperatorDesa'])) {
                return $this->forbidden('Tidak memiliki izin untuk mengubah laporan ini');
            }

            $validator = Validator::make($request->all(), [
                'judul_laporan' => 'sometimes|string|max:255',
                'deskripsi' => 'sometimes|string',
                'tingkat_keparahan' => 'sometimes|string|in:Rendah,Sedang,Tinggi,Sangat Tinggi',
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

            $data = $request->all();

            
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
                    $user_id = auth()->id(); 
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

            
            $laporan->load([
                'pelapor:id,nama,email,alamat,no_telepon',
                'kategori:id,nama_kategori,deskripsi',
                'desa:id,nama,id_kecamatan',
                'desa.kecamatan:id,nama,id_kabupaten',
                'desa.kecamatan.kabupaten:id,nama,id_provinsi',
                'desa.kecamatan.kabupaten.provinsi:id,nama'
            ]);

            return $this->successResponse('Laporan berhasil diperbarui', $laporan);

        } catch (\Exception $e) {
            return $this->errorResponse('Gagal memperbarui laporan: ' . $e->getMessage(), 500, ['data' => null]);
        }
    }

    
    public function destroy(Laporans $laporan): JsonResponse
    {
        try {
            
            if (auth()->id() !== $laporan->id_pelapor && !auth()->user()->hasRole(['Admin', 'OperatorDesa'])) {
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
            return $this->errorResponse('Gagal menghapus laporan: ' . $e->getMessage(), 500, ['data' => null]);
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

            
            $total_laporan = $query->count();

            
            $laporan_perlu_verifikasi = $query->clone()
                ->whereIn('status', ['Draft', 'Menunggu Verifikasi'])
                ->count();

            
            $laporan_ditindak = $query->clone()
                ->whereIn('status', ['Diverifikasi', 'Diproses', 'Tindak Lanjut'])
                ->count();

            
            $laporan_selesai = $query->clone()
                ->where('status', 'Selesai')
                ->count();

            
            $laporan_ditolak = $query->clone()
                ->where('status', 'Ditolak')
                ->count();

            
            $weekly_stats = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $count = Laporans::whereDate('created_at', $date)->count();
                $weekly_stats[strtolower(now()->subDays($i)->format('D'))] = $count;
            }

            
            $categories_stats = DB::table('laporans')
                ->join('kategori_bencana', 'laporans.id_kategori_bencana', '=', 'kategori_bencana.id')
                ->select('kategori_bencana.nama_kategori as category_name', DB::raw('count(*) as count'))
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
                ->groupBy('pengguna.id', 'pengguna.nama')
                ->orderBy('laporan_count', 'desc')
                ->limit(5)
                ->get();

            return $this->successResponse('Statistics retrieved successfully', [
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
            return $this->errorResponse('Failed to retrieve statistics: ' . $e->getMessage(), 500, ['data' => null]);
        }
    }

    
}
