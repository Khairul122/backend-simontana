<?php

namespace App\Http\Controllers;

use App\Models\TindakLanjut;
use App\Models\Laporans;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;


class TindakLanjutController extends Controller
{
    public function __construct(private readonly LogActivityService $logActivityService)
    {
    }

    
    public function index(Request $request): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return $this->unauthorized();
        }

        $query = TindakLanjut::with(['laporan.pelapor', 'petugas']);

        if ($request->has('laporan_id')) {
            $query->where('laporan_id', $request->laporan_id);
        }

        if ($request->has('id_petugas')) {
            $query->where('id_petugas', $request->id_petugas);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 20);
        $tindakLanjuts = $query->paginate($perPage);

        return $this->successResponse('Data tindak lanjut berhasil diambil', $tindakLanjuts);
    }

    
    public function store(Request $request): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return $this->unauthorized();
        }

        $validator = Validator::make($request->all(), [
            'laporan_id' => 'required|exists:laporans,id',
            'id_petugas' => 'required|exists:pengguna,id',
            'tanggal_tanggapan' => 'required|date',
            'status' => 'sometimes|in:Menuju Lokasi,Selesai'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $laporan = Laporans::find($request->laporan_id);
        if (!$laporan) {
            return $this->notFoundResponse('Laporan tidak ditemukan');
        }

        $tindakLanjut = TindakLanjut::create($request->all());

        $tindakLanjut->load(['laporan.pelapor', 'petugas']);

        $this->logActivityService->log($user->id, $user->role, 'Buat tindak lanjut', '/api/tindak-lanjut', $request->ip(), $request->userAgent());

        return $this->successResponse('Tindak lanjut berhasil dibuat', $tindakLanjut, 201);
    }

    
    public function show(Request $request, $id): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return $this->unauthorized();
        }

        $tindakLanjut = TindakLanjut::with(['laporan.pelapor', 'petugas'])->find($id);

        if (!$tindakLanjut) {
            return $this->notFoundResponse('Tindak lanjut tidak ditemukan');
        }

        return $this->successResponse('Data tindak lanjut berhasil diambil', $tindakLanjut);
    }

    
    public function update(Request $request, $id): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return $this->unauthorized();
        }

        $validator = Validator::make($request->all(), [
            'tanggal_tanggapan' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:Menuju Lokasi,Selesai'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $tindakLanjut = TindakLanjut::find($id);

        if (!$tindakLanjut) {
            return $this->notFoundResponse('Tindak lanjut tidak ditemukan');
        }

        $tindakLanjut->update($request->all());

        $tindakLanjut->load(['laporan.pelapor', 'petugas']);

        $this->logActivityService->log($user->id, $user->role, 'Update tindak lanjut', '/api/tindak-lanjut/' . $id, $request->ip(), $request->userAgent());

        return $this->successResponse('Tindak lanjut berhasil diupdate', $tindakLanjut);
    }

    
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return $this->unauthorized();
        }

        $tindakLanjut = TindakLanjut::find($id);

        if (!$tindakLanjut) {
            return $this->notFoundResponse('Tindak lanjut tidak ditemukan');
        }

        $this->logActivityService->log($user->id, $user->role, 'Hapus tindak lanjut', '/api/tindak-lanjut/' . $id, $request->ip(), $request->userAgent());

        $tindakLanjut->delete();

        return $this->successResponse('Tindak lanjut berhasil dihapus');
    }
}
