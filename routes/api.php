<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->group(function () {
    // Public routes (no authentication required)
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/roles', [AuthController::class, 'getRoles']);

    // Protected routes (authentication required)
    Route::middleware('jwt.auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::prefix('users')->group(function () {
    // Public routes (no authentication required)
    // None for now - all user operations require authentication

    // Protected routes (authentication required)
    Route::middleware('jwt.auth')->group(function () {
        // Current user profile routes (all authenticated users)
        Route::get('/profile', [UserController::class, 'profile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);

        // Admin only routes
        Route::middleware('role:Admin')->group(function () {
            Route::get('/', [UserController::class, 'index']); // List all users
            Route::post('/', [UserController::class, 'store']); // Create user
            Route::get('/statistics', [UserController::class, 'statistics']); // User statistics
            Route::get('/{id}', [UserController::class, 'show']); // Get specific user
            Route::put('/{id}', [UserController::class, 'update']); // Update user
            Route::delete('/{id}', [UserController::class, 'destroy']); // Delete user
        });

        // User can view their own data (optional - depends on your requirements)
        // Note: This route might need additional logic to verify ownership
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
    Route::get('/provinsi', [App\Http\Controllers\ProvinsiController::class, 'index']);
    Route::get('/provinsi/{id}', [App\Http\Controllers\ProvinsiController::class, 'show']);
    Route::get('/kabupaten/{provinsi_id}', [App\Http\Controllers\KabupatenController::class, 'getByProvinsi']);
    Route::get('/kecamatan/{kabupaten_id}', [App\Http\Controllers\KecamatanController::class, 'getByKabupaten']);
    Route::get('/desa/{kecamatan_id}', [App\Http\Controllers\DesaController::class, 'getByKecamatan']);
});

// Data Relasi Routes (Public - Read Only)
Route::prefix('provinsi')->group(function () {
    Route::get('/statistics', [App\Http\Controllers\ProvinsiController::class, 'statistics']);
    Route::get('/', [App\Http\Controllers\ProvinsiController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\ProvinsiController::class, 'show']);
});

Route::prefix('kabupaten')->group(function () {
    Route::get('/statistics', [App\Http\Controllers\KabupatenController::class, 'statistics']);
    Route::get('/', [App\Http\Controllers\KabupatenController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\KabupatenController::class, 'show']);
});

Route::prefix('kecamatan')->group(function () {
    Route::get('/statistics', [App\Http\Controllers\KecamatanController::class, 'statistics']);
    Route::get('/', [App\Http\Controllers\KecamatanController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\KecamatanController::class, 'show']);
});

Route::prefix('desa')->group(function () {
    Route::get('/statistics', [App\Http\Controllers\DesaController::class, 'statistics']);
    Route::get('/', [App\Http\Controllers\DesaController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\DesaController::class, 'show']);
});

// BMKG Test Routes (Temporarily Public for Testing)
Route::prefix('bmkg')->group(function () {
    // New BMKG format routes (temporary public for testing)
    Route::prefix('gempa')->group(function () {
        Route::get('/terbaru', [App\Http\Controllers\BmkgController::class, 'getLatestEarthquakeBmkg']); // autogempa.xml format
        Route::get('/terkini', [App\Http\Controllers\BmkgController::class, 'getLatestEarthquakesBmkg']); // gempaterkini.xml format
        Route::get('/dirasakan', [App\Http\Controllers\BmkgController::class, 'getEarthquakeFeltBmkg']); // gempadirasakan.xml format
    });
});

// BMKG Integration Routes (Protected - Sync and View data)
Route::middleware('jwt.auth')->group(function () {
    Route::prefix('bmkg')->group(function () {
        // Original routes
        Route::get('/', [App\Http\Controllers\BmkgController::class, 'index']);
        Route::get('/latest/{jenis?}', [App\Http\Controllers\BmkgController::class, 'showLatest']);
        Route::get('/{jenis}/{id}', [App\Http\Controllers\BmkgController::class, 'show']);
        Route::post('/sync-autogempa', [App\Http\Controllers\BmkgController::class, 'syncAutoEarthquakeData']);
        Route::post('/sync-gempa-terkini', [App\Http\Controllers\BmkgController::class, 'syncLatestEarthquakeData']);
        Route::post('/sync-gempa-dirasakan', [App\Http\Controllers\BmkgController::class, 'syncEarthquakeFeltData']);
        Route::post('/sync-cuaca', [App\Http\Controllers\BmkgController::class, 'syncWeatherData']);
        Route::post('/sync-prakiraan-cuaca', [App\Http\Controllers\BmkgController::class, 'syncWeatherForecastData']);
        Route::post('/sync-peringatan-dini', [App\Http\Controllers\BmkgController::class, 'syncNowcastRssData']);
        Route::post('/sync-detail-peringatan', [App\Http\Controllers\BmkgController::class, 'syncNowcastCapDetail']);
        Route::post('/sync-cap', [App\Http\Controllers\BmkgController::class, 'syncCapData']);
        Route::post('/sync-all', [App\Http\Controllers\BmkgController::class, 'syncAllData']);
        Route::post('/sync-gempa', [App\Http\Controllers\BmkgController::class, 'syncEarthquakeData']); // Alias
        Route::get('/prakiraan-cuaca', [App\Http\Controllers\BmkgController::class, 'getWeatherForecast']);
        Route::get('/peringatan-dini', [App\Http\Controllers\BmkgController::class, 'getNowcastRssFeed']);
        Route::get('/detail-peringatan/{alert_code}', [App\Http\Controllers\BmkgController::class, 'getNowcastCapDetail']);
    });
});

// Monitoring Routes (Protected - Create, Update, Delete)
Route::middleware('jwt.auth')->group(function () {
    Route::prefix('monitoring')->group(function () {
        Route::get('/', [App\Http\Controllers\MonitoringController::class, 'index']);
        Route::post('/', [App\Http\Controllers\MonitoringController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\MonitoringController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\MonitoringController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\MonitoringController::class, 'destroy']);
    });
});

// Riwayat Tindakan Routes (Protected - Create, Update, Delete)
Route::middleware('jwt.auth')->group(function () {
    Route::prefix('riwayat-tindakan')->group(function () {
        Route::get('/', [App\Http\Controllers\RiwayatTindakanController::class, 'index']);
        Route::post('/', [App\Http\Controllers\RiwayatTindakanController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\RiwayatTindakanController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\RiwayatTindakanController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\RiwayatTindakanController::class, 'destroy']);
    });
});

// Tindak Lanjut Routes (Protected - Create, Update, Delete)
Route::middleware('jwt.auth')->group(function () {
    Route::prefix('tindak-lanjut')->group(function () {
        Route::get('/', [App\Http\Controllers\TindakLanjutController::class, 'index']);
        Route::post('/', [App\Http\Controllers\TindakLanjutController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\TindakLanjutController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\TindakLanjutController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\TindakLanjutController::class, 'destroy']);
    });
});

// Laporan Routes (Protected - Create, Update, Delete)
Route::middleware('jwt.auth')->group(function () {
    Route::prefix('laporan')->group(function () {
        Route::get('/', [App\Http\Controllers\LaporanController::class, 'index']);
        Route::post('/', [App\Http\Controllers\LaporanController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\LaporanController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\LaporanController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\LaporanController::class, 'destroy']);
        Route::post('/{id}/verifikasi', [App\Http\Controllers\LaporanController::class, 'verifikasi']);
        Route::post('/{id}/proses', [App\Http\Controllers\LaporanController::class, 'proses']);
        Route::get('/statistics', [App\Http\Controllers\LaporanController::class, 'statistics']);
    });
});

// Data Relasi Routes (Protected - Create, Update, Delete)
Route::middleware('jwt.auth')->group(function () {
    Route::prefix('provinsi')->group(function () {
        Route::post('/', [App\Http\Controllers\ProvinsiController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\ProvinsiController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\ProvinsiController::class, 'destroy']);
    });

    Route::prefix('kabupaten')->group(function () {
        Route::post('/', [App\Http\Controllers\KabupatenController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\KabupatenController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\KabupatenController::class, 'destroy']);
    });

    Route::prefix('kecamatan')->group(function () {
        Route::post('/', [App\Http\Controllers\KecamatanController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\KecamatanController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\KecamatanController::class, 'destroy']);
    });

    Route::prefix('desa')->group(function () {
        Route::post('/', [App\Http\Controllers\DesaController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\DesaController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\DesaController::class, 'destroy']);
    });
});
