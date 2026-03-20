<?php

namespace App\Http\Controllers;

use App\Models\RiwayatTindakan;
use App\Models\TindakLanjut;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;


class RiwayatTindakanController extends Controller
{
    private const FULL_RELATIONS = [
        'petugas.desa.kecamatan.kabupaten.provinsi',
        'tindakLanjut.petugas.desa.kecamatan.kabupaten.provinsi',
        'tindakLanjut.laporan.pelapor.desa.kecamatan.kabupaten.provinsi',
        'tindakLanjut.laporan.kategori',
        'tindakLanjut.laporan.desa.kecamatan.kabupaten.provinsi',
        'tindakLanjut.laporan.verifikator.desa.kecamatan.kabupaten.provinsi',
        'tindakLanjut.laporan.penanggungJawab.desa.kecamatan.kabupaten.provinsi',
    ];

    public function __construct(private readonly LogActivityService $logActivityService)
    {
    }

    
    public function index(Request $request): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return $this->unauthorized();
        }

        if ($user->role === 'Warga') {
            return $this->deniedByPolicy('Warga tidak memiliki akses ke data riwayat tindakan operasional');
        }

        $query = RiwayatTindakan::with(self::FULL_RELATIONS);

        if ($request->has('tindaklanjut_id')) {
            $query->where('tindaklanjut_id', $request->tindaklanjut_id);
        }

        if ($request->has('id_petugas')) {
            $query->where('id_petugas', $request->id_petugas);
        }

        $perPage = $this->clampPerPage((int) $request->get('per_page', 20), 20, 100);
        $riwayatTindakans = $query->paginate($perPage);

        return $this->successResponse('Data riwayat tindakan berhasil diambil', $riwayatTindakans);
    }

    
    public function store(Request $request): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return $this->unauthorized();
        }

        $validator = Validator::make($request->all(), [
            'tindaklanjut_id' => 'required|exists:tindaklanjut,id_tindaklanjut',
            'id_petugas' => 'required|exists:pengguna,id',
            'keterangan' => 'required|string',
            'waktu_tindakan' => 'required|date'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $tindakLanjut = TindakLanjut::find($request->tindaklanjut_id);
        if (!$tindakLanjut) {
            return $this->notFoundResponse('Tindak lanjut tidak ditemukan');
        }

        if ($user->cannot('create', [RiwayatTindakan::class, $tindakLanjut, (int) $request->id_petugas])) {
            return $this->deniedByPolicy('Tidak memiliki izin untuk membuat riwayat tindakan ini');
        }

        $payload = $validator->validated();
        if ($user->role !== 'Admin') {
            $payload['id_petugas'] = $user->id;
        }

        $riwayatTindakan = RiwayatTindakan::create($payload);
        $riwayatTindakan->load(self::FULL_RELATIONS);

        $this->logActivityService->log($user->id, $user->role, 'Buat riwayat tindakan', '/api/riwayat-tindakan', $request->ip(), $request->userAgent());

        return $this->successResponse('Riwayat tindakan berhasil dibuat', $riwayatTindakan, 201);
    }

    
    public function show(Request $request, $id): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return $this->unauthorized();
        }

        $riwayatTindakan = RiwayatTindakan::with(self::FULL_RELATIONS)->find($id);

        if (!$riwayatTindakan) {
            return $this->notFoundResponse('Riwayat tindakan tidak ditemukan');
        }

        if ($user->cannot('view', $riwayatTindakan)) {
            return $this->deniedByPolicy('Tidak memiliki izin untuk melihat riwayat tindakan ini');
        }

        return $this->successResponse('Data riwayat tindakan berhasil diambil', $riwayatTindakan);
    }

    
    public function update(Request $request, $id): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return $this->unauthorized();
        }

        $validator = Validator::make($request->all(), [
            'keterangan' => 'sometimes|required|string',
            'waktu_tindakan' => 'sometimes|required|date'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $riwayatTindakan = RiwayatTindakan::find($id);

        if (!$riwayatTindakan) {
            return $this->notFoundResponse('Riwayat tindakan tidak ditemukan');
        }

        if ($user->cannot('update', $riwayatTindakan)) {
            return $this->deniedByPolicy('Tidak memiliki izin untuk mengubah riwayat tindakan ini');
        }

        $riwayatTindakan->update($validator->validated());
        $riwayatTindakan->load(self::FULL_RELATIONS);

        $this->logActivityService->log($user->id, $user->role, 'Update riwayat tindakan', '/api/riwayat-tindakan/' . $id, $request->ip(), $request->userAgent());

        return $this->successResponse('Riwayat tindakan berhasil diupdate', $riwayatTindakan);
    }

    
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return $this->unauthorized();
        }

        $riwayatTindakan = RiwayatTindakan::find($id);

        if (!$riwayatTindakan) {
            return $this->notFoundResponse('Riwayat tindakan tidak ditemukan');
        }

        if ($user->cannot('delete', $riwayatTindakan)) {
            return $this->deniedByPolicy('Tidak memiliki izin untuk menghapus riwayat tindakan ini');
        }

        $this->logActivityService->log($user->id, $user->role, 'Hapus riwayat tindakan', '/api/riwayat-tindakan/' . $id, $request->ip(), $request->userAgent());

        $riwayatTindakan->delete();

        return $this->successResponse('Riwayat tindakan berhasil dihapus');
    }
}
