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

        $query = Monitoring::with(['laporan', 'operator']);

        if ($request->has('id_laporan')) {
            $query->where('id_laporan', $request->id_laporan);
        }

        if ($request->has('id_operator')) {
            $query->where('id_operator', $request->id_operator);
        }

        $perPage = $request->get('per_page', 20);
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

        $monitoring = Monitoring::create($request->all());
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

        $monitoring->update($request->all());
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

        $this->logActivityService->log($user->id, $user->role, 'Hapus monitoring', '/api/monitoring/' . $id, $request->ip(), $request->userAgent());

        $monitoring->delete();

        return $this->successResponse('Monitoring berhasil dihapus');
    }
}
