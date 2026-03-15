<?php

namespace App\Http\Controllers\Wilayah;

use App\Http\Controllers\Controller;
use App\Services\WilayahManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WilayahReferenceController extends Controller
{
    public function __construct(private WilayahManagementService $wilayahManagementService)
    {
    }

    public function getAllProvinsi(Request $request): JsonResponse
    {
        $provinsi = $this->wilayahManagementService
            ->buildQuery('provinsi', $request->get('include', null))
            ->orderBy('nama')
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

        return $this->successResponse('Data desa berhasil diambil', $desa);
    }
}
