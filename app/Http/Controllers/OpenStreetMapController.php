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
     * @OA\Post(
     *      path="/api/osm/geocode",
     *      tags={"OpenStreetMap Integration"},
     *      summary="Geocoding - Address to Coordinates",
     *      description="Endpoint untuk mengkonversi alamat menjadi koordinat (latitude/longitude) menggunakan OpenStreetMap.",
     *      operationId="osmGeocode",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"alamat"},
     *              @OA\Property(property="alamat", type="string", example="Jl. Sudirman No. 1, Jakarta", description="Alamat yang akan dikonversi"),
     *              @OA\Property(property="negara", type="string", example="Indonesia", description="Negara (optional, default: Indonesia)")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Geocoding berhasil dilakukan",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Geocoding berhasil dilakukan"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="latitude", type="number", format="double", example=-6.200000),
     *                  @OA\Property(property="longitude", type="number", format="double", example=106.816666),
     *                  @OA\Property(property="formatted_address", type="string", example="Jl. Jenderal Sudirman, RT.1/RW.3, Gelora, Kecamatan Tanah Abang, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta, Indonesia")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validasi gagal",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */

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
     * @OA\Post(
     *      path="/api/osm/reverse-geocode",
     *      tags={"OpenStreetMap Integration"},
     *      summary="Reverse Geocoding - Coordinates to Address",
     *      description="Endpoint untuk mengkonversi koordinat (latitude/longitude) menjadi alamat menggunakan OpenStreetMap.",
     *      operationId="osmReverseGeocode",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"latitude","longitude"},
     *              @OA\Property(property="latitude", type="number", format="double", example=-6.200000, description="Latitude lokasi"),
     *              @OA\Property(property="longitude", type="number", format="double", example=106.816666, description="Longitude lokasi")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Reverse geocoding berhasil dilakukan",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Reverse geocoding berhasil dilakukan"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="address", type="string", example="Jl. Jenderal Sudirman, RT.1/RW.3, Gelora, Kecamatan Tanah Abang, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta, 10270, Indonesia"),
     *                  @OA\Property(property="formatted_address", type="string", example="Jl. Jenderal Sudirman No. 1, Jakarta Pusat"),
     *                  @OA\Property(property="place_name", type="string", example="Jakarta"),
     *                  @OA\Property(property="country", type="string", example="Indonesia")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validasi gagal",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */

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
     * @OA\Get(
     *      path="/api/osm/disaster-locations",
     *      tags={"OpenStreetMap Integration"},
     *      summary="Search Disaster-Related Locations",
     *      description="Endpoint untuk mencari lokasi-lokasi terkait bencana menggunakan OpenStreetMap.",
     *      operationId="osmSearchDisasterLocations",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="latitude",
     *          in="query",
     *          description="Latitude pusat pencarian",
     *          required=false,
     *          @OA\Schema(type="number", format="double", example=-6.200000)
     *      ),
     *      @OA\Parameter(
     *          name="longitude",
     *          in="query",
     *          description="Longitude pusat pencarian",
     *          required=false,
     *          @OA\Schema(type="number", format="double", example=106.816666)
     *      ),
     *      @OA\Parameter(
     *          name="radius",
     *          in="query",
     *          description="Radius pencarian dalam meter (100-50000)",
     *          required=false,
     *          @OA\Schema(type="integer", example=5000)
     *      ),
     *      @OA\Parameter(
     *          name="bounds",
     *          in="query",
     *          description="Batas area pencarian [min_lat, min_lon, max_lat, max_lon]",
     *          required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(type="number", format="double")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Pencarian lokasi bencana berhasil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Pencarian lokasi bencana berhasil dilakukan"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id", type="string", example="node/123456789"),
     *                      @OA\Property(property="nama", type="string", example="Lokasi Bencana Contoh"),
     *                      @OA\Property(property="latitude", type="number", format="double", example=-6.200000),
     *                      @OA\Property(property="longitude", type="number", format="double", example=106.816666),
     *                      @OA\Property(property="jenis_lokasi", type="string", example="evacuation_center"),
     *                      @OA\Property(property="jarak_meter", type="integer", example=1200)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validasi gagal",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */

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
     * @OA\Get(
     *      path="/api/osm/nearby-hospitals",
     *      tags={"OpenStreetMap Integration"},
     *      summary="Get Nearby Hospitals and Health Facilities",
     *      description="Endpoint untuk mencari fasilitas kesehatan terdekat menggunakan OpenStreetMap.",
     *      operationId="osmGetNearbyHospitals",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="latitude",
     *          in="query",
     *          description="Latitude titik awal pencarian",
     *          required=true,
     *          @OA\Schema(type="number", format="double", example=-6.200000)
     *      ),
     *      @OA\Parameter(
     *          name="longitude",
     *          in="query",
     *          description="Longitude titik awal pencarian",
     *          required=true,
     *          @OA\Schema(type="number", format="double", example=106.816666)
     *      ),
     *      @OA\Parameter(
     *          name="radius",
     *          in="query",
     *          description="Radius pencarian dalam meter (100-50000)",
     *          required=false,
     *          @OA\Schema(type="integer", example=5000)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Pencarian fasilitas kesehatan berhasil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Pencarian fasilitas kesehatan berhasil dilakukan"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id", type="string", example="way/123456789"),
     *                      @OA\Property(property="nama_fasilitas", type="string", example="RSUD Contoh"),
     *                      @OA\Property(property="latitude", type="number", format="double", example=-6.200000),
     *                      @OA\Property(property="longitude", type="number", format="double", example=106.816666),
     *                      @OA\Property(property="jenis_fasilitas", type="string", example="hospital"),
     *                      @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 123, Jakarta"),
     *                      @OA\Property(property="jarak_meter", type="integer", example=800)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validasi gagal",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */

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
    /**
     * @OA\Get(
     *      path="/api/osm/evacuation-centers",
     *      tags={"OpenStreetMap Integration"},
     *      summary="Get Evacuation Centers and Shelters",
     *      description="Endpoint untuk mendapatkan lokasi pusat evakuasi dan shelter terdekat menggunakan OpenStreetMap.",
     *      operationId="osmGetEvacuationCenters",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="latitude",
     *          in="query",
     *          description="Latitude titik awal pencarian",
     *          required=false,
     *          @OA\Schema(type="number", format="double", example=-6.200000)
     *      ),
     *      @OA\Parameter(
     *          name="longitude",
     *          in="query",
     *          description="Longitude titik awal pencarian",
     *          required=false,
     *          @OA\Schema(type="number", format="double", example=106.816666)
     *      ),
     *      @OA\Parameter(
     *          name="bounds",
     *          in="query",
     *          description="Batas area pencarian [min_lat, min_lon, max_lat, max_lon]",
     *          required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(type="number", format="double")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Pencarian pusat evakuasi berhasil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Pencarian pusat evakuasi berhasil dilakukan"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id", type="string", example="way/987654321"),
     *                      @OA\Property(property="nama_tempat", type="string", example="Posko Evakuasi Contoh"),
     *                      @OA\Property(property="latitude", type="number", format="double", example=-6.200000),
     *                      @OA\Property(property="longitude", type="number", format="double", example=106.816666),
     *                      @OA\Property(property="jenis_lokasi", type="string", example="emergency_shelter"),
     *                      @OA\Property(property="alamat", type="string", example="Jl. Evakuasi No. 456, Jakarta"),
     *                      @OA\Property(property="kapasitas", type="integer", example=200),
     *                      @OA\Property(property="jarak_meter", type="integer", example=1500)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validasi gagal",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
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
    /**
     * @OA\Get(
     *      path="/api/osm/disaster-map",
     *      tags={"OpenStreetMap Integration"},
     *      summary="Get Disaster Map Data",
     *      description="Endpoint untuk mendapatkan data peta untuk visualisasi bencana menggunakan OpenStreetMap.",
     *      operationId="osmGetDisasterMap",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="latitude",
     *          in="query",
     *          description="Latitude pusat peta",
     *          required=true,
     *          @OA\Schema(type="number", format="double", example=-6.200000)
     *      ),
     *      @OA\Parameter(
     *          name="longitude",
     *          in="query",
     *          description="Longitude pusat peta",
     *          required=true,
     *          @OA\Schema(type="number", format="double", example=106.816666)
     *      ),
     *      @OA\Parameter(
     *          name="radius",
     *          in="query",
     *          description="Radius area peta dalam meter (1000-50000)",
     *          required=false,
     *          @OA\Schema(type="integer", example=10000)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Data peta bencana berhasil diambil",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Data peta bencana berhasil diambil"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="pusat", type="object",
     *                      @OA\Property(property="latitude", type="number", format="double", example=-6.200000),
     *                      @OA\Property(property="longitude", type="number", format="double", example=106.816666),
     *                      @OA\Property(property="radius", type="integer", example=10000)
     *                  ),
     *                  @OA\Property(property="lokasi_bencana", type="array",
     *                      @OA\Items(type="object",
     *                          @OA\Property(property="id", type="string", example="node/123456789"),
     *                          @OA\Property(property="nama", type="string", example="Lokasi Bencana"),
     *                          @OA\Property(property="latitude", type="number", format="double", example=-6.201000),
     *                          @OA\Property(property="longitude", type="number", format="double", example=106.817666)
     *                      )
     *                  ),
     *                  @OA\Property(property="fasilitas_kesehatan", type="array",
     *                      @OA\Items(type="object",
     *                          @OA\Property(property="id", type="string", example="way/987654321"),
     *                          @OA\Property(property="nama", type="string", example="RSUD Terdekat"),
     *                          @OA\Property(property="latitude", type="number", format="double", example=-6.199000),
     *                          @OA\Property(property="longitude", type="number", format="double", example=106.815666)
     *                      )
     *                  ),
     *                  @OA\Property(property="pusat_evakuasi", type="array",
     *                      @OA\Items(type="object",
     *                          @OA\Property(property="id", type="string", example="way/456789123"),
     *                          @OA\Property(property="nama", type="string", example="Posko Evakuasi"),
     *                          @OA\Property(property="latitude", type="number", format="double", example=-6.202000),
     *                          @OA\Property(property="longitude", type="number", format="double", example=106.818666)
     *                      )
     *                  ),
     *                  @OA\Property(property="waktu_update", type="string", format="date-time", example="2024-12-10T10:30:00.000000Z")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validasi gagal",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validasi gagal"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
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
     * @OA\Delete(
     *      path="/api/osm/admin/cache",
     *      tags={"OpenStreetMap Admin"},
     *      summary="Clear OpenStreetMap Cache",
     *      description="Endpoint untuk membersihkan cache data OpenStreetMap (Admin Only).",
     *      operationId="clearOSMCache",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Cache OpenStreetMap berhasil dibersihkan",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Cache OpenStreetMap berhasil dibersihkan"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="cleared", type="boolean", example=true),
     *                  @OA\Property(property="cleaned_by", type="string", example="Admin Test"),
     *                  @OA\Property(property="waktu_clean", type="string", format="date-time", example="2024-12-10T10:30:00.000000Z")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden - Admin access required"
     *      )
     * )
     */

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