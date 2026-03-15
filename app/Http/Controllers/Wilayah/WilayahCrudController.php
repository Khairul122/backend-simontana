<?php

namespace App\Http\Controllers\Wilayah;

use App\Http\Controllers\Controller;
use App\Services\WilayahManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WilayahCrudController extends Controller
{
    public function __construct(private WilayahManagementService $wilayahManagementService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'jenis' => 'required|in:provinsi,kabupaten,kecamatan,desa',
            'nama' => 'required|string|max:255',
            'id_parent' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $jenis = strtolower($request->jenis);
                    if (in_array($jenis, ['kabupaten', 'kecamatan', 'desa']) && !$value) {
                        $fail('ID parent wajib diisi untuk jenis ' . $jenis . '.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $jenis = $this->wilayahManagementService->normalizeJenis($request->jenis);
        $nama = $request->nama;
        $id_parent = $request->id_parent ? (int) $request->id_parent : null;

        if (!$jenis) {
            return $this->errorResponse('Jenis wilayah tidak valid', 400);
        }

        $result = $this->wilayahManagementService->createByJenis($jenis, $nama, $id_parent);

        if (isset($result['error'])) {
            $statusCode = str_contains($result['error'], 'wajib diisi') ? 422 : 404;
            if ($statusCode === 422) {
                return $this->validationErrorResponse(['id_parent' => [$result['error']]]);
            }

            return $this->notFoundResponse($result['error']);
        }

        $wilayah = $result['model'];

        return $this->successResponse('Wilayah berhasil ditambahkan', [
            'id' => $wilayah->id,
            'nama' => $wilayah->nama,
            'jenis' => $jenis,
            'id_parent' => $jenis !== 'provinsi' ? $id_parent : null,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $jenis = $this->wilayahManagementService->normalizeJenis($request->get('jenis'));
        if (!$jenis) {
            return $this->errorResponse('Parameter jenis wajib disertakan', 400);
        }

        $validator = Validator::make($request->all(), [
            'jenis' => 'required|in:provinsi,kabupaten,kecamatan,desa',
            'nama' => 'required|string|max:255',
            'id_parent' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $innerJenis = strtolower($request->jenis);
                    if (in_array($innerJenis, ['kabupaten', 'kecamatan', 'desa']) && !$value) {
                        $fail('ID parent wajib diisi untuk jenis ' . $innerJenis . '.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $nama = $request->nama;
        $id_parent = $request->id_parent ? (int) $request->id_parent : null;

        $result = $this->wilayahManagementService->updateByJenis($jenis, (int) $id, $nama, $id_parent);

        if (isset($result['not_found'])) {
            return $this->notFoundResponse($result['not_found']);
        }

        if (isset($result['error'])) {
            $statusCode = str_contains($result['error'], 'wajib diisi') ? 422 : 404;
            if ($statusCode === 422) {
                return $this->validationErrorResponse(['id_parent' => [$result['error']]]);
            }

            return $this->notFoundResponse($result['error']);
        }

        $wilayah = $result['model'];

        return $this->successResponse('Wilayah berhasil diperbarui', [
            'id' => $wilayah->id,
            'nama' => $wilayah->nama,
            'jenis' => $jenis,
            'id_parent' => $jenis !== 'provinsi' ? $id_parent : null,
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $jenis = $this->wilayahManagementService->normalizeJenis($request->get('jenis'));
        if (!$jenis) {
            return $this->errorResponse('Parameter jenis wajib disertakan', 400);
        }

        $result = $this->wilayahManagementService->deleteByJenis($jenis, (int) $id);

        if (isset($result['not_found'])) {
            return $this->notFoundResponse($result['not_found']);
        }

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], 400);
        }

        if (isset($result['failed'])) {
            return $this->errorResponse('Gagal menghapus wilayah', 500);
        }

        return $this->successResponse('Wilayah berhasil dihapus');

    }

    public function storeProvinsi(Request $request): JsonResponse
    {
        $request->merge(['jenis' => 'provinsi', 'id_parent' => null]);
        return $this->store($request);
    }

    public function updateProvinsi(Request $request, $id): JsonResponse
    {
        $request->merge(['jenis' => 'provinsi', 'id_parent' => null]);
        return $this->update($request, $id);
    }

    public function destroyProvinsi(Request $request, $id): JsonResponse
    {
        $request->merge(['jenis' => 'provinsi']);
        return $this->destroy($request, $id);
    }

    public function storeKabupaten(Request $request): JsonResponse
    {
        $request->merge([
            'jenis' => 'kabupaten',
            'id_parent' => $request->input('id_parent', $request->input('id_provinsi')),
        ]);
        return $this->store($request);
    }

    public function updateKabupaten(Request $request, $id): JsonResponse
    {
        $request->merge([
            'jenis' => 'kabupaten',
            'id_parent' => $request->input('id_parent', $request->input('id_provinsi')),
        ]);
        return $this->update($request, $id);
    }

    public function destroyKabupaten(Request $request, $id): JsonResponse
    {
        $request->merge(['jenis' => 'kabupaten']);
        return $this->destroy($request, $id);
    }

    public function storeKecamatan(Request $request): JsonResponse
    {
        $request->merge([
            'jenis' => 'kecamatan',
            'id_parent' => $request->input('id_parent', $request->input('id_kabupaten')),
        ]);
        return $this->store($request);
    }

    public function updateKecamatan(Request $request, $id): JsonResponse
    {
        $request->merge([
            'jenis' => 'kecamatan',
            'id_parent' => $request->input('id_parent', $request->input('id_kabupaten')),
        ]);
        return $this->update($request, $id);
    }

    public function destroyKecamatan(Request $request, $id): JsonResponse
    {
        $request->merge(['jenis' => 'kecamatan']);
        return $this->destroy($request, $id);
    }

    public function storeDesa(Request $request): JsonResponse
    {
        $request->merge([
            'jenis' => 'desa',
            'id_parent' => $request->input('id_parent', $request->input('id_kecamatan')),
        ]);
        return $this->store($request);
    }

    public function updateDesa(Request $request, $id): JsonResponse
    {
        $request->merge([
            'jenis' => 'desa',
            'id_parent' => $request->input('id_parent', $request->input('id_kecamatan')),
        ]);
        return $this->update($request, $id);
    }

    public function destroyDesa(Request $request, $id): JsonResponse
    {
        $request->merge(['jenis' => 'desa']);
        return $this->destroy($request, $id);
    }
}
