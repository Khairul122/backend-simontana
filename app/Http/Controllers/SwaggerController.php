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
 *     url="{protocol}://{host}/api",
 *     description="SIMONTA BENCANA API Server",
 *     @OA\ServerVariable(
 *         serverVariable="protocol",
 *         default="http",
 *         enum={"http", "https"}
 *     ),
 *     @OA\ServerVariable(
 *         serverVariable="host",
 *         default="localhost:8000",
 *         description="Alamat host API server"
 *     )
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="jwt",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Gunakan access token JWT yang didapat dari login endpoint. Format: Bearer {token}"
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
 *
 * @OA\Tag(
 *     name="Kategori Bencana",
 *     description="Kategori bencana management endpoints untuk CRUD operations"
 * )
 */
class SwaggerController extends Controller
{
    // Controller ini hanya untuk dokumentasi Swagger
    // Implementasi ada di AuthController dan UserController lainnya
}