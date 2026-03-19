<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    protected AuthService $authService;
    protected LogActivityService $logActivityService;

    public function __construct(AuthService $authService, LogActivityService $logActivityService)
    {
        $this->authService = $authService;
        $this->logActivityService = $logActivityService;
    }

    
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $userData = $this->authService->register($request->validated());

            $this->logActivityService->log($userData['id'], $userData['role'], 'Registrasi pengguna baru', '/api/auth/register', $request->ip(), $request->userAgent());

            return $this->successResponse('Registrasi berhasil', $userData, 201);

        } catch (\Exception $e) {
            Log::error('Registrasi gagal', [
                'error' => $e->getMessage(),
                'username' => $request->input('username'),
                'ip' => $request->ip(),
            ]);

            return $this->internalError('Registrasi gagal');
        }
    }

    
    public function login(LoginRequest $request): JsonResponse
    {
        $loginResult = $this->authService->login($request->username, $request->password);

        if (!$loginResult) {
            return $this->errorResponse('Username/email atau password salah', 401, code: 'INVALID_CREDENTIALS');
        }

        $this->logActivityService->log($loginResult['user']['id'], $loginResult['user']['role'], 'Login berhasil', '/api/auth/login', $request->ip(), $request->userAgent());

        return $this->successResponse('Login berhasil', $loginResult);
    }

    
    public function logout(Request $request): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);

        if (!$user) {
            return $this->unauthorized('Tidak ada sesi login');
        }

        $this->logActivityService->log($user->id, $user->role, 'Logout', '/api/auth/logout', $request->ip(), $request->userAgent());

        $this->authService->logout($user);

        return $this->successResponse('Logout berhasil');
    }

    
    public function refresh(Request $request): JsonResponse
    {
        try {
            $token = $this->authService->refresh();

            if (!$token) {
                return $this->errorResponse('Gagal memperbarui token', 401, code: 'TOKEN_REFRESH_FAILED');
            }

            return $this->successResponse('Token berhasil diperbarui', [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Token tidak valid', 401, code: 'TOKEN_INVALID');
        }
    }

    
    public function me(Request $request): JsonResponse
    {
        $user = $this->ensureAuthenticated($request);

        if (!$user) {
            return $this->unauthorized();
        }

        return $this->successResponse('Data user berhasil diambil', $this->authService->getCurrentUser($user));
    }

    
    public function getRoles(): JsonResponse
    {
        $roles = Cache::remember('auth.roles', now()->addHours(24), static function () {
            return Pengguna::getAvailableRoles();
        });

        return $this->successResponse('Daftar role tersedia', $roles);
    }

}
