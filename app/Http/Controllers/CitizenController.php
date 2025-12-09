<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Get(
 *      path="/api/citizen/disaster-info",
 *      tags={"Citizen Access"},
 *      summary="Get Disaster Information",
 *      description="Endpoint untuk mendapatkan informasi bencana terkini (Warga).",
 *      operationId="citizenGetDisasterInfo",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Disaster information retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Citizen disaster information")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Citizen access required"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/citizen/evacuation-info",
 *      tags={"Citizen Access"},
 *      summary="Get Evacuation Information",
 *      description="Endpoint untuk mendapatkan informasi evakuasi (Warga).",
 *      operationId="citizenGetEvacuationInfo",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Evacuation information retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Citizen evacuation information")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Citizen access required"
 *      )
 * )
 */
class CitizenController extends Controller
{
    public function getDisasterInfo()
    {
        return response()->json([
            'success' => true,
            'message' => 'Citizen disaster information'
        ]);
    }

    public function getEvacuationInfo()
    {
        return response()->json([
            'success' => true,
            'message' => 'Citizen evacuation information'
        ]);
    }
}