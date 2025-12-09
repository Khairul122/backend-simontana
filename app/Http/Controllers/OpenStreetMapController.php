<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpenStreetMapService;

class OpenStreetMapController extends Controller
{
    protected $osmService;

    public function __construct(OpenStreetMapService $osmService)
    {
        $this->osmService = $osmService;
    }

    /**
     * Geocoding: Convert address to coordinates
     */
    public function geocode(Request $request)
    {
        try {
            $validated = $request->validate([
                'alamat' => 'required|string|max:500',
                'negara' => 'nullable|string|max:100'
            ], [
                'alamat.required' => 'Alamat wajib diisi',
                'alamat.max' => 'Alamat maksimal 500 karakter',
                'negara.max' => 'Nama negara maksimal 100 karakter'
            ]);

            $negara = $validated['negara'] ?? 'Indonesia';
            $result = $this->osmService->geocode($validated['alamat'], $negara);

            return response()->json([
                'success' => true,
                'message' => 'Geocoding berhasil dilakukan',
                'data' => $result
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan geocoding: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reverse Geocoding: Convert coordinates to address
     */
    public function reverseGeocode(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180'
            ], [
                'latitude.required' => 'Latitude wajib diisi',
                'longitude.required' => 'Longitude wajib diisi',
                'latitude.numeric' => 'Latitude harus berupa angka',
                'longitude.numeric' => 'Longitude harus berupa angka',
                'latitude.between' => 'Latitude harus antara -90 dan 90',
                'longitude.between' => 'Longitude harus antara -180 dan 180'
            ]);

            $result = $this->osmService->reverseGeocode(
                $validated['latitude'],
                $validated['longitude']
            );

            return response()->json([
                'success' => true,
                'message' => 'Reverse geocoding berhasil dilakukan',
                'data' => $result
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan reverse geocoding: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search disaster-related locations
     */
    public function searchDisasterLocations(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'radius' => 'nullable|integer|min:100|max:50000',
                'bounds' => 'nullable|array|min:4|max:4'
            ], [
                'latitude.numeric' => 'Latitude harus berupa angka',
                'longitude.numeric' => 'Longitude harus berupa angka',
                'latitude.between' => 'Latitude harus antara -90 dan 90',
                'longitude.between' => 'Longitude harus antara -180 dan 180',
                'radius.min' => 'Radius minimal 100 meter',
                'radius.max' => 'Radius maksimal 50000 meter (50km)',
                'bounds.array' => 'Bounds harus berupa array dengan 4 elemen'
            ]);

            $lat = $validated['latitude'] ?? null;
            $lon = $validated['longitude'] ?? null;
            $radius = $validated['radius'] ?? null;
            $bounds = $validated['bounds'] ?? null;

            $result = $this->osmService->searchDisasterLocations(
                $bounds,
                $radius,
                $lat,
                $lon
            );

            return response()->json([
                'success' => true,
                'message' => 'Pencarian lokasi bencana berhasil dilakukan',
                'data' => $result
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari lokasi bencana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get nearby hospitals and health facilities
     */
    public function getNearbyHospitals(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|integer|min:100|max:50000'
            ], [
                'latitude.required' => 'Latitude wajib diisi',
                'longitude.required' => 'Longitude wajib diisi',
                'latitude.numeric' => 'Latitude harus berupa angka',
                'longitude.numeric' => 'Longitude harus berupa angka',
                'latitude.between' => 'Latitude harus antara -90 dan 90',
                'longitude.between' => 'Longitude harus antara -180 dan 180',
                'radius.min' => 'Radius minimal 100 meter',
                'radius.max' => 'Radius maksimal 50000 meter (50km)'
            ]);

            $radius = $validated['radius'] ?? 5000; // default 5km
            $result = $this->osmService->getNearbyHospitals(
                $validated['latitude'],
                $validated['longitude'],
                $radius
            );

            return response()->json([
                'success' => true,
                'message' => 'Pencarian fasilitas kesehatan berhasil dilakukan',
                'data' => $result
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari fasilitas kesehatan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get evacuation centers and shelters
     */
    public function getEvacuationCenters(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'bounds' => 'nullable|array|min:4|max:4'
            ], [
                'latitude.numeric' => 'Latitude harus berupa angka',
                'longitude.numeric' => 'Longitude harus berupa angka',
                'latitude.between' => 'Latitude harus antara -90 dan 90',
                'longitude.between' => 'Longitude harus antara -180 dan 180',
                'bounds.array' => 'Bounds harus berupa array dengan 4 elemen'
            ]);

            $lat = $validated['latitude'] ?? null;
            $lon = $validated['longitude'] ?? null;
            $bounds = $validated['bounds'] ?? null;

            $result = $this->osmService->getEvacuationCenters($lat, $lon, $bounds);

            return response()->json([
                'success' => true,
                'message' => 'Pencarian pusat evakuasi berhasil dilakukan',
                'data' => $result
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari pusat evakuasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get map data for disaster visualization
     */
    public function getDisasterMap(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|integer|min:1000|max:50000'
            ], [
                'latitude.required' => 'Latitude wajib diisi',
                'longitude.required' => 'Longitude wajib diisi',
                'latitude.numeric' => 'Latitude harus berupa angka',
                'longitude.numeric' => 'Longitude harus berupa angka',
                'latitude.between' => 'Latitude harus antara -90 dan 90',
                'longitude.between' => 'Longitude harus antara -180 dan 180',
                'radius.min' => 'Radius minimal 1000 meter (1km)',
                'radius.max' => 'Radius maksimal 50000 meter (50km)'
            ]);

            $radius = $validated['radius'] ?? 10000; // default 10km

            // Get all relevant data for disaster map
            $disasterLocations = $this->osmService->searchDisasterLocations(
                null,
                $radius,
                $validated['latitude'],
                $validated['longitude']
            );

            $hospitals = $this->osmService->getNearbyHospitals(
                $validated['latitude'],
                $validated['longitude'],
                $radius
            );

            $evacuationCenters = $this->osmService->getEvacuationCenters(
                $validated['latitude'],
                $validated['longitude']
            );

            $mapData = [
                'pusat' => [
                    'latitude' => (float) $validated['latitude'],
                    'longitude' => (float) $validated['longitude'],
                    'radius' => $radius
                ],
                'lokasi_bencana' => $disasterLocations,
                'fasilitas_kesehatan' => $hospitals,
                'pusat_evakuasi' => $evacuationCenters,
                'waktu_update' => now()->toISOString()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data peta bencana berhasil diambil',
                'data' => $mapData
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data peta bencana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear OpenStreetMap cache (Admin only)
     */
    public function clearCache(Request $request)
    {
        try {
            $user = $request->user();

            // Only admin can clear cache
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya admin yang dapat membersihkan cache.'
                ], 403);
            }

            $cleared = $this->osmService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Cache OpenStreetMap berhasil dibersihkan',
                'data' => [
                    'cleared' => $cleared,
                    'cleaned_by' => $user->nama,
                    'waktu_clean' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan cache OpenStreetMap: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get OpenStreetMap service status
     */
    public function status(Request $request)
    {
        try {
            $nominatimUrl = env('OSM_NOMINATIM_URL', 'https://nominatim.openstreetmap.org/');
            $overpassUrl = env('OSM_OVERPASS_URL', 'https://overpass-api.de/api/interpreter');

            // Test Nominatim API
            $nominatimTest = \Http::timeout(10)
                ->get($nominatimUrl . 'search', [
                    'q' => 'Jakarta, Indonesia',
                    'format' => 'json',
                    'limit' => 1
                ]);

            // Test Overpass API
            $overpassTest = \Http::timeout(15)
                ->post($overpassUrl, [
                    'data' => '[out:json]; node["name"="Jakarta"]; out count;'
                ]);

            $status = [
                'nominatim_url' => $nominatimUrl,
                'nominatim_status' => $nominatimTest->successful() ? 'online' : 'offline',
                'nominatim_response_time' => $nominatimTest->successful() ?
                    ($nominatimTest->handlerStats()['total_time'] ?? 'unknown') : 'timeout',

                'overpass_url' => $overpassUrl,
                'overpass_status' => $overpassTest->successful() ? 'online' : 'offline',
                'overpass_response_time' => $overpassTest->successful() ?
                    ($overpassTest->handlerStats()['total_time'] ?? 'unknown') : 'timeout',

                'last_check' => now()->toISOString(),
                'cache_status' => 'active'
            ];

            return response()->json([
                'success' => true,
                'message' => 'Status OpenStreetMap API berhasil diambil',
                'data' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa status OpenStreetMap API: ' . $e->getMessage()
            ], 500);
        }
    }
}