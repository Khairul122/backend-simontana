<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use App\Models\Laporans;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;


class MonitoringController extends Controller
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

        if ($user->role === 'Warga') {
            return $this->deniedByPolicy('Warga tidak memiliki akses ke data monitoring operasional');
        }

        $query = Monitoring::with(['laporan', 'operator']);

        if ($request->has('id_laporan')) {
            $query->where('id_laporan', $request->id_laporan);
        }

        if ($request->has('id_operator')) {
            $query->where('id_operator', $request->id_operator);
        }

        $perPage = $this->clampPerPage((int) $request->get('per_page', 20), 20, 100);
        $monitorings = $query->paginate($perPage);

        return $this->successResponse('Data monitoring berhasil diambil', $monitorings);
    }

    
    public function store(Request $request): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return $this->unauthorized();
        }

        $validator = Validator::make($request->all(), [
            'id_laporan' => 'required|exists:laporans,id',
            'id_operator' => 'required|exists:pengguna,id',
            'waktu_monitoring' => 'required|date',
            'hasil_monitoring' => 'required|string',
            'koordinat_gps' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $laporan = Laporans::find($request->id_laporan);
        if (!$laporan) {
            return $this->notFoundResponse('Laporan tidak ditemukan');
        }

        if ($user->cannot('create', [Monitoring::class, $laporan, (int) $request->id_operator])) {
            return $this->deniedByPolicy('Tidak memiliki izin untuk membuat monitoring pada laporan ini');
        }

        $payload = $validator->validated();
        if ($user->role !== 'Admin') {
            $payload['id_operator'] = $user->id;
        }

        $monitoring = Monitoring::create($payload);
        $monitoring->load(['laporan', 'operator']);

        $this->logActivityService->log($user->id, $user->role, 'Buat monitoring', '/api/monitoring', $request->ip(), $request->userAgent());

        return $this->successResponse('Monitoring berhasil dibuat', $monitoring, 201);
    }

    
    public function show(Request $request, $id): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return $this->unauthorized();
        }

        $monitoring = Monitoring::with(['laporan', 'operator'])->find($id);

        if (!$monitoring) {
            return $this->notFoundResponse('Monitoring tidak ditemukan');
        }

        if ($user->cannot('view', $monitoring)) {
            return $this->deniedByPolicy('Tidak memiliki izin untuk melihat monitoring ini');
        }

        return $this->successResponse('Data monitoring berhasil diambil', $monitoring);
    }

    
    public function update(Request $request, $id): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return $this->unauthorized();
        }

        $validator = Validator::make($request->all(), [
            'id_laporan' => 'sometimes|exists:laporans,id',
            'id_operator' => 'sometimes|exists:pengguna,id',
            'waktu_monitoring' => 'sometimes|date',
            'hasil_monitoring' => 'sometimes|string',
            'koordinat_gps' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $monitoring = Monitoring::find($id);

        if (!$monitoring) {
            return $this->notFoundResponse('Monitoring tidak ditemukan');
        }

        if ($request->filled('id_operator') && (int) $request->id_operator !== $monitoring->id_operator) {
            return $this->deniedByPolicy('Perubahan id_operator tidak diizinkan');
        }

        $operatorId = (int) ($request->input('id_operator', $monitoring->id_operator));
        if ($user->cannot('update', [$monitoring, $operatorId])) {
            return $this->deniedByPolicy('Tidak memiliki izin untuk mengubah monitoring ini');
        }

        $payload = $validator->validated();
        if ($user->role !== 'Admin') {
            $payload['id_operator'] = $user->id;
        }

        $monitoring->update($payload);
        $monitoring->load(['laporan', 'operator']);

        $this->logActivityService->log($user->id, $user->role, 'Update monitoring', '/api/monitoring/' . $id, $request->ip(), $request->userAgent());

        return $this->successResponse('Monitoring berhasil diupdate', $monitoring);
    }

    
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return $this->unauthorized();
        }

        $monitoring = Monitoring::find($id);

        if (!$monitoring) {
            return $this->notFoundResponse('Monitoring tidak ditemukan');
        }

        if ($user->cannot('delete', $monitoring)) {
            return $this->deniedByPolicy('Tidak memiliki izin untuk menghapus monitoring ini');
        }

        $this->logActivityService->log($user->id, $user->role, 'Hapus monitoring', '/api/monitoring/' . $id, $request->ip(), $request->userAgent());

        $monitoring->delete();

        return $this->successResponse('Monitoring berhasil dihapus');
    }
}
