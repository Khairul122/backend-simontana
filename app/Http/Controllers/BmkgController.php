<?php

namespace App\Http\Controllers;

use App\Services\BmkgService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;


class BmkgController extends Controller
{
    protected $bmkgService;

    public function __construct(BmkgService $bmkgService)
    {
        $this->bmkgService = $bmkgService;
    }

    
    public function index(): JsonResponse
    {
        try {
            $data = [
                'gempa_terbaru' => $this->bmkgService->getGempaTerbaru(),
                'daftar_gempa' => $this->bmkgService->getDaftarGempa(),
                'gempa_dirasakan' => $this->bmkgService->getGempaDirasakan(),
                'cache_status' => $this->bmkgService->getCacheStatus()
            ];

            return $this->successResponse('Data BMKG berhasil diambil', $data);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil data BMKG', ['error' => $e->getMessage()]);
            return $this->internalError('Gagal mengambil data BMKG');
        }
    }

    
    public function getGempaTerbaru(): JsonResponse
    {
        try {
            $data = $this->bmkgService->getGempaTerbaru();

            if (!$data) {
                return $this->notFoundResponse('Data gempa terbaru tidak tersedia');
            }

            return $this->successResponse('Data gempa terbaru berhasil diambil', $data);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil data gempa terbaru', ['error' => $e->getMessage()]);
            return $this->internalError('Gagal mengambil data gempa terbaru');
        }
    }

    
    public function getDaftarGempa(): JsonResponse
    {
        try {
            $data = $this->bmkgService->getDaftarGempa();

            if (!$data) {
                return $this->notFoundResponse('Data daftar gempa tidak tersedia');
            }

            return $this->successResponse('Daftar gempa berhasil diambil', $data);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil daftar gempa', ['error' => $e->getMessage()]);
            return $this->internalError('Gagal mengambil daftar gempa');
        }
    }

    
    public function getGempaDirasakan(): JsonResponse
    {
        try {
            $data = $this->bmkgService->getGempaDirasakan();

            if (!$data) {
                return $this->notFoundResponse('Data gempa dirasakan tidak tersedia');
            }

            return $this->successResponse('Data gempa dirasakan berhasil diambil', $data);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil data gempa dirasakan', ['error' => $e->getMessage()]);
            return $this->internalError('Gagal mengambil data gempa dirasakan');
        }
    }

    
    public function getPrakiraanCuaca(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'wilayah_id' => 'required|string'
            ]);

            $data = $this->bmkgService->getPrakiraanCuaca($request->wilayah_id);

            if (!$data) {
                return $this->notFoundResponse('Data prakiraan cuaca tidak tersedia');
            }

            return $this->successResponse('Data prakiraan cuaca berhasil diambil', $data);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil data prakiraan cuaca', [
                'error' => $e->getMessage(),
                'wilayah_id' => $request->input('wilayah_id'),
            ]);

            return $this->internalError('Gagal mengambil data prakiraan cuaca');
        }
    }

    
    public function getPeringatanTsunami(): JsonResponse
    {
        try {
            $data = $this->bmkgService->getPeringatanTsunami();

            if (!$data) {
                return $this->notFoundResponse('Data peringatan tsunami tidak tersedia');
            }

            return $this->successResponse('Data peringatan tsunami berhasil diambil', $data);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil data peringatan tsunami', ['error' => $e->getMessage()]);
            return $this->internalError('Gagal mengambil data peringatan tsunami');
        }
    }

    
    public function clearCache(): JsonResponse
    {
        try {
            $this->bmkgService->clearCache();

            return $this->successResponse('Cache BMKG berhasil dibersihkan');

        } catch (\Exception $e) {
            Log::error('Gagal membersihkan cache BMKG', ['error' => $e->getMessage()]);
            return $this->internalError('Gagal membersihkan cache BMKG');
        }
    }

    
    public function getCacheStatus(): JsonResponse
    {
        try {
            $status = $this->bmkgService->getCacheStatus();

            return $this->successResponse('Status cache berhasil diambil', $status);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil status cache BMKG', ['error' => $e->getMessage()]);
            return $this->internalError('Gagal mengambil status cache');
        }
    }
}
