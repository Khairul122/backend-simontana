<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="SIMONTA BENCANA API",
 *     version="2.0.0",
 *     description="API Documentation untuk Sistem Informasi Monitoring Bencana dengan Access Token Authentication",
 *     contact={
 *         "name": "Development Team",
 *         "email": "dev@simonta.id"
 *     }
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="SIMONTA BENCANA API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="jwt",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="token",
 *     description="Gunakan access token yang didapat dari login endpoint. Format: Bearer {token}"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="Authentication endpoints untuk login, registrasi, dan token management"
 * )
 *
 * @OA\Tag(
 *     name="User Management",
 *     description="User management endpoints untuk CRUD operations dan user statistics"
 * )
 */
class SwaggerController extends Controller
{
    // Controller ini hanya untuk dokumentasi Swagger
    // Implementasi ada di AuthController dan UserController lainnya
}