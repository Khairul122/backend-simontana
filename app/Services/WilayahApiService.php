<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

class WilayahApiService
{
    private const BASE_URL = 'https://source.imigrasi.go.id/maestro/api-wilayah-indonesia/api';
    private const TIMEOUT = 30;
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY = 1000; // milliseconds

    /**
     * Get provinces data with retry mechanism
     */
    public function getProvinces(): array
    {
        return $this->makeRequest('/provinces.json', 'provinces');
    }

    /**
     * Get regencies by province ID
     */
    public function getRegenciesByProvince(int $provinceId): array
    {
        return $this->makeRequest("/regencies/{$provinceId}.json", "regencies for province {$provinceId}");
    }

    /**
     * Get districts by regency ID
     */
    public function getDistrictsByRegency(int $regencyId): array
    {
        return $this->makeRequest("/districts/{$regencyId}.json", "districts for regency {$regencyId}");
    }

    /**
     * Get villages by district ID
     */
    public function getVillagesByDistrict(int $districtId): array
    {
        return $this->makeRequest("/villages/{$districtId}.json", "villages for district {$districtId}");
    }

    /**
     * Make HTTP request with retry and validation
     */
    private function makeRequest(string $endpoint, string $context): array
    {
        $url = self::BASE_URL . $endpoint;
        $lastException = null;

        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                $response = Http::timeout(self::TIMEOUT)
                    ->retry(self::MAX_RETRIES, self::RETRY_DELAY / 1000)
                    ->withHeaders([
                        'User-Agent' => 'SIMONTA-BENCANA/1.0',
                        'Accept' => 'application/json',
                    ])
                    ->get($url);

                if ($this->isValidJsonResponse($response)) {
                    $data = $response->json();

                    if (is_array($data) && !empty($data)) {
                        Log::info("Successfully fetched {$context}: " . count($data) . " records");
                        return $data;
                    }
                }

                if ($attempt < self::MAX_RETRIES) {
                    Log::warning("Attempt {$attempt} failed for {$context}, retrying...");
                    usleep(self::RETRY_DELAY * 1000);
                    continue;
                }

            } catch (\Exception $e) {
                $lastException = $e;

                if ($attempt < self::MAX_RETRIES) {
                    Log::warning("API Error on attempt {$attempt} for {$context}: " . $e->getMessage());
                    usleep(self::RETRY_DELAY * 1000);
                    continue;
                }
            }
        }

        // All attempts failed
        $errorMessage = $lastException ? $lastException->getMessage() : "Invalid response format";
        Log::error("Failed to fetch {$context} after " . self::MAX_RETRIES . " attempts: {$errorMessage}");

        throw new \RuntimeException("Unable to fetch {$context}: {$errorMessage}");
    }

    /**
     * Validate if response contains valid JSON data
     */
    private function isValidJsonResponse(Response $response): bool
    {
        if (!$response->successful()) {
            return false;
        }

        $contentType = $response->header('Content-Type', '');
        if (!str_contains($contentType, 'application/json')) {
            return false;
        }

        $body = $response->body();

        // Check for HTML redirects (Cloudflare, auth pages)
        if (str_contains(strtolower($body), '<html') || str_contains(strtolower($body), 'redirect')) {
            return false;
        }

        // Try to decode JSON
        json_decode($body);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Test API connectivity
     */
    public function testConnectivity(): array
    {
        $results = [];

        try {
            $results['provinces'] = $this->getProvinces();
            $results['status'] = 'connected';
            $results['message'] = 'API is accessible and returning valid data';
        } catch (\Exception $e) {
            $results['status'] = 'failed';
            $results['message'] = $e->getMessage();
            $results['provinces'] = [];
        }

        return $results;
    }
}