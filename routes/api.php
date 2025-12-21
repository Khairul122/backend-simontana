<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\KategoriBencanaController;
use App\Http\Controllers\ProvinsiController;
use App\Http\Controllers\KabupatenController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\DesaController;
use App\Http\Controllers\TindakLanjutController;
use App\Http\Controllers\RiwayatTindakanController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\BmkgController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/roles', [AuthController::class, 'getRoles']);

    Route::middleware('jwt.auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// User Routes
Route::middleware('jwt.auth')->prefix('users')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);

    Route::middleware('role:Admin')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/statistics', [UserController::class, 'statistics']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });
});

// Helper route to check if token is valid
Route::get('/check-token', function () {
    $user = request()->user();

    if ($user) {
        return response()->json([
            'success' => true,
            'message' => 'Token valid',
            'data' => [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_name' => $user->nama
            ]
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'No valid token'
    ], 401);
})->middleware('jwt.auth')->name('check.token');

// Wilayah Routes (Public - Read Only)
Route::prefix('wilayah')->group(function () {
    Route::get('/provinsi', [ProvinsiController::class, 'index']);
    Route::get('/provinsi/{id}', [ProvinsiController::class, 'show']);
    Route::get('/kabupaten/{provinsi_id}', [KabupatenController::class, 'getByProvinsi']);
    Route::get('/kecamatan/{kabupaten_id}', [KecamatanController::class, 'getByKabupaten']);
    Route::get('/desa/{kecamatan_id}', [DesaController::class, 'getByKecamatan']);
});

// Wilayah Management Routes (Protected - Admin only)
Route::middleware('jwt.auth')->group(function () {
    // Data Relasi Routes (CRUD Operations - Admin only)
    Route::middleware('role:Admin')->group(function () {
        Route::apiResource('provinsi', ProvinsiController::class);
        Route::apiResource('kabupaten', KabupatenController::class);
        Route::apiResource('kecamatan', KecamatanController::class);
        Route::apiResource('desa', DesaController::class);
    });
});

// BMKG Routes (Public & Protected)
Route::prefix('bmkg')->group(function () {
    // Public routes for BMKG earthquake data (no authentication required)
    Route::prefix('gempa')->group(function () {
        Route::get('/terbaru', [BmkgController::class, 'getGempaTerbaru']);
        Route::get('/terkini', [BmkgController::class, 'getDaftarGempa']);
        Route::get('/dirasakan', [BmkgController::class, 'getGempaDirasakan']);
    });

    // Public route for tsunami warnings (critical safety information)
    Route::get('/peringatan-tsunami', [BmkgController::class, 'getPeringatanTsunami']);

    // Protected routes for BMKG management (require authentication)
    Route::middleware('jwt.auth')->group(function () {
        Route::get('/', [BmkgController::class, 'index']);
        Route::get('/cache/status', [BmkgController::class, 'getCacheStatus']);
        Route::post('/cache/clear', [BmkgController::class, 'clearCache']);

        // Weather forecast (requires authentication)
        Route::get('/prakiraan-cuaca', [BmkgController::class, 'getPrakiraanCuaca']);
    });
});

// Main Application Routes (Protected)
Route::middleware('jwt.auth')->group(function () {
    // Laporan Routes
    Route::apiResource('laporans', LaporanController::class);
    Route::patch('/laporans/{id}/verifikasi', [LaporanController::class, 'verifikasi']);
    Route::patch('/laporans/{id}/proses', [LaporanController::class, 'proses']);
    Route::get('/laporans/{id}/riwayat', [LaporanController::class, 'riwayat']);
    Route::get('/laporans/statistics', [LaporanController::class, 'statistics']);

    // Kategori Bencana Routes (Read - all authenticated users, Write - Admin only)
    Route::get('/kategori-bencana', [KategoriBencanaController::class, 'index']);
    Route::get('/kategori-bencana/{id}', [KategoriBencanaController::class, 'show']);

    Route::middleware('role:Admin')->group(function () {
        Route::post('/kategori-bencana', [KategoriBencanaController::class, 'store']);
        Route::put('/kategori-bencana/{id}', [KategoriBencanaController::class, 'update']);
        Route::patch('/kategori-bencana/{id}', [KategoriBencanaController::class, 'update']);
        Route::delete('/kategori-bencana/{id}', [KategoriBencanaController::class, 'destroy']);
    });

    // Tindak Lanjut Routes
    Route::apiResource('tindak-lanjut', TindakLanjutController::class);

    // Riwayat Tindakan Routes
    Route::apiResource('riwayat-tindakan', RiwayatTindakanController::class);

    // Monitoring Routes
    Route::apiResource('monitoring', MonitoringController::class);
});