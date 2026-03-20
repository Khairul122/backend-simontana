<?php

namespace App\Http\Controllers\Wilayah;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Services\WilayahManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WilayahListingController extends Controller
{
    public function __construct(private WilayahManagementService $wilayahManagementService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $jenis = $this->wilayahManagementService->normalizeJenis($request->get('jenis', null));
        $include = $request->get('include', null);
        $perPage = $this->clampPerPage((int) $request->get('per_page', 15), 15, 100);

        if (!$jenis) {
            $cacheKey = 'wilayah:index:all:' . md5((string) $request->get('include', ''));

            $data = Cache::remember($cacheKey, now()->addMinutes(15), function () {
                return collect($this->wilayahManagementService->buildAllWilayahData());
            });

            return $this->successResponse('Data wilayah berhasil diambil', $data);
        }

            $data = $this->wilayahManagementService
                ->buildQuery($jenis, $include)
                ->orderBy('nama')
                ->paginate($perPage);

        return $this->successResponse('Data wilayah berhasil diambil', $data);
    }

    public function showById(Request $request, $id): JsonResponse
    {
        $jenis = $this->wilayahManagementService->normalizeJenis($request->get('jenis'));
        $include = $request->get('include', null);

        if (!$jenis) {
            return $this->errorResponse('Parameter jenis wajib disertakan', 400);
        }

        $data = $this->wilayahManagementService->buildQuery($jenis, $include)->find($id);

        if (!$data) {
            return $this->notFoundResponse('Wilayah tidak ditemukan');
        }

        $data->jenis = $jenis;

        return $this->successResponse('Detail wilayah berhasil diambil', $data);
    }

    public function getWilayahDetailByDesaId($desa_id): JsonResponse
    {
        $desa = Cache::remember('wilayah:detail:desa:' . (int) $desa_id, now()->addMinutes(15), function () use ($desa_id) {
            return Desa::with(['kecamatan.kabupaten.provinsi', 'laporan.pelapor.desa.kecamatan.kabupaten.provinsi', 'pengguna.desa.kecamatan.kabupaten.provinsi'])->find($desa_id);
        });

        if (!$desa) {
            return $this->notFoundResponse('Desa tidak ditemukan');
        }

        return $this->successResponse('Detail wilayah berhasil diambil', $desa);
    }

    public function getWilayahHierarchyByDesaId($desa_id): JsonResponse
    {
        $desa = Cache::remember('wilayah:hierarchy:desa:' . (int) $desa_id, now()->addMinutes(15), function () use ($desa_id) {
            return Desa::with(['kecamatan.kabupaten.provinsi'])->find($desa_id);
        });

        if (!$desa) {
            return $this->notFoundResponse('Desa tidak ditemukan');
        }

        $hierarchy = [
            'desa' => $desa,
            'kecamatan' => $desa->kecamatan,
            'kabupaten' => $desa->kecamatan->kabupaten,
            'provinsi' => $desa->kecamatan->kabupaten->provinsi,
        ];

        return $this->successResponse('Hirarki wilayah berhasil diambil', $hierarchy);
    }

    public function search(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        $jenis = $request->get('jenis', null);

        if (empty($q)) {
            return $this->errorResponse('Parameter pencarian (q) wajib disertakan', 400);
        }

        $results = $this->wilayahManagementService->search($q, $jenis);

        return $this->successResponse('Hasil pencarian wilayah', $results);
    }
}
