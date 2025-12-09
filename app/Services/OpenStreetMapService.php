<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OpenStreetMapService
{
    protected $nominatimUrl;
    protected $overpassUrl;
    protected $cacheTime;

    public function __construct()
    {
        $this->nominatimUrl = env('OSM_NOMINATIM_URL', 'https://nominatim.openstreetmap.org/');
        $this->overpassUrl = env('OSM_OVERPASS_URL', 'https://overpass-api.de/api/interpreter');
        $this->cacheTime = env('OSM_CACHE_TIME', 3600); // 1 hour default
    }

    /**
     * Geocoding: Convert address to coordinates
     */
    public function geocode($address, $country = 'Indonesia')
    {
        $cacheKey = 'osm_geocode_' . md5($address . $country);

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($address, $country) {
            try {
                $response = Http::timeout(30)
                    ->get($this->nominatimUrl . 'search', [
                        'q' => $address . ', ' . $country,
                        'format' => 'json',
                        'limit' => 1,
                        'countrycodes' => 'id',
                        'addressdetails' => 1,
                        'extratags' => 1
                    ]);

                if ($response->successful() && !empty($response->json())) {
                    $data = $response->json()[0];
                    return $this->formatGeocodeResult($data);
                }

                return $this->getGeocodeFallback($address);

            } catch (\Exception $e) {
                Log::error('Error geocoding address: ' . $e->getMessage());
                return $this->getGeocodeFallback($address);
            }
        });
    }

    /**
     * Reverse Geocoding: Convert coordinates to address
     */
    public function reverseGeocode($lat, $lon)
    {
        $cacheKey = 'osm_reverse_' . md5($lat . $lon);

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($lat, $lon) {
            try {
                $response = Http::timeout(30)
                    ->get($this->nominatimUrl . 'reverse', [
                        'format' => 'json',
                        'lat' => $lat,
                        'lon' => $lon,
                        'zoom' => 18,
                        'addressdetails' => 1,
                        'extratags' => 1
                    ]);

                if ($response->successful()) {
                    return $this->formatReverseGeocodeResult($response->json());
                }

                return $this->getReverseGeocodeFallback($lat, $lon);

            } catch (\Exception $e) {
                Log::error('Error reverse geocoding coordinates: ' . $e->getMessage());
                return $this->getReverseGeocodeFallback($lat, $lon);
            }
        });
    }

    /**
     * Search for disaster-related locations using Overpass API
     */
    public function searchDisasterLocations($bounds = null, $radius = null, $lat = null, $lon = null)
    {
        $cacheKey = 'osm_disaster_locations_' . md5(serialize(func_get_args()));

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($bounds, $radius, $lat, $lon) {
            try {
                $query = $this->buildDisasterQuery($bounds, $radius, $lat, $lon);

                $response = Http::timeout(60)
                    ->post($this->overpassUrl, [
                        'data' => $query
                    ]);

                if ($response->successful()) {
                    return $this->formatDisasterLocations($response->json());
                }

                return $this->getDisasterFallback();

            } catch (\Exception $e) {
                Log::error('Error searching disaster locations: ' . $e->getMessage());
                return $this->getDisasterFallback();
            }
        });
    }

    /**
     * Get nearby hospitals and health facilities
     */
    public function getNearbyHospitals($lat, $lon, $radius = 5000)
    {
        $cacheKey = 'osm_hospitals_' . md5($lat . $lon . $radius);

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($lat, $lon, $radius) {
            try {
                $query = $this->buildHospitalQuery($lat, $lon, $radius);

                $response = Http::timeout(60)
                    ->post($this->overpassUrl, [
                        'data' => $query
                    ]);

                if ($response->successful()) {
                    return $this->formatHospitalResults($response->json());
                }

                return $this->getHospitalFallback();

            } catch (\Exception $e) {
                Log::error('Error searching nearby hospitals: ' . $e->getMessage());
                return $this->getHospitalFallback();
            }
        });
    }

    /**
     * Get evacuation centers and shelters
     */
    public function getEvacuationCenters($lat = null, $lon = null, $bounds = null)
    {
        $cacheKey = 'osm_evacuation_' . md5(serialize(func_get_args()));

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($lat, $lon, $bounds) {
            try {
                $query = $this->buildEvacuationQuery($lat, $lon, $bounds);

                $response = Http::timeout(60)
                    ->post($this->overpassUrl, [
                        'data' => $query
                    ]);

                if ($response->successful()) {
                    return $this->formatEvacuationResults($response->json());
                }

                return $this->getEvacuationFallback();

            } catch (\Exception $e) {
                Log::error('Error searching evacuation centers: ' . $e->getMessage());
                return $this->getEvacuationFallback();
            }
        });
    }

    /**
     * Build Overpass query for disaster-related locations
     */
    private function buildDisasterQuery($bounds, $radius, $lat, $lon)
    {
        if ($bounds) {
            $bbox = $bounds;
        } elseif ($lat && $lon && $radius) {
            $bbox = $this->calculateBoundingBox($lat, $lon, $radius);
        } else {
            // Default to Indonesia bounds
            $bbox = [-11, 95, 6, 141];
        }

        $query = "[out:json][timeout:25];
        (
          node[\"emergency\"~\"ambulance_station|disaster_response|rescue\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
          way[\"emergency\"~\"ambulance_station|disaster_response|rescue\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
          relation[\"emergency\"~\"ambulance_station|disaster_response|rescue\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});

          node[\"amenity\"~\"hospital|clinic|doctors|pharmacy\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
          way[\"amenity\"~\"hospital|clinic|doctors|pharmacy\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});

          node[\"building\"~\"hospital|clinic|public\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
          way[\"building\"~\"hospital|clinic|public\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
        );
        out geom;";

        return $query;
    }

    /**
     * Build Overpass query for hospitals
     */
    private function buildHospitalQuery($lat, $lon, $radius)
    {
        $bbox = $this->calculateBoundingBox($lat, $lon, $radius);

        $query = "[out:json][timeout:25];
        (
          node[\"amenity\"=\"hospital\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
          way[\"amenity\"=\"hospital\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
          relation[\"amenity\"=\"hospital\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});

          node[\"building\"=\"hospital\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
          way[\"building\"=\"hospital\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});

          node[\"emergency\"=\"ambulance_station\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
          way[\"emergency\"=\"ambulance_station\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
        );
        out center;";

        return $query;
    }

    /**
     * Build Overpass query for evacuation centers
     */
    private function buildEvacuationQuery($lat, $lon, $bounds)
    {
        if ($bounds) {
            $bbox = $bounds;
        } elseif ($lat && $lon) {
            $bbox = $this->calculateBoundingBox($lat, $lon, 10000); // 10km radius
        } else {
            // Default to Indonesia bounds
            $bbox = [-11, 95, 6, 141];
        }

        $query = "[out:json][timeout:25];
        (
          node[\"amenity\"~\"shelter|social_facility\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
          way[\"amenity\"~\"shelter|social_facility\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
          relation[\"amenity\"~\"shelter|social_facility\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});

          node[\"building\"~\"school|public|government\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
          way[\"building\"~\"school|public|government\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});

          node[\"landuse\"~\"recreation_ground|grass|village_green\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
          way[\"landuse\"~\"recreation_ground|grass|village_green\"]({$bbox[0]},{$bbox[1]},{$bbox[2]},{$bbox[3]});
        );
        out center;";

        return $query;
    }

    /**
     * Calculate bounding box from center point and radius
     */
    private function calculateBoundingBox($lat, $lon, $radius)
    {
        $earthRadius = 6371000; // Earth's radius in meters
        $deltaLat = $radius / $earthRadius * (180 / pi());
        $deltaLon = $radius / ($earthRadius * cos(pi() * $lat / 180)) * (180 / pi());

        return [
            $lat - $deltaLat,
            $lon - $deltaLon,
            $lat + $deltaLat,
            $lon + $deltaLon
        ];
    }

    /**
     * Format geocoding result
     */
    private function formatGeocodeResult($data)
    {
        return [
            'status' => 'success',
            'alamat' => $data['display_name'] ?? 'Tidak diketahui',
            'koordinat' => [
                'latitude' => (float) ($data['lat'] ?? 0),
                'longitude' => (float) ($data['lon'] ?? 0)
            ],
            'detail' => [
                'type' => $data['type'] ?? 'unknown',
                'importance' => (float) ($data['importance'] ?? 0),
                'class' => $data['class'] ?? 'unknown',
                'type_class' => $data['type'] ?? 'unknown'
            ],
            'address_components' => $data['address'] ?? []
        ];
    }

    /**
     * Format reverse geocoding result
     */
    private function formatReverseGeocodeResult($data)
    {
        return [
            'status' => 'success',
            'alamat' => $data['display_name'] ?? 'Lokasi tidak diketahui',
            'koordinat' => [
                'latitude' => (float) ($data['lat'] ?? 0),
                'longitude' => (float) ($data['lon'] ?? 0)
            ],
            'address' => $data['address'] ?? [],
            'place_id' => $data['place_id'] ?? 0,
            'osm_type' => $data['osm_type'] ?? 'unknown'
        ];
    }

    /**
     * Format disaster locations result
     */
    private function formatDisasterLocations($data)
    {
        $locations = [];

        foreach ($data['elements'] ?? [] as $element) {
            $location = [
                'id' => $element['id'],
                'type' => $element['type'],
                'name' => $element['tags']['name'] ?? 'Lokasi tidak bernama',
                'kategori' => $this->getDisasterCategory($element['tags']),
                'tags' => $element['tags'],
                'koordinat' => $this->getCoordinates($element),
                'alamat' => $this->getAddress($element['tags'] ?? [])
            ];

            $locations[] = $location;
        }

        return [
            'status' => 'success',
            'total' => count($locations),
            'data' => $locations
        ];
    }

    /**
     * Format hospital results
     */
    private function formatHospitalResults($data)
    {
        $hospitals = [];

        foreach ($data['elements'] ?? [] as $element) {
            $hospital = [
                'id' => $element['id'],
                'type' => $element['type'],
                'name' => $element['tags']['name'] ?? 'Rumah Sakit tidak bernama',
                'tipe' => $this->getHospitalType($element['tags']),
                'tags' => $element['tags'],
                'koordinat' => $this->getCoordinates($element),
                'alamat' => $this->getAddress($element['tags'] ?? []),
                'kontak' => [
                    'telepon' => $element['tags']['phone'] ?? null,
                    'website' => $element['tags']['website'] ?? null
                ]
            ];

            $hospitals[] = $hospital;
        }

        return [
            'status' => 'success',
            'total' => count($hospitals),
            'data' => $hospitals
        ];
    }

    /**
     * Format evacuation centers result
     */
    private function formatEvacuationResults($data)
    {
        $centers = [];

        foreach ($data['elements'] ?? [] as $element) {
            $center = [
                'id' => $element['id'],
                'type' => $element['type'],
                'name' => $element['tags']['name'] ?? 'Pusat Evakuasi tidak bernama',
                'tipe' => $this->getEvacuationType($element['tags']),
                'tags' => $element['tags'],
                'koordinat' => $this->getCoordinates($element),
                'alamat' => $this->getAddress($element['tags'] ?? []),
                'kapasitas' => $element['tags']['capacity'] ?? null
            ];

            $centers[] = $center;
        }

        return [
            'status' => 'success',
            'total' => count($centers),
            'data' => $centers
        ];
    }

    /**
     * Get coordinates from element
     */
    private function getCoordinates($element)
    {
        if ($element['type'] === 'node') {
            return [
                'latitude' => (float) $element['lat'],
                'longitude' => (float) $element['lon']
            ];
        } elseif (isset($element['center'])) {
            return [
                'latitude' => (float) $element['center']['lat'],
                'longitude' => (float) $element['center']['lon']
            ];
        }

        return [
            'latitude' => 0,
            'longitude' => 0
        ];
    }

    /**
     * Get address from tags
     */
    private function getAddress($tags)
    {
        $address = [];

        if (isset($tags['addr:housenumber'])) $address['nomor'] = $tags['addr:housenumber'];
        if (isset($tags['addr:street'])) $address['jalan'] = $tags['addr:street'];
        if (isset($tags['addr:city'])) $address['kota'] = $tags['addr:city'];
        if (isset($tags['addr:postcode'])) $address['kode_pos'] = $tags['addr:postcode'];

        return $address;
    }

    /**
     * Get disaster category from tags
     */
    private function getDisasterCategory($tags)
    {
        if (isset($tags['amenity'])) {
            if ($tags['amenity'] === 'hospital') return 'Rumah Sakit';
            if ($tags['amenity'] === 'clinic') return 'Klinik';
            if ($tags['amenity'] === 'doctors') return 'Dokter/Praktek';
            if ($tags['amenity'] === 'pharmacy') return 'Apotik';
        }

        if (isset($tags['emergency'])) {
            if ($tags['emergency'] === 'ambulance_station') return 'Pos Ambulans';
            if ($tags['emergency'] === 'disaster_response') return 'Pos Tanggap Darurat';
            if ($tags['emergency'] === 'rescue') return 'Pos Penyelamatan';
        }

        if (isset($tags['building'])) {
            if ($tags['building'] === 'hospital') return 'Rumah Sakit';
            if ($tags['building'] === 'clinic') return 'Klinik';
            if ($tags['building'] === 'public') return 'Gedung Publik';
        }

        return 'Lainnya';
    }

    /**
     * Get hospital type from tags
     */
    private function getHospitalType($tags)
    {
        if (isset($tags['emergency']) && $tags['emergency'] === 'ambulance_station') {
            return 'Pos Ambulans';
        }

        if (isset($tags['amenity'])) {
            return match($tags['amenity']) {
                'hospital' => 'Rumah Sakit',
                'clinic' => 'Klinik',
                'doctors' => 'Praktek Dokter',
                'pharmacy' => 'Apotik',
                default => 'Fasilitas Kesehatan'
            };
        }

        return 'Fasilitas Kesehatan';
    }

    /**
     * Get evacuation center type from tags
     */
    private function getEvacuationType($tags)
    {
        if (isset($tags['amenity'])) {
            if ($tags['amenity'] === 'shelter') return 'Tempat Penampungan';
            if ($tags['amenity'] === 'social_facility') return 'Fasilitas Sosial';
        }

        if (isset($tags['building'])) {
            if ($tags['building'] === 'school') return 'Sekolah';
            if ($tags['building'] === 'public') return 'Gedung Publik';
            if ($tags['building'] === 'government') return 'Gedung Pemerintah';
        }

        if (isset($tags['landuse'])) {
            if ($tags['landuse'] === 'recreation_ground') return 'Lapangan Rekreasi';
            if ($tags['landuse'] === 'grass') return 'Rumput Terbuka';
            if ($tags['landuse'] === 'village_green') return 'Lapangan Desa';
        }

        return 'Pusat Evakuasi';
    }

    /**
     * Fallback data for geocoding
     */
    private function getGeocodeFallback($address)
    {
        return [
            'status' => 'fallback',
            'message' => 'Layanan geocoding tidak tersedia',
            'alamat' => $address,
            'koordinat' => [
                'latitude' => 0,
                'longitude' => 0
            ],
            'detail' => [
                'type' => 'fallback',
                'importance' => 0,
                'class' => 'unknown',
                'type_class' => 'unknown'
            ]
        ];
    }

    /**
     * Fallback data for reverse geocoding
     */
    private function getReverseGeocodeFallback($lat, $lon)
    {
        return [
            'status' => 'fallback',
            'message' => 'Layanan reverse geocoding tidak tersedia',
            'alamat' => "Koordinat: {$lat}, {$lon}",
            'koordinat' => [
                'latitude' => (float) $lat,
                'longitude' => (float) $lon
            ],
            'address' => [],
            'place_id' => 0,
            'osm_type' => 'unknown'
        ];
    }

    /**
     * Fallback data for disaster locations
     */
    private function getDisasterFallback()
    {
        return [
            'status' => 'fallback',
            'message' => 'Layanan pencarian lokasi tidak tersedia',
            'total' => 0,
            'data' => []
        ];
    }

    /**
     * Fallback data for hospitals
     */
    private function getHospitalFallback()
    {
        return [
            'status' => 'fallback',
            'message' => 'Layanan pencarian rumah sakit tidak tersedia',
            'total' => 0,
            'data' => []
        ];
    }

    /**
     * Fallback data for evacuation centers
     */
    private function getEvacuationFallback()
    {
        return [
            'status' => 'fallback',
            'message' => 'Layanan pencarian pusat evakuasi tidak tersedia',
            'total' => 0,
            'data' => []
        ];
    }

    /**
     * Clear OpenStreetMap cache
     */
    public function clearCache()
    {
        // Clear specific cache keys
        $patterns = [
            'osm_geocode_',
            'osm_reverse_',
            'osm_disaster_locations_',
            'osm_hospitals_',
            'osm_evacuation_'
        ];

        foreach ($patterns as $pattern) {
            // Note: Laravel cache doesn't support pattern deletion directly
            // This would need custom implementation or Redis patterns
        }

        return true;
    }
}