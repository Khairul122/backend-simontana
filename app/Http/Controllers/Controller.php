<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    protected function successResponse(string $message, mixed $data = null, int $status = 200, array $extra = []): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json(array_merge($payload, $extra), $status);
    }

    protected function errorResponse(string $message, int $status = 400, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'success' => false,
            'message' => $message,
        ], $extra), $status);
    }

    protected function validationErrorResponse(mixed $errors, string $message = 'Validasi gagal'): JsonResponse
    {
        return $this->errorResponse($message, 422, ['errors' => $errors]);
    }

    protected function notFoundResponse(string $message = 'Data tidak ditemukan'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    protected function authenticatedUser(Request $request): ?Pengguna
    {
        $user = $request->user();

        if ($user instanceof Pengguna) {
            return $user;
        }

        $fallbackUser = auth()->user();

        return $fallbackUser instanceof Pengguna ? $fallbackUser : null;
    }

    protected function unauthorized(string $message = 'Token tidak valid'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    protected function forbidden(string $message = 'Akses ditolak'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    protected function ensureAuthenticated(Request $request): ?Pengguna
    {
        $user = $this->authenticatedUser($request);

        if (!$user) {
            return null;
        }

        return $user;
    }

    protected function ensureAdmin(Request $request): ?Pengguna
    {
        $user = $this->authenticatedUser($request);

        if (!$user || $user->role !== 'Admin') {
            return null;
        }

        return $user;
    }
}
