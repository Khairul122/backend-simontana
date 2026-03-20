<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="SIMONTA BENCANA API",
 *     version="3.0.0",
 *     description="Dokumentasi OpenAPI untuk backend SIMONTA BENCANA."
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local API server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="jwt",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Gunakan access token JWT pada header Authorization: Bearer {token}"
 * )
 * @OA\PathItem(path="/")
 */
class OpenApiSpec
{
}
