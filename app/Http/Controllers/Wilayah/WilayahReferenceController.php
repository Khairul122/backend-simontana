<?php

namespace App\Http\Controllers\Wilayah;

use App\Http\Controllers\Controller;
use App\Services\WilayahManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WilayahReferenceController extends Controller
{
    private const MAX_LIMIT = 200;

    public function __construct(private WilayahManagementService $wilayahManagementService)
    {
    }

    private function resolveLimit(Request $request, int $default = 50): int
    {
        $limit = (int) $request->get('limit', $default);

        if ($limit < 1) {
            return $default;
        }

        return min($limit, self::MAX_LIMIT);
    }

    public function getAllProvinsi(Request $request): JsonResponse
    {
        $provinsi = $this->wilayahManagementService
            ->buildQuery('provinsi', $request->get('include', null))
            ->orderBy('nama')
            ->limit($this->resolveLimit($request, 100))
            ->get();

        return $this->successResponse('Data provinsi berhasil diambil', $provinsi);
    }

    public function getProvinsiById(Request $request, $id): JsonResponse
    {
        $provinsi = $this->wilayahManagementService
            ->buildQuery('provinsi', $request->get('include', null))
            ->find($id);

        if (!$provinsi) {
            return $this->notFoundResponse('Provinsi tidak ditemukan');
        }

        return $this->successResponse('Data provinsi berhasil diambil', $provinsi);
    }

    public function getKabupatenByProvinsi(Request $request, $provinsi_id): JsonResponse
    {
        $result = $this->wilayahManagementService->getByParent(
            'kabupaten',
            (int) $provinsi_id,
            $request->get('include', null)
        );

        if (isset($result['error'])) {
            return $this->notFoundResponse($result['error']);
        }

        $kabupaten = $result['data'];

        if ($kabupaten instanceof \Illuminate\Support\Collection) {
            $kabupaten = $kabupaten->take($this->resolveLimit($request));
        }

        return $this->successResponse('Data kabupaten berhasil diambil', $kabupaten);
    }

    public function getKecamatanByKabupaten(Request $request, $kabupaten_id): JsonResponse
    {
        $result = $this->wilayahManagementService->getByParent(
            'kecamatan',
            (int) $kabupaten_id,
            $request->get('include', null)
        );

        if (isset($result['error'])) {
            return $this->notFoundResponse($result['error']);
        }

        $kecamatan = $result['data'];

        if ($kecamatan instanceof \Illuminate\Support\Collection) {
            $kecamatan = $kecamatan->take($this->resolveLimit($request));
        }

        return $this->successResponse('Data kecamatan berhasil diambil', $kecamatan);
    }

    public function getDesaByKecamatan(Request $request, $kecamatan_id): JsonResponse
    {
        $result = $this->wilayahManagementService->getByParent(
            'desa',
            (int) $kecamatan_id,
            $request->get('include', null)
        );

        if (isset($result['error'])) {
            return $this->notFoundResponse($result['error']);
        }

        $desa = $result['data'];

        if ($desa instanceof \Illuminate\Support\Collection) {
            $desa = $desa->take($this->resolveLimit($request));
        }

        return $this->successResponse('Data desa berhasil diambil', $desa);
    }
}
