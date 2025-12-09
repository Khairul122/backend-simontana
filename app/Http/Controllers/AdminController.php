<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Get(
 *      path="/api/admin/pengguna",
 *      tags={"Admin Management"},
 *      summary="Get All Users (Admin Only)",
 *      description="Endpoint untuk mendapatkan semua data pengguna (hanya untuk Admin).",
 *      operationId="adminGetPengguna",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Data pengguna berhasil diambil",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Admin pengguna management"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(type="object",
 *                      @OA\Property(property="id", type="integer", example=1),
 *                      @OA\Property(property="nama", type="string", example="Administrator"),
 *                      @OA\Property(property="username", type="string", example="admin"),
 *                      @OA\Property(property="email", type="string", example="admin@example.com"),
 *                      @OA\Property(property="role", type="string", example="Admin"),
 *                      @OA\Property(property="no_telepon", type="string", example="08123456789"),
 *                      @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123")
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Admin access required"
 *      )
 * )
 */

/**
 * @OA\Post(
 *      path="/api/admin/pengguna",
 *      tags={"Admin Management"},
 *      summary="Create New User (Admin Only)",
 *      description="Endpoint untuk membuat pengguna baru (hanya untuk Admin).",
 *      operationId="adminCreatePengguna",
 *      security={{"bearerAuth":{}}},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"nama","username","password","role"},
 *              @OA\Property(property="nama", type="string", example="John Doe", description="Nama lengkap pengguna"),
 *              @OA\Property(property="username", type="string", example="johndoe", description="Username unik"),
 *              @OA\Property(property="password", type="string", format="password", example="123456", description="Password minimal 6 karakter"),
 *              @OA\Property(property="role", type="string", enum={"Admin","PetugasBPBD","OperatorDesa","Warga"}, example="Warga", description="Role pengguna"),
 *              @OA\Property(property="email", type="string", format="email", example="john@example.com", description="Email pengguna"),
 *              @OA\Property(property="no_telepon", type="string", example="08123456789", description="Nomor telepon"),
 *              @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123", description="Alamat lengkap")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="User created successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Admin create pengguna")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Admin access required"
 *      )
 * )
 */

/**
 * @OA\Put(
 *      path="/api/admin/pengguna/{id}",
 *      tags={"Admin Management"},
 *      summary="Update User (Admin Only)",
 *      description="Endpoint untuk mengupdate data pengguna (hanya untuk Admin).",
 *      operationId="adminUpdatePengguna",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID pengguna",
 *          @OA\Schema(type="integer", example=1)
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="nama", type="string", example="John Doe Updated", description="Nama lengkap pengguna"),
 *              @OA\Property(property="email", type="string", format="email", example="john.updated@example.com", description="Email pengguna"),
 *              @OA\Property(property="no_telepon", type="string", example="08123456789", description="Nomor telepon"),
 *              @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 456", description="Alamat lengkap")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="User updated successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Admin update pengguna")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Admin access required"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="User not found"
 *      )
 * )
 */

/**
 * @OA\Delete(
 *      path="/api/admin/pengguna/{id}",
 *      tags={"Admin Management"},
 *      summary="Delete User (Admin Only)",
 *      description="Endpoint untuk menghapus pengguna (hanya untuk Admin).",
 *      operationId="adminDeletePengguna",
 *      security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID pengguna",
 *          @OA\Schema(type="integer", example=1)
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="User deleted successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Admin delete pengguna")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Admin access required"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="User not found"
 *      )
 * )
 */

/**
 * @OA\Get(
 *      path="/api/admin/system-monitoring",
 *      tags={"Admin Management"},
 *      summary="System Monitoring (Admin Only)",
 *      description="Endpoint untuk monitoring sistem (hanya untuk Admin).",
 *      operationId="adminSystemMonitoring",
 *      security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="System monitoring data retrieved",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Admin system monitoring")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthorized"
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden - Admin access required"
 *      )
 * )
 */
class AdminController extends Controller
{
    public function getPengguna()
    {
        try {
            Log::info('Admin pengguna endpoint called');

            $pengguna = Pengguna::select('id', 'nama', 'username', 'email', 'role', 'no_telepon', 'alamat')->get();

            Log::info('Admin pengguna query result', ['count' => $pengguna->count()]);

            $response = [
                'success' => true,
                'message' => 'Admin pengguna management',
                'data' => $pengguna
            ];

            Log::info('Admin pengguna response prepared', ['response_data' => $response]);

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Admin pengguna error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function createPengguna(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Admin create pengguna'
        ]);
    }

    public function updatePengguna(Request $request, $id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Admin update pengguna'
        ]);
    }

    public function deletePengguna($id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Admin delete pengguna'
        ]);
    }

    public function systemMonitoring()
    {
        return response()->json([
            'success' => true,
            'message' => 'Admin system monitoring'
        ]);
    }
}