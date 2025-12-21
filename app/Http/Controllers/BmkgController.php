<?php

namespace App\Http\Controllers;

use App\Services\BmkgService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="BMKG Integration",
 *     description="BMKG real-time data integration endpoints untuk gempa, cuaca, dan peringatan tsunami"
 * )
 */
class BmkgController extends Controller
{
    protected $bmkgService;

    public function __construct(BmkgService $bmkgService)
    {
        $this->bmkgService = $bmkgService;
    }

    /**
     * Get all BMKG data summary
     *
     * @OA\Get(
     *     path="/bmkg",
     *     tags={"BMKG Integration"},
     *     summary="Get all BMKG data summary",
     *     description="Retrieve summary of all BMKG data including latest earthquakes, felt earthquakes, and cache status",
     *     @OA\Response(
     *         response=200,
     *         description="BMKG data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="BMKG data retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="gempa_terbaru", type="object"),
     *                 @OA\Property(property="daftar_gempa", type="object"),
     *                 @OA\Property(property="gempa_dirasakan", type="object"),
     *                 @OA\Property(property="cache_status", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve BMKG data"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $data = [
                'gempa_terbaru' => $this->bmkgService->getGempaTerbaru(),
                'daftar_gempa' => $this->bmkgService->getDaftarGempa(),
                'gempa_dirasakan' => $this->bmkgService->getGempaDirasakan(),
                'cache_status' => $this->bmkgService->getCacheStatus()
            ];

            return response()->json([
                'success' => true,
                'message' => 'BMKG data retrieved successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve BMKG data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest earthquake data
     *
     * @OA\Get(
     *     path="/bmkg/gempa/terbaru",
     *     tags={"BMKG Integration"},
     *     summary="Get latest earthquake data from BMKG",
     *     description="Retrieve the most recent earthquake data from BMKG Open API",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getGempaTerbaru(): JsonResponse
    {
        try {
            $data = $this->bmkgService->getGempaTerbaru();

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve latest earthquake data',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Latest earthquake data retrieved successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve latest earthquake data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get earthquake list
     *
     * @OA\Get(
     *     path="/bmkg/gempa/terkini",
     *     tags={"BMKG Integration"},
     *     summary="Get earthquake list from BMKG",
     *     description="Retrieve list of recent earthquakes from BMKG Open API",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getDaftarGempa(): JsonResponse
    {
        try {
            $data = $this->bmkgService->getDaftarGempa();

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve earthquake list',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Earthquake list retrieved successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve earthquake list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get felt earthquakes
     *
     * @OA\Get(
     *     path="/bmkg/gempa/dirasakan",
     *     tags={"BMKG Integration"},
     *     summary="Get felt earthquakes from BMKG",
     *     description="Retrieve felt earthquake data from BMKG Open API",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getGempaDirasakan(): JsonResponse
    {
        try {
            $data = $this->bmkgService->getGempaDirasakan();

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve felt earthquake data',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Felt earthquake data retrieved successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve felt earthquake data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get weather forecast for specific region
     *
     * @OA\Get(
     *     path="/bmkg/prakiraan-cuaca",
     *     tags={"BMKG Integration"},
     *     summary="Get weather forecast for specific region",
     *     description="Retrieve weather forecast data for a specific region by wilayah_id",
     *     @OA\Parameter(
     *         name="wilayah_id",
     *         in="query",
     *         required=true,
     *         description="Region identifier for weather forecast",
     *         @OA\Schema(type="string", example="JawaBarat")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getPrakiraanCuaca(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'wilayah_id' => 'required|string'
            ]);

            $data = $this->bmkgService->getPrakiraanCuaca($request->wilayah_id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve weather forecast data',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Weather forecast data retrieved successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve weather forecast data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tsunami warning
     *
     * @OA\Get(
     *     path="/bmkg/peringatan-tsunami",
     *     tags={"BMKG Integration"},
     *     summary="Get tsunami warning data from BMKG",
     *     description="Retrieve tsunami warning and alert data from BMKG (public endpoint for critical safety information)",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getPeringatanTsunami(): JsonResponse
    {
        try {
            $data = $this->bmkgService->getPeringatanTsunami();

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve tsunami warning data',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tsunami warning data retrieved successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tsunami warning data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear BMKG cache
     *
     * @OA\Post(
     *     path="/bmkg/cache/clear",
     *     tags={"BMKG Integration"},
     *     summary="Clear BMKG cache",
     *     description="Clear all BMKG cache data to force fresh data retrieval",
     *     @OA\Response(
     *         response=200,
     *         description="Cache cleared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->bmkgService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'BMKG cache cleared successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear BMKG cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache status
     *
     * @OA\Get(
     *     path="/bmkg/cache/status",
     *     tags={"BMKG Integration"},
     *     summary="Get BMKG cache status information",
     *     description="Retrieve cache status information including duration and cached endpoints",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getCacheStatus(): JsonResponse
    {
        try {
            $status = $this->bmkgService->getCacheStatus();

            return response()->json([
                'success' => true,
                'message' => 'Cache status retrieved successfully',
                'data' => $status
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cache status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}