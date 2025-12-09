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
use App\Http\Controllers\SystemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BPBDController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\CitizenController;

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
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Admin only routes
        Route::middleware(['role:Admin'])->prefix('admin')->group(function () {
            // User Management routes
            Route::get('/pengguna', [AdminController::class, 'getPengguna']);
            Route::post('/pengguna', [AdminController::class, 'createPengguna']);
            Route::put('/pengguna/{id}', [AdminController::class, 'updatePengguna']);
            Route::delete('/pengguna/{id}', [AdminController::class, 'deletePengguna']);

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

            // System monitoring
            Route::get('/system-monitoring', [AdminController::class, 'systemMonitoring']);
        });

        // Petugas BPBD routes
        Route::middleware(['role:PetugasBPBD'])->prefix('bpbd')->group(function () {
            Route::get('/reports', [BPBDController::class, 'getReports']);
            Route::get('/reports/{id}', [BPBDController::class, 'getReportDetails']);
            Route::post('/reports/{id}/response', [BPBDController::class, 'createResponse']);
            Route::put('/responses/{id}', [BPBDController::class, 'updateResponse']);
            Route::get('/statistics', [BPBDController::class, 'getStatistics']);
            Route::post('/notifications', [BPBDController::class, 'sendNotifications']);
        });

        // Operator Desa routes
        Route::middleware(['role:OperatorDesa'])->prefix('operator')->group(function () {
            Route::get('/reports', [OperatorController::class, 'getReports']);
            Route::post('/reports/{id}/verify', [OperatorController::class, 'verifyReport']);
            Route::post('/reports/{id}/monitor', [OperatorController::class, 'createMonitoring']);
            Route::get('/evacuation-sites', [OperatorController::class, 'getEvacuationSites']);
            Route::post('/evacuation-sites', [OperatorController::class, 'addEvacuationSite']);
        });

        // Warga routes
        Route::middleware(['role:Warga'])->prefix('citizen')->group(function () {
            Route::get('/disaster-info', [CitizenController::class, 'getDisasterInfo']);
            Route::get('/evacuation-info', [CitizenController::class, 'getEvacuationInfo']);
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
Route::get('/test', [SystemController::class, 'test']);