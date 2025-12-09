<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Get(
 *      path="/api/dashboard",
 *      tags={"Dashboard"},
 *      summary="Get User Dashboard",
 *      description="Endpoint untuk mendapatkan dashboard user berdasarkan role.",
 *      operationId="getDashboard",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Dashboard accessed successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Dashboard accessed successfully"),
 *              @OA\Property(property="data",
 *                  @OA\Property(property="user", type="object",
 *                      @OA\Property(property="id", type="integer", example=1),
 *                      @OA\Property(property="nama", type="string", example="Admin Test"),
 *                      @OA\Property(property="username", type="string", example="admintest"),
 *                      @OA\Property(property="role", type="string", example="Admin")
 *                  ),
 *                  @OA\Property(property="role", type="string", example="Admin")
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      )
 * )
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Dashboard accessed successfully',
            'data' => [
                'user' => $request->user(),
                'role' => $request->user()->role
            ]
        ]);
    }
}