<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DesaController;
use App\Http\Controllers\KategoriBencanaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\TindaklanjutController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\BMKGController;
use App\Http\Controllers\OpenStreetMapController;

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

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware(['jwt.auth'])->group(function () {

    // Auth endpoints
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);

    // Routes for all authenticated users
    Route::middleware(['log.activity'])->group(function () {

        // Public desa endpoints (read-only for all authenticated users)
        Route::get('/desa-list/kecamatan', [DesaController::class, 'getKecamatan']);
        Route::get('/desa-list/kabupaten', [DesaController::class, 'getKabupaten']);

        // Dashboard endpoints - all roles
        Route::get('/dashboard', function (Request $request) {
            return response()->json([
                'success' => true,
                'message' => 'Dashboard accessed successfully',
                'data' => [
                    'user' => $request->user(),
                    'role' => $request->user()->role
                ]
            ]);
        });

        // Admin only routes
        Route::middleware(['role:Admin'])->prefix('admin')->group(function () {
            Route::get('/users', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Admin users management'
                ]);
            });

            Route::post('/users', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Admin create user'
                ]);
            });

            Route::put('/users/{id}', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Admin update user'
                ]);
            });

            Route::delete('/users/{id}', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Admin delete user'
                ]);
            });

            // Kategori Bencana management routes
            Route::get('/kategori-bencana', [KategoriBencanaController::class, 'index']);
            Route::post('/kategori-bencana', [KategoriBencanaController::class, 'store']);
            Route::get('/kategori-bencana/{kategori_bencana}', [KategoriBencanaController::class, 'show']);
            Route::put('/kategori-bencana/{kategori_bencana}', [KategoriBencanaController::class, 'update']);
            Route::delete('/kategori-bencana/{kategori_bencana}', [KategoriBencanaController::class, 'destroy']);
            Route::get('/kategori-bencana-statistics', [KategoriBencanaController::class, 'statistics']);

            // Desa management routes
            Route::get('/desa', [DesaController::class, 'index']);
            Route::post('/desa', [DesaController::class, 'store']);
            Route::get('/desa/{desa}', [DesaController::class, 'show']);
            Route::put('/desa/{desa}', [DesaController::class, 'update']);
            Route::delete('/desa/{desa}', [DesaController::class, 'destroy']);
            Route::get('/desa-statistics', [DesaController::class, 'statistics']);

            Route::get('/system-monitoring', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Admin system monitoring'
                ]);
            });
        });

        // Petugas BPBD routes
        Route::middleware(['role:PetugasBPBD'])->prefix('bpbd')->group(function () {
            Route::get('/reports', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'BPBD reports management'
                ]);
            });

            Route::get('/reports/{id}', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'BPBD report details'
                ]);
            });

            Route::post('/reports/{id}/response', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'BPBD create response to report'
                ]);
            });

            Route::put('/responses/{id}', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'BPBD update response status'
                ]);
            });

            Route::get('/statistics', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'BPBD disaster statistics'
                ]);
            });

            Route::post('/notifications', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'BPBD send notifications'
                ]);
            });
        });

        // Operator Desa routes
        Route::middleware(['role:OperatorDesa'])->prefix('operator')->group(function () {
            Route::get('/reports', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Operator village reports'
                ]);
            });

            Route::post('/reports/{id}/verify', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Operator verify report'
                ]);
            });

            Route::post('/reports/{id}/monitor', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Operator create monitoring record'
                ]);
            });

            Route::get('/evacuation-sites', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Operator evacuation sites'
                ]);
            });

            Route::post('/evacuation-sites', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Operator add evacuation site'
                ]);
            });
        });

        // Warga routes
        Route::middleware(['role:Warga'])->prefix('citizen')->group(function () {
            Route::get('/disaster-info', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Citizen disaster information'
                ]);
            });

            Route::get('/evacuation-info', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Citizen evacuation information'
                ]);
            });
        });

        // Multi-role routes (can be accessed by multiple roles)
        Route::middleware(['role:Admin,PetugasBPBD,OperatorDesa'])->group(function () {
            Route::get('/desa', [DesaController::class, 'index']);
            Route::get('/desa/{desa}', [DesaController::class, 'show']);
            Route::get('/desa-list/kecamatan', [DesaController::class, 'getKecamatan']);
            Route::get('/desa-list/kabupaten', [DesaController::class, 'getKabupaten']);
            Route::get('/desa-statistics', [DesaController::class, 'statistics']);
            Route::get('/kategori-bencana', [KategoriBencanaController::class, 'index']);
            Route::get('/kategori-bencana/{kategori_bencana}', [KategoriBencanaController::class, 'show']);

            // Laporan Management Routes
            Route::prefix('laporan')->group(function () {
                Route::get('/', [LaporanController::class, 'index']);
                Route::get('/statistics', [LaporanController::class, 'statistics']);
                Route::get('/{laporan}', [LaporanController::class, 'show']);
                Route::put('/{laporan}', [LaporanController::class, 'update']);
                Route::delete('/{laporan}', [LaporanController::class, 'destroy']);
            });

            // Tindaklanjut Management Routes
            Route::prefix('tindaklanjut')->group(function () {
                Route::get('/', [TindaklanjutController::class, 'index']);
                Route::post('/', [TindaklanjutController::class, 'store']);
                Route::get('/statistics', [TindaklanjutController::class, 'statistics']);
                Route::get('/{tindaklanjut}', [TindaklanjutController::class, 'show']);
                Route::put('/{tindaklanjut}', [TindaklanjutController::class, 'update']);
                Route::delete('/{tindaklanjut}', [TindaklanjutController::class, 'destroy']);
            });

            // Monitoring Management Routes
            Route::prefix('monitoring')->group(function () {
                Route::get('/', [MonitoringController::class, 'index']);
                Route::post('/', [MonitoringController::class, 'store']);
                Route::get('/statistics', [MonitoringController::class, 'statistics']);
                Route::get('/{monitoring}', [MonitoringController::class, 'show']);
                Route::put('/{monitoring}', [MonitoringController::class, 'update']);
                Route::delete('/{monitoring}', [MonitoringController::class, 'destroy']);
                Route::get('/laporan/{laporan_id}', [MonitoringController::class, 'getByLaporan']);
            });

            // BMKG API Routes (All authenticated users can access)
            Route::prefix('bmkg')->group(function () {
                Route::get('/dashboard', [BMKGController::class, 'dashboard']);
                Route::get('/cuaca', [BMKGController::class, 'cuaca']);
                Route::get('/cuaca/peringatan', [BMKGController::class, 'peringatanCuaca']);
                Route::get('/gempa/terbaru', [BMKGController::class, 'gempaTerbaru']);
                Route::get('/gempa/24-jam', [BMKGController::class, 'gempa24Jam']);
                Route::get('/gempa/riwayat', [BMKGController::class, 'riwayatGempa']);
                Route::get('/gempa/statistik', [BMKGController::class, 'statistikGempa']);
                Route::get('/gempa/cek-koordinat', [BMKGController::class, 'cekGempaKoordinat']);
                Route::get('/gempa/peringatan-tsunami', [BMKGController::class, 'peringatanTsunami']);
            });

            // BMKG Admin Routes
            Route::prefix('bmkg/admin')->middleware(['role:Admin'])->group(function () {
                Route::delete('/cache', [BMKGController::class, 'clearCache']);
                Route::get('/status', [BMKGController::class, 'status']);
            });

            // OpenStreetMap Routes (All authenticated users can access)
            Route::prefix('osm')->group(function () {
                Route::get('/status', [OpenStreetMapController::class, 'status']);
                Route::post('/geocode', [OpenStreetMapController::class, 'geocode']);
                Route::post('/reverse-geocode', [OpenStreetMapController::class, 'reverseGeocode']);
                Route::get('/disaster-locations', [OpenStreetMapController::class, 'searchDisasterLocations']);
                Route::get('/nearby-hospitals', [OpenStreetMapController::class, 'getNearbyHospitals']);
                Route::get('/evacuation-centers', [OpenStreetMapController::class, 'getEvacuationCenters']);
                Route::get('/disaster-map', [OpenStreetMapController::class, 'getDisasterMap']);
            });

            // OpenStreetMap Admin Routes
            Route::prefix('osm/admin')->middleware(['role:Admin'])->group(function () {
                Route::delete('/cache', [OpenStreetMapController::class, 'clearCache']);
            });
        });

        // Warga + Operator Desa routes
        Route::middleware(['role:Warga,OperatorDesa'])->group(function () {
            // Create new laporan
            Route::post('/laporan', [LaporanController::class, 'store']);

            // Get my reports (user's own reports)
            Route::get('/my-reports', [LaporanController::class, 'index']);

            // View specific laporan details (with authorization)
            Route::get('/laporan/{laporan}', [LaporanController::class, 'show']);
        });
    });
});

// Test route for checking API
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'SIMONTA BENCANA API is running',
        'version' => '1.0.0',
        'timestamp' => now()
    ]);
});