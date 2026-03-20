<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    protected function requestId(): ?string
    {
        $request = request();

        if (!$request) {
            return null;
        }

        return $request->attributes->get('request_id')
            ?? $request->headers->get('X-Request-Id');
    }

    protected function successResponse(string $message, mixed $data = null, int $status = 200, array $extra = []): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data instanceof LengthAwarePaginator) {
            $payload['data'] = $data->items();
            $payload['meta'] = [
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                ],
            ];
        } elseif ($data !== null) {
            $payload['data'] = $data;
        }

        $requestId = $this->requestId();
        if ($requestId) {
            $payload['request_id'] = $requestId;
        }

        return response()->json(array_merge($payload, $extra), $status);
    }

    protected function errorResponse(
        string $message,
        int $status = 400,
        array $extra = [],
        ?string $code = null,
        mixed $details = null
    ): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
            'code' => $code ?? $this->defaultErrorCode($status),
        ];

        if ($details !== null) {
            $payload['details'] = $details;
        }

        $requestId = $this->requestId();
        if ($requestId) {
            $payload['request_id'] = $requestId;
        }

        return response()->json(array_merge($payload, $extra), $status);
    }

    protected function validationErrorResponse(mixed $errors, string $message = 'Validasi gagal'): JsonResponse
    {
        return $this->errorResponse($message, 422, ['errors' => $errors], 'VALIDATION_ERROR', $errors);
    }

    protected function notFoundResponse(string $message = 'Data tidak ditemukan'): JsonResponse
    {
        return $this->errorResponse($message, 404, code: 'RESOURCE_NOT_FOUND');
    }

    protected function authenticatedUser(Request $request): ?Pengguna
    {
        $user = $request->user();

        if ($user instanceof Pengguna) {
            return $user;
        }

        $fallbackUser = app('auth')->user();

        return $fallbackUser instanceof Pengguna ? $fallbackUser : null;
    }

    protected function unauthorized(string $message = 'Token tidak valid'): JsonResponse
    {
        return $this->errorResponse($message, 401, code: 'UNAUTHORIZED');
    }

    protected function forbidden(string $message = 'Akses ditolak'): JsonResponse
    {
        return $this->errorResponse($message, 403, code: 'FORBIDDEN');
    }

    protected function deniedByPolicy(string $message = 'Anda tidak memiliki izin untuk melakukan tindakan ini'): JsonResponse
    {
        return $this->errorResponse($message, 403, code: 'INSUFFICIENT_PERMISSIONS');
    }

    protected function internalError(string $message = 'Terjadi kesalahan pada server'): JsonResponse
    {
        return $this->errorResponse($message, 500);
    }

    protected function clampPerPage(int $requested, int $default = 15, int $max = 100): int
    {
        if ($requested < 1) {
            return $default;
        }

        return min($requested, $max);
    }

    private function defaultErrorCode(int $status): string
    {
        if ($status >= 500) {
            return 'INTERNAL_SERVER_ERROR';
        }

        return match ($status) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'RESOURCE_NOT_FOUND',
            409 => 'CONFLICT',
            422 => 'VALIDATION_ERROR',
            429 => 'RATE_LIMITED',
            default => 'REQUEST_FAILED',
        };
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
