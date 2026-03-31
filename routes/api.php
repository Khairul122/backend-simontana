<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LaporansController;
use App\Http\Controllers\KategoriBencanaController;
use App\Http\Controllers\TindakLanjutController;
use App\Http\Controllers\RiwayatTindakanController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\BmkgController;
use App\Http\Controllers\Wilayah\WilayahCrudController;
use App\Http\Controllers\Wilayah\WilayahListingController;
use App\Http\Controllers\Wilayah\WilayahReferenceController;
use App\Http\Controllers\Laporan\LaporanWorkflowController;
use App\Http\Controllers\Warga\WargaLaporanDetailController;

/*
|--------------------------------------------------------------------------
| API Routes — versioned under /api/v1/
|--------------------------------------------------------------------------
| Semua route dibungkus prefix v1 untuk mendukung API versioning.
| Klien lama yang masih pakai /api/* tetap bisa dilayani melalui
| alias backward-compat di bawah.
*/

Route::prefix('v1')->group(function () {

    // ─── Auth ──────────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth-login');
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth-register');
        Route::get('/roles', [AuthController::class, 'getRoles']);

        Route::middleware('jwt.auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    // ─── Users ─────────────────────────────────────────────────────────────
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

    // ─── Token check ───────────────────────────────────────────────────────
    Route::get('/check-token', function () {
        $user = request()->user();

        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'Token valid',
                'data'    => [
                    'user_id'   => $user->id,
                    'user_role' => $user->role,
                    'user_name' => $user->nama,
                ],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No valid token'], 401);
    })->middleware('jwt.auth')->name('check.token');

    // ─── Wilayah (public read) ──────────────────────────────────────────────
    Route::prefix('wilayah')->group(function () {
        Route::get('/provinsi', [WilayahReferenceController::class, 'getAllProvinsi']);
        Route::get('/provinsi/{id}', [WilayahReferenceController::class, 'getProvinsiById']);
        Route::get('/kabupaten/{provinsi_id}', [WilayahReferenceController::class, 'getKabupatenByProvinsi']);
        Route::get('/kecamatan/{kabupaten_id}', [WilayahReferenceController::class, 'getKecamatanByKabupaten']);
        Route::get('/desa/{kecamatan_id}', [WilayahReferenceController::class, 'getDesaByKecamatan']);

        Route::get('/detail/{desa_id}', [WilayahListingController::class, 'getWilayahDetailByDesaId']);
        Route::get('/hierarchy/{desa_id}', [WilayahListingController::class, 'getWilayahHierarchyByDesaId']);
        Route::get('/search', [WilayahListingController::class, 'search']);

        Route::get('/', [WilayahListingController::class, 'index']);
        Route::get('/{id}', [WilayahListingController::class, 'showById']);
        Route::post('/', [WilayahCrudController::class, 'store'])->middleware(['jwt.auth', 'role:Admin']);
        Route::put('/{id}', [WilayahCrudController::class, 'update'])->middleware(['jwt.auth', 'role:Admin']);
        Route::delete('/{id}', [WilayahCrudController::class, 'destroy'])->middleware(['jwt.auth', 'role:Admin']);
    });

    // ─── Wilayah CRUD (Admin) ───────────────────────────────────────────────
    Route::middleware(['jwt.auth', 'role:Admin'])->prefix('wilayah')->group(function () {
        Route::post('/provinsi', [WilayahCrudController::class, 'storeProvinsi']);
        Route::put('/provinsi/{id}', [WilayahCrudController::class, 'updateProvinsi']);
        Route::delete('/provinsi/{id}', [WilayahCrudController::class, 'destroyProvinsi']);

        Route::post('/kabupaten', [WilayahCrudController::class, 'storeKabupaten']);
        Route::put('/kabupaten/{id}', [WilayahCrudController::class, 'updateKabupaten']);
        Route::delete('/kabupaten/{id}', [WilayahCrudController::class, 'destroyKabupaten']);

        Route::post('/kecamatan', [WilayahCrudController::class, 'storeKecamatan']);
        Route::put('/kecamatan/{id}', [WilayahCrudController::class, 'updateKecamatan']);
        Route::delete('/kecamatan/{id}', [WilayahCrudController::class, 'destroyKecamatan']);

        Route::post('/desa', [WilayahCrudController::class, 'storeDesa']);
        Route::put('/desa/{id}', [WilayahCrudController::class, 'updateDesa']);
        Route::delete('/desa/{id}', [WilayahCrudController::class, 'destroyDesa']);
    });

    // ─── BMKG (sebagian publik) ─────────────────────────────────────────────
    Route::prefix('bmkg')->group(function () {
        Route::prefix('gempa')->group(function () {
            Route::get('/terbaru', [BmkgController::class, 'getGempaTerbaru']);
            Route::get('/terkini', [BmkgController::class, 'getDaftarGempa']);
            Route::get('/dirasakan', [BmkgController::class, 'getGempaDirasakan']);
        });

        Route::get('/peringatan-dini-cuaca', [BmkgController::class, 'getPeringatanDiniCuaca']);
        Route::get('/prakiraan-cuaca', [BmkgController::class, 'getPrakiraanCuaca']);

        Route::middleware('jwt.auth')->group(function () {
            Route::get('/', [BmkgController::class, 'index']);
            Route::get('/cache/status', [BmkgController::class, 'getCacheStatus']);
            Route::post('/cache/clear', [BmkgController::class, 'clearCache']);
        });
    });

    // ─── Laporans ──────────────────────────────────────────────────────────
    Route::middleware('jwt.auth')->group(function () {
        Route::controller(LaporansController::class)->prefix('laporans')->group(function () {
            Route::get('statistics', 'statistics');
            Route::get('pelapor/{pelaporId}', 'byPelapor')->whereNumber('pelaporId');
        });

        Route::controller(LaporanWorkflowController::class)->prefix('laporans')->group(function () {
            Route::post('{id}/verifikasi', 'verifikasi');
            Route::post('{id}/proses', 'proses');
            Route::get('{id}/riwayat', 'riwayat');
        });

        Route::apiResource('laporans', LaporansController::class);

        Route::middleware('role:Warga')->prefix('warga')->group(function () {
            Route::get('laporans/{id}/detail-lengkap', [WargaLaporanDetailController::class, 'show'])->whereNumber('id');
        });

        // ─── Kategori Bencana ─────────────────────────────────────────────
        Route::get('/kategori-bencana', [KategoriBencanaController::class, 'index']);
        Route::get('/kategori-bencana/{id}', [KategoriBencanaController::class, 'show']);

        Route::middleware('role:Admin')->group(function () {
            Route::post('/kategori-bencana', [KategoriBencanaController::class, 'store']);
            Route::put('/kategori-bencana/{id}', [KategoriBencanaController::class, 'update']);
            Route::patch('/kategori-bencana/{id}', [KategoriBencanaController::class, 'update']);
            Route::delete('/kategori-bencana/{id}', [KategoriBencanaController::class, 'destroy']);
        });

        // ─── Operasional ──────────────────────────────────────────────────
        Route::apiResource('tindak-lanjut', TindakLanjutController::class);
        Route::apiResource('riwayat-tindakan', RiwayatTindakanController::class);
        Route::apiResource('monitoring', MonitoringController::class);
    });

});

/*
|--------------------------------------------------------------------------
| Legacy alias routes (/api/*) for backward compatibility
|--------------------------------------------------------------------------
*/
Route::group([], function () {

    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth-login');
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth-register');
        Route::get('/roles', [AuthController::class, 'getRoles']);

        Route::middleware('jwt.auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    Route::get('/check-token', function () {
        $user = request()->user();

        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'Token valid',
                'data'    => [
                    'user_id'   => $user->id,
                    'user_role' => $user->role,
                    'user_name' => $user->nama,
                ],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No valid token'], 401);
    })->middleware('jwt.auth')->name('check.token.legacy');

    Route::prefix('wilayah')->group(function () {
        Route::get('/provinsi', [WilayahReferenceController::class, 'getAllProvinsi']);
        Route::get('/provinsi/{id}', [WilayahReferenceController::class, 'getProvinsiById']);
        Route::get('/kabupaten/{provinsi_id}', [WilayahReferenceController::class, 'getKabupatenByProvinsi']);
        Route::get('/kecamatan/{kabupaten_id}', [WilayahReferenceController::class, 'getKecamatanByKabupaten']);
        Route::get('/desa/{kecamatan_id}', [WilayahReferenceController::class, 'getDesaByKecamatan']);

        Route::get('/detail/{desa_id}', [WilayahListingController::class, 'getWilayahDetailByDesaId']);
        Route::get('/hierarchy/{desa_id}', [WilayahListingController::class, 'getWilayahHierarchyByDesaId']);
        Route::get('/search', [WilayahListingController::class, 'search']);

        Route::get('/', [WilayahListingController::class, 'index']);
        Route::get('/{id}', [WilayahListingController::class, 'showById']);
        Route::post('/', [WilayahCrudController::class, 'store'])->middleware(['jwt.auth', 'role:Admin']);
        Route::put('/{id}', [WilayahCrudController::class, 'update'])->middleware(['jwt.auth', 'role:Admin']);
        Route::delete('/{id}', [WilayahCrudController::class, 'destroy'])->middleware(['jwt.auth', 'role:Admin']);
    });

    Route::middleware(['jwt.auth', 'role:Admin'])->prefix('wilayah')->group(function () {
        Route::post('/provinsi', [WilayahCrudController::class, 'storeProvinsi']);
        Route::put('/provinsi/{id}', [WilayahCrudController::class, 'updateProvinsi']);
        Route::delete('/provinsi/{id}', [WilayahCrudController::class, 'destroyProvinsi']);

        Route::post('/kabupaten', [WilayahCrudController::class, 'storeKabupaten']);
        Route::put('/kabupaten/{id}', [WilayahCrudController::class, 'updateKabupaten']);
        Route::delete('/kabupaten/{id}', [WilayahCrudController::class, 'destroyKabupaten']);

        Route::post('/kecamatan', [WilayahCrudController::class, 'storeKecamatan']);
        Route::put('/kecamatan/{id}', [WilayahCrudController::class, 'updateKecamatan']);
        Route::delete('/kecamatan/{id}', [WilayahCrudController::class, 'destroyKecamatan']);

        Route::post('/desa', [WilayahCrudController::class, 'storeDesa']);
        Route::put('/desa/{id}', [WilayahCrudController::class, 'updateDesa']);
        Route::delete('/desa/{id}', [WilayahCrudController::class, 'destroyDesa']);
    });

    Route::prefix('bmkg')->group(function () {
        Route::prefix('gempa')->group(function () {
            Route::get('/terbaru', [BmkgController::class, 'getGempaTerbaru']);
            Route::get('/terkini', [BmkgController::class, 'getDaftarGempa']);
            Route::get('/dirasakan', [BmkgController::class, 'getGempaDirasakan']);
        });

        Route::get('/peringatan-dini-cuaca', [BmkgController::class, 'getPeringatanDiniCuaca']);
        Route::get('/prakiraan-cuaca', [BmkgController::class, 'getPrakiraanCuaca']);

        Route::middleware('jwt.auth')->group(function () {
            Route::get('/', [BmkgController::class, 'index']);
            Route::get('/cache/status', [BmkgController::class, 'getCacheStatus']);
            Route::post('/cache/clear', [BmkgController::class, 'clearCache']);
        });
    });

    Route::middleware('jwt.auth')->group(function () {
        Route::controller(LaporansController::class)->prefix('laporans')->group(function () {
            Route::get('statistics', 'statistics');
            Route::get('pelapor/{pelaporId}', 'byPelapor')->whereNumber('pelaporId');
        });

        Route::controller(LaporanWorkflowController::class)->prefix('laporans')->group(function () {
            Route::post('{id}/verifikasi', 'verifikasi');
            Route::post('{id}/proses', 'proses');
            Route::get('{id}/riwayat', 'riwayat');
        });

        Route::apiResource('laporans', LaporansController::class);

        Route::middleware('role:Warga')->prefix('warga')->group(function () {
            Route::get('laporans/{id}/detail-lengkap', [WargaLaporanDetailController::class, 'show'])->whereNumber('id');
        });

        Route::get('/kategori-bencana', [KategoriBencanaController::class, 'index']);
        Route::get('/kategori-bencana/{id}', [KategoriBencanaController::class, 'show']);

        Route::middleware('role:Admin')->group(function () {
            Route::post('/kategori-bencana', [KategoriBencanaController::class, 'store']);
            Route::put('/kategori-bencana/{id}', [KategoriBencanaController::class, 'update']);
            Route::patch('/kategori-bencana/{id}', [KategoriBencanaController::class, 'update']);
            Route::delete('/kategori-bencana/{id}', [KategoriBencanaController::class, 'destroy']);
        });

        Route::apiResource('tindak-lanjut', TindakLanjutController::class);
        Route::apiResource('riwayat-tindakan', RiwayatTindakanController::class);
        Route::apiResource('monitoring', MonitoringController::class);
    });
});
