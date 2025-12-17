<?php

namespace App\Services;

use App\Models\BencanaBmkg;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BmkgService
{
    protected $baseUrl = 'https://data.bmkg.go.id';

    /**
     * Mendapatkan data autogempa dari BMKG (XML)
     * @return array
     */
    public function getAutoEarthquake()
    {
        try {
            // URL untuk data autogempa dari BMKG dalam format XML berdasarkan dokumentasi resmi
            $response = Http::timeout(30)->get('https://data.bmkg.go.id/DataMKG/TEWS/autogempa.xml');

            if ($response->successful()) {
                // Parse XML menjadi array
                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($response->body());

                if ($xml !== false) {
                    // Konversi ke array
                    $data = json_decode(json_encode($xml), true);

                    // Berdasarkan struktur data aktual dari BMKG, root elemen adalah Infogempa
                    // dan di dalamnya terdapat satu elemen gempa
                    if (isset($data['gempa'])) {
                        // Kembalikan dalam bentuk array untuk konsistensi dengan fungsi sync
                        return [$data['gempa']];
                    }

                    return null;
                } else {
                    // Log error jika parsing XML gagal
                    $errors = libxml_get_errors();
                    Log::error('Gagal parsing XML autogempa dari data.bmkg.go.id', [
                        'errors' => $errors,
                        'response_body' => $response->body()
                    ]);
                }
            }

            Log::warning('Gagal mengambil data autogempa dari BMKG', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data autogempa dari BMKG', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Mendapatkan data gempa dirasakan dari BMKG (XML)
     * @return array
     */
    public function getEarthquakeFelt()
    {
        try {
            // URL untuk data gempabumi dirasakan dari BMKG dalam format XML berdasarkan dokumentasi resmi
            $response = Http::timeout(30)->get('https://data.bmkg.go.id/DataMKG/TEWS/gempadirasakan.xml');

            if ($response->successful()) {
                // Parse XML menjadi array
                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($response->body());

                if ($xml !== false) {
                    // Konversi ke array
                    $data = json_decode(json_encode($xml), true);

                    // Berdasarkan struktur data aktual dari BMKG, root elemen adalah Infogempa
                    // dan di dalamnya terdapat beberapa elemen gempa yang dirasakan
                    if (isset($data['gempa']) && is_array($data['gempa'])) {
                        return $data['gempa'];
                    }

                    // Jika hanya satu entri, ubah ke array
                    if (isset($data['gempa'])) {
                        return [$data['gempa']];
                    }
                } else {
                    // Log error jika parsing XML gagal
                    $errors = libxml_get_errors();
                    Log::error('Gagal parsing XML gempadirasakan dari data.bmkg.go.id', [
                        'errors' => $errors,
                        'response_body' => $response->body()
                    ]);
                }
            }

            Log::warning('Gagal mengambil data gempa dirasakan dari BMKG', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data gempa dirasakan dari BMKG', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Mendapatkan data gempa terkini dari BMKG (XML)
     * @return array
     */
    public function getLatestEarthquake()
    {
        try {
            // URL untuk data gempaterkini dari BMKG dalam format XML berdasarkan dokumentasi resmi
            $response = Http::timeout(30)->get('https://data.bmkg.go.id/DataMKG/TEWS/gempaterkini.xml');

            if ($response->successful()) {
                // Parse XML menjadi array
                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($response->body());

                if ($xml !== false) {
                    // Konversi ke array
                    $data = json_decode(json_encode($xml), true);

                    // Berdasarkan struktur data aktual dari BMKG, root elemen adalah Infogempa
                    // dan di dalamnya terdapat beberapa elemen gempa
                    if (isset($data['gempa']) && is_array($data['gempa'])) {
                        return $data['gempa'];
                    }

                    // Jika hanya satu entri gempa, ubah ke array
                    if (isset($data['gempa'])) {
                        return [$data['gempa']];
                    }
                } else {
                    // Log error jika parsing XML gagal
                    $errors = libxml_get_errors();
                    Log::error('Gagal parsing XML gempaterkini dari data.bmkg.go.id', [
                        'errors' => $errors,
                        'response_body' => $response->body()
                    ]);
                }
            }

            Log::warning('Gagal mengambil data gempaterkini dari BMKG', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data gempaterkini dari BMKG', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Mendapatkan data gempa terkini dari repositori GitHub (fallback)
     * @return array
     */
    public function getLatestEarthquakeFromRepo()
    {
        try {
            // Alternatif: URL dari repositori infoBMKG/data-gempabumi
            $response = Http::timeout(30)
                ->get('https://raw.githubusercontent.com/BMKG/data-gempabumi/main/gempa-today.json');

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Gagal mengambil data gempa dari GitHub repo', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data gempa dari GitHub repo', [
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Mendapatkan data cuaca harian
     * @return array
     */
    public function getDailyWeather()
    {
        try {
            // URL untuk data cuaca dari repositori infoBMKG/data-cuaca
            $response = Http::timeout(30)
                ->get('https://raw.githubusercontent.com/BMKG/data-cuaca/main/cuaca-harian.json');

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Gagal mengambil data cuaca harian dari GitHub repo', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data cuaca harian dari GitHub repo', [
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Mendapatkan data CAP (Cuaca Awan Potensial)
     * @return array
     */
    public function getCapData()
    {
        try {
            // URL untuk data CAP dari repositori infoBMKG/data-cap
            $response = Http::timeout(30)
                ->get('https://raw.githubusercontent.com/BMKG/data-cap/main/cap-latest.json');

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Gagal mengambil data CAP dari GitHub repo', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data CAP dari GitHub repo', [
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Sinkronisasi data autogempa ke database
     * @return int Jumlah data yang disimpan
     */
    public function syncAutoEarthquake()
    {
        $data = $this->getAutoEarthquake();

        if (!$data) {
            return 0;
        }

        $count = 0;

        // Ambil data gempa dari hasil parsing
        $gempaData = $data['gempa'] ?? [];

        // Jika hanya satu gempa, buat menjadi array
        if (isset($gempaData['Tanggal'])) {
            $gempaData = [$gempaData];
        }

        foreach ($gempaData as $gempa) {
            // Gunakan kolom yang sesuai dengan struktur data BMKG dari XML
            $tanggal = $gempa['Tanggal'] ?? '';
            $jam = $gempa['Jam'] ?? '';
            $judul = $tanggal . ' ' . $jam;

            // Ekstrak koordinat dari format "longitude,lattitude"
            $coordinates = $gempa['point']['coordinates'] ?? '';
            $coordArray = explode(',', $coordinates);
            // Berdasarkan pengamatan data BMKG, format koordinat adalah "latitude,longitude" (bukan "longitude,latitude")
            // Jadi coordArray[0] adalah latitude (lintang) dan coordArray[1] adalah longitude (bujur)
            $latitude = isset($coordArray[0]) ? floatval(trim($coordArray[0])) : null;
            $longitude = isset($coordArray[1]) ? floatval(trim($coordArray[1])) : null;

            // Sebagai alternatif, jika nilai koordinat tidak ditemukan atau tidak valid,
            // kita bisa mencoba ekstrak dari teks Lintang/Bujur
            if ($latitude === null || $longitude === null) {
                // Ekstrak nilai numerik dari string seperti "6.62 LS" atau "131.16 BT"
                $latText = $gempa['Lintang'] ?? '';
                $lngText = $gempa['Bujur'] ?? '';

                // Ekstrak nilai numerik dari teks lintang
                if (!empty($latText)) {
                    preg_match('/([-]?[0-9.]+)/', $latText, $latMatches);
                    $latitude = isset($latMatches[1]) ? floatval($latMatches[1]) : null;
                }

                // Ekstrak nilai numerik dari teks bujur
                if (!empty($lngText)) {
                    preg_match('/([-]?[0-9.]+)/', $lngText, $lngMatches);
                    $longitude = isset($lngMatches[1]) ? floatval($lngMatches[1]) : null;
                }
            }

            // Untuk autogempa, karena hanya ada satu entri yang selalu diperbarui,
            // kita akan membuat atau memperbarui entri dengan jenis_bencana = 'autogempa'
            $existing = BencanaBmkg::where('jenis_bencana', 'autogempa')
                ->first();

            if (!$existing) {
                // Jika belum ada entri autogempa, buat entri baru
                BencanaBmkg::create([
                    'jenis_bencana' => 'autogempa',
                    'judul' => $judul,
                    'isi_data' => json_encode($gempa),
                    'waktu_pembaruan' => now(),
                    'lokasi' => $gempa['Wilayah'] ?? null,
                    'lintang' => $latitude,
                    'bujur' => $longitude,
                    'magnitude' => floatval($gempa['Magnitude'] ?? 0),
                    'kedalaman' => $gempa['Kedalaman'] ?? null,
                    'peringkat' => $gempa['Potensi'] ?? null,
                    'sumber_data' => 'https://data.bmkg.go.id/DataMKG/TEWS/autogempa.xml'
                ]);
                $count++;
            } else {
                // Jika sudah ada, periksa apakah datanya berbeda (update terbaru)
                $existingData = json_decode($existing->isi_data, true);
                if (($existingData['DateTime'] ?? '') !== ($gempa['DateTime'] ?? '')) {
                    // Jika DateTime berbeda, update entri
                    $existing->update([
                        'judul' => $judul,
                        'isi_data' => json_encode($gempa),
                        'waktu_pembaruan' => now(),
                        'lokasi' => $gempa['Wilayah'] ?? null,
                        'lintang' => $latitude,
                        'bujur' => $longitude,
                        'magnitude' => floatval($gempa['Magnitude'] ?? 0),
                        'kedalaman' => $gempa['Kedalaman'] ?? null,
                        'peringkat' => $gempa['Potensi'] ?? null,
                        'sumber_data' => 'https://data.bmkg.go.id/DataMKG/TEWS/autogempa.xml'
                    ]);
                    $count++; // Tandai sebagai data baru jika ada update
                }
            }
        }

        return $count;
    }

    /**
     * Sinkronisasi data gempa terkini ke database
     * @return int Jumlah data yang disimpan
     */
    public function syncLatestEarthquakes()
    {
        $data = $this->getLatestEarthquake();

        if (!$data) {
            // Jika data dari situs resmi tidak tersedia, coba dari repositori
            $data = $this->getLatestEarthquakeFromRepo();
        }

        if (!$data) {
            return 0;
        }

        $count = 0;

        // Pastikan data berbentuk array
        if (!is_array($data)) {
            $data = [$data];
        }

        foreach ($data as $gempa) {
            // Gunakan kolom yang sesuai dengan struktur data BMKG
            $tanggal = $gempa['Tanggal'] ?? '';
            $jam = $gempa['Jam'] ?? '';
            $judul = $tanggal . ' ' . $jam;
            $dateTime = $gempa['DateTime'] ?? '';

            // Ekstrak koordinat dari format "longitude,lattitude"
            $coordinates = $gempa['point']['coordinates'] ?? '';
            $coordArray = explode(',', $coordinates);
            // Berdasarkan pengamatan data BMKG, format koordinat adalah "latitude,longitude" (bukan "longitude,latitude")
            // Jadi coordArray[0] adalah latitude (lintang) dan coordArray[1] adalah longitude (bujur)
            $latitude = isset($coordArray[0]) ? floatval(trim($coordArray[0])) : null;
            $longitude = isset($coordArray[1]) ? floatval(trim($coordArray[1])) : null;

            // Sebagai alternatif, jika nilai koordinat tidak ditemukan atau tidak valid,
            // kita bisa mencoba ekstrak dari teks Lintang/Bujur
            if ($latitude === null || $longitude === null) {
                // Ekstrak nilai numerik dari string seperti "6.62 LS" atau "131.16 BT"
                $latText = $gempa['Lintang'] ?? '';
                $lngText = $gempa['Bujur'] ?? '';

                // Ekstrak nilai numerik dari teks lintang
                if (!empty($latText)) {
                    preg_match('/([-]?[0-9.]+)/', $latText, $latMatches);
                    $latitude = isset($latMatches[1]) ? floatval($latMatches[1]) : null;
                }

                // Ekstrak nilai numerik dari teks bujur
                if (!empty($lngText)) {
                    preg_match('/([-]?[0-9.]+)/', $lngText, $lngMatches);
                    $longitude = isset($lngMatches[1]) ? floatval($lngMatches[1]) : null;
                }
            }

            // Cek apakah data sudah ada di database berdasarkan DateTime (unik untuk setiap gempa)
            $existing = BencanaBmkg::where('isi_data->DateTime', $dateTime)
                ->where('jenis_bencana', 'gempa_terkini')
                ->first();

            if (!$existing) {
                BencanaBmkg::create([
                    'jenis_bencana' => 'gempa_terkini',
                    'judul' => $judul,
                    'isi_data' => json_encode($gempa),
                    'waktu_pembaruan' => now(),
                    'lokasi' => $gempa['Wilayah'] ?? null,
                    'lintang' => $latitude,
                    'bujur' => $longitude,
                    'magnitude' => floatval($gempa['Magnitude'] ?? 0),
                    'kedalaman' => $gempa['Kedalaman'] ?? null,
                    'peringkat' => $gempa['Potensi'] ?? null,
                    'sumber_data' => 'https://data.bmkg.go.id/DataMKG/TEWS/gempaterkini.xml'
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Sinkronisasi data gempa dirasakan ke database
     * @return int Jumlah data yang disimpan
     */
    public function syncEarthquakeFelt()
    {
        $data = $this->getEarthquakeFelt();

        if (!$data) {
            return 0;
        }

        $count = 0;

        // Pastikan data berbentuk array
        if (!is_array($data)) {
            $data = [$data];
        }

        foreach ($data as $gempa) {
            // Gunakan kolom yang sesuai dengan struktur data BMKG
            $tanggal = $gempa['Tanggal'] ?? '';
            $jam = $gempa['Jam'] ?? '';
            $judul = $tanggal . ' ' . $jam;
            $dateTime = $gempa['DateTime'] ?? '';

            // Ekstrak koordinat dari format "longitude,lattitude"
            $coordinates = $gempa['point']['coordinates'] ?? '';
            $coordArray = explode(',', $coordinates);
            // Berdasarkan pengamatan data BMKG, format koordinat adalah "latitude,longitude" (bukan "longitude,latitude")
            // Jadi coordArray[0] adalah latitude (lintang) dan coordArray[1] adalah longitude (bujur)
            $latitude = isset($coordArray[0]) ? floatval(trim($coordArray[0])) : null;
            $longitude = isset($coordArray[1]) ? floatval(trim($coordArray[1])) : null;

            // Sebagai alternatif, jika nilai koordinat tidak ditemukan atau tidak valid,
            // kita bisa mencoba ekstrak dari teks Lintang/Bujur
            if ($latitude === null || $longitude === null) {
                // Ekstrak nilai numerik dari string seperti "6.62 LS" atau "131.16 BT"
                $latText = $gempa['Lintang'] ?? '';
                $lngText = $gempa['Bujur'] ?? '';

                // Ekstrak nilai numerik dari teks lintang
                if (!empty($latText)) {
                    preg_match('/([-]?[0-9.]+)/', $latText, $latMatches);
                    $latitude = isset($latMatches[1]) ? floatval($latMatches[1]) : null;
                }

                // Ekstrak nilai numerik dari teks bujur
                if (!empty($lngText)) {
                    preg_match('/([-]?[0-9.]+)/', $lngText, $lngMatches);
                    $longitude = isset($lngMatches[1]) ? floatval($lngMatches[1]) : null;
                }
            }

            // Cek apakah data sudah ada di database berdasarkan DateTime (unik untuk setiap gempa)
            $existing = BencanaBmkg::where('isi_data->DateTime', $dateTime)
                ->where('jenis_bencana', 'gempa_dirasakan')
                ->first();

            if (!$existing) {
                BencanaBmkg::create([
                    'jenis_bencana' => 'gempa_dirasakan',
                    'judul' => $judul,
                    'isi_data' => json_encode($gempa),
                    'waktu_pembaruan' => now(),
                    'lokasi' => $gempa['Wilayah'] ?? null,
                    'lintang' => $latitude,
                    'bujur' => $longitude,
                    'magnitude' => floatval($gempa['Magnitude'] ?? 0),
                    'kedalaman' => $gempa['Kedalaman'] ?? null,
                    'peringkat' => $gempa['Potensi'] ?? null,
                    'sumber_data' => 'https://data.bmkg.go.id/DataMKG/TEWS/gempadirasakan.xml'
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Sinkronisasi data cuaca terkini
     * @return int Jumlah data yang disimpan
     */
    public function syncWeatherData()
    {
        $data = $this->getDailyWeather();

        if (!$data) {
            return 0;
        }

        $count = 0;

        if (!is_array($data)) {
            $data = [$data];
        }

        foreach ($data as $cuaca) {
            $judul = $cuaca['kota'] ?? $cuaca['wilayah'] ?? 'Cuaca Harian';

            $existing = BencanaBmkg::where('judul', $judul)
                ->where('jenis_bencana', 'cuaca_ekstrem')
                ->first();

            if (!$existing) {
                BencanaBmkg::create([
                    'jenis_bencana' => 'cuaca_ekstrem',
                    'judul' => $judul,
                    'isi_data' => json_encode($cuaca),
                    'waktu_pembaruan' => now(),
                    'lokasi' => $cuaca['kota'] ?? $cuaca['wilayah'] ?? null,
                    'peringkat' => $cuaca['peringatan'] ?? null,
                    'sumber_data' => 'https://raw.githubusercontent.com/BMKG/data-cuaca/main/cuaca-harian.json'
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Sinkronisasi data CAP (Cuaca Awan Potensial)
     * @return int Jumlah data yang disimpan
     */
    public function syncCapData()
    {
        $data = $this->getCapData();

        if (!$data) {
            return 0;
        }

        $count = 0;

        if (!is_array($data)) {
            $data = [$data];
        }

        foreach ($data as $cap) {
            $judul = $cap['judul'] ?? $cap['tanggal'] ?? 'CAP Data';

            $existing = BencanaBmkg::where('judul', $judul)
                ->where('jenis_bencana', 'peringatan_dini')
                ->first();

            if (!$existing) {
                BencanaBmkg::create([
                    'jenis_bencana' => 'peringatan_dini',
                    'judul' => $judul,
                    'isi_data' => json_encode($cap),
                    'waktu_pembaruan' => now(),
                    'lokasi' => $cap['lokasi'] ?? null,
                    'peringkat' => $cap['tingkat'] ?? null,
                    'sumber_data' => 'https://raw.githubusercontent.com/BMKG/data-cap/main/cap-latest.json'
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Fungsi bantu untuk mengekstrak nilai koordinat dari string
     * @param string $coordinate
     * @return float|null
     */
    private function extractCoordinate($coordinate)
    {
        if (empty($coordinate)) {
            return null;
        }

        // Menghapus huruf (N/S/E/W) dari koordinat
        preg_match('/([0-9.-]+)/', $coordinate, $matches);
        return isset($matches[1]) ? floatval($matches[1]) : null;
    }

    /**
     * Alias untuk syncCapData - untuk kompatibilitas
     * @return int Jumlah data yang disimpan
     */
    public function syncWeatherAlerts()
    {
        return $this->syncCapData();
    }

    /**
     * Sinkronisasi semua data BMKG
     * @return array Jumlah data yang disimpan untuk setiap jenis
     */
    public function syncAllBmkgData()
    {
        return [
            'autogempa' => $this->syncAutoEarthquake(),
            'gempa_terkini' => $this->syncLatestEarthquakes(),
            'gempa_dirasakan' => $this->syncEarthquakeFelt(),
            'cuaca' => $this->syncWeatherData(),
            'cap' => $this->syncCapData()
        ];
    }
    
    /**
     * Menyimpan data langsung ke database
     */
    public function saveBencanaData($jenisBencana, $data)
    {
        $bencana = new BencanaBmkg();
        $bencana->jenis_bencana = $jenisBencana;
        $bencana->judul = $data['judul'] ?? $data['Tanggal'] ?? 'Data Bencana';
        $bencana->isi_data = $data;
        $bencana->waktu_pembaruan = now();
        $bencana->lokasi = $data['Wilayah'] ?? $data['lokasi'] ?? null;
        $bencana->lintang = $data['Lintang'] ?? $data['lintang'] ?? null;
        $bencana->bujur = $data['Bujur'] ?? $data['bujur'] ?? null;
        $bencana->magnitude = $data['Magnitude'] ?? $data['magnitude'] ?? null;
        $bencana->kedalaman = $data['Kedalaman'] ?? $data['kedalaman'] ?? null;
        $bencana->peringkat = $data['Potensi'] ?? $data['peringkat'] ?? null;
        $bencana->sumber_data = $this->baseUrl;
        $bencana->save();
        
        return $bencana;
    }
    
    /**
     * Mendapatkan semua data bencana dari BMKG dari database
     */
    public function getAllBencanaData($jenisBencana = null, $limit = null)
    {
        $query = BencanaBmkg::orderBy('waktu_pembaruan', 'desc');
        
        if ($jenisBencana) {
            $query->where('jenis_bencana', $jenisBencana);
        }
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Mendapatkan data bencana terbaru berdasarkan jenis
     */
    public function getLatestBencanaByType($jenisBencana, $limit = 10)
    {
        return BencanaBmkg::where('jenis_bencana', $jenisBencana)
            ->orderBy('waktu_pembaruan', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Format data gempa untuk respons API sesuai dokumentasi BMKG
     */
    public function formatEarthquakeData($bencanaBmkg)
    {
        $isiData = $bencanaBmkg->isi_data;

        // Jika isi_data adalah string, decode ke array
        if (is_string($isiData)) {
            $isiData = json_decode($isiData, true);
        }

        return [
            'Tanggal' => $bencanaBmkg->tanggal ?: ($isiData['Tanggal'] ?? null),
            'Jam' => $bencanaBmkg->jam ?: ($isiData['Jam'] ?? null),
            'DateTime' => $bencanaBmkg->waktu_lengkap ?: ($isiData['DateTime'] ?? null),
            'Magnitude' => $bencanaBmkg->magnitudo ?: ($isiData['Magnitude'] ?? null),
            'Kedalaman' => $bencanaBmkg->kedalaman ?: ($isiData['Kedalaman'] ?? null),
            'point' => [
                'coordinates' => $bencanaBmkg->koordinat ?: ($isiData['point']['coordinates'] ?? null)
            ],
            'Lintang' => $bencanaBmkg->lintang_text ?: ($isiData['Lintang'] ?? null),
            'Bujur' => $bencanaBmkg->bujur_text ?: ($isiData['Bujur'] ?? null),
            'Wilayah' => $bencanaBmkg->lokasi_lengkap ?: ($isiData['Wilayah'] ?? null),
            'Potensi' => $bencanaBmkg->potensi ?: ($isiData['Potensi'] ?? null),
            'Dirasakan' => $bencanaBmkg->dirasakan ?: ($isiData['Dirasakan'] ?? null),
            'Shakemap' => $isiData['Shakemap'] ?? null
        ];
    }

    /**
     * Format multiple earthquake data
     */
    public function formatMultipleEarthquakeData($collection)
    {
        return $collection->map(function ($item) {
            return $this->formatEarthquakeData($item);
        });
    }

    /**
     * Mendapatkan data prakiraan cuaca berdasarkan kode administrasi (adm4)
     * @param string $adm4Code Kode administrasi level 4 (desa/kelurahan)
     * @return array|null
     */
    public function getWeatherForecast($adm4Code)
    {
        try {
            $apiUrl = "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4={$adm4Code}";
            $response = Http::timeout(30)->get($apiUrl);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Gagal mengambil data prakiraan cuaca dari BMKG API', [
                'adm4_code' => $adm4Code,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data prakiraan cuaca dari BMKG API', [
                'adm4_code' => $adm4Code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Sinkronisasi data prakiraan cuaca ke database
     * @param string $adm4Code Kode administrasi level 4
     * @return int Jumlah data yang disimpan
     */
    public function syncWeatherForecast($adm4Code)
    {
        $data = $this->getWeatherForecast($adm4Code);

        if (!$data) {
            return 0;
        }

        $count = 0;

        try {
            // Extract location information
            $lokasi = $data['lokasi'] ?? [];
            $desa = $lokasi['desa'] ?? '';
            $kecamatan = $lokasi['kecamatan'] ?? '';
            $kotkab = $lokasi['kotkab'] ?? '';
            $provinsi = $lokasi['provinsi'] ?? '';
            $lat = $lokasi['lat'] ?? null;
            $lon = $lokasi['lon'] ?? null;

            // Extract weather forecast data
            $cuacaData = $data['data'] ?? [];

            if (!empty($cuacaData) && isset($cuacaData[0]['cuaca'])) {
                foreach ($cuacaData[0]['cuaca'] as $index => $cuaca) {
                    $localDatetime = $cuaca['local_datetime'] ?? '';
                    $judul = "Prakiraan Cuaca - {$desa}, {$kecamatan} - {$localDatetime}";

                    // Check if data already exists
                    $existing = BencanaBmkg::where('jenis_bencana', 'prakiraan_cuaca')
                        ->where('lokasi', "{$desa}, {$kecamatan}")
                        ->where('judul', $judul)
                        ->first();

                    if (!$existing) {
                        BencanaBmkg::create([
                            'jenis_bencana' => 'prakiraan_cuaca',
                            'judul' => $judul,
                            'isi_data' => json_encode($cuaca),
                            'waktu_pembaruan' => now(),
                            'lokasi' => "{$desa}, {$kecamatan}, {$kotkab}, {$provinsi}",
                            'lintang' => $lat ? floatval($lat) : null,
                            'bujur' => $lon ? floatval($lon) : null,
                            'peringkat' => $cuaca['weather_desc'] ?? null,
                            'sumber_data' => "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4={$adm4Code}",
                            'id_bencana' => 'prakiraan_cuaca_' . $adm4Code . '_' . $index
                        ]);
                        $count++;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan data prakiraan cuaca', [
                'adm4_code' => $adm4Code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $count;
    }

    /**
     * Mendapatkan data prakiraan cuaca dari database
     * @param string $lokasi Nama lokasi (desa/kecamatan)
     * @param int $limit Jumlah data yang ingin diambil
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWeatherForecastFromDatabase($lokasi = null, $limit = 20)
    {
        $query = BencanaBmkg::where('jenis_bencana', 'prakiraan_cuaca')
            ->orderBy('waktu_pembaruan', 'desc');

        if ($lokasi) {
            $query->where('lokasi', 'LIKE', '%' . $lokasi . '%');
        }

        return $query->limit($limit)->get();
    }

    /**
     * Format data prakiraan cuaca untuk respons API
     */
    public function formatWeatherForecastData($collection)
    {
        return $collection->map(function ($item) {
            $isiData = $item->isi_data;

            // Jika isi_data adalah string, decode ke array
            if (is_string($isiData)) {
                $isiData = json_decode($isiData, true);
            }

            return [
                'id_bencana' => $item->id_bencana,
                'judul' => $item->judul,
                'lokasi' => $item->lokasi,
                'waktu_pembaruan' => $item->waktu_pembaruan,
                'local_datetime' => $isiData['local_datetime'] ?? null,
                'weather_desc' => $isiData['weather_desc'] ?? null,
                'temperature' => $isiData['t'] ?? null,
                'humidity' => $isiData['hu'] ?? null,
                'wind_speed' => $isiData['ws'] ?? null,
                'wind_direction' => $isiData['wd'] ?? null,
                'visibility' => $isiData['vs_text'] ?? null,
                'image_url' => $isiData['image'] ?? null,
                'coordinates' => [
                    'latitude' => $item->lintang,
                    'longitude' => $item->bujur
                ]
            ];
        });
    }

    /**
     * Mendapatkan data RSS feed peringatan dini cuaca (Nowcast) dari BMKG
     * @param string $language Bahasa (id/en)
     * @return array|null
     */
    public function getNowcastRssFeed($language = 'id')
    {
        try {
            $rssUrl = "https://www.bmkg.go.id/alerts/nowcast/{$language}/rss.xml";
            $response = Http::timeout(30)->get($rssUrl);

            if ($response->successful()) {
                // Parse XML RSS feed
                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($response->body());

                if ($xml !== false) {
                    // Convert to array for easier manipulation
                    $data = json_decode(json_encode($xml), true);

                    // Extract items from RSS feed
                    $items = [];
                    if (isset($data['channel']['item'])) {
                        $itemsData = $data['channel']['item'];

                        // Handle single item vs multiple items
                        if (!isset($itemsData[0])) {
                            $itemsData = [$itemsData];
                        }

                        foreach ($itemsData as $item) {
                            $items[] = [
                                'title' => $item['title'] ?? null,
                                'link' => $item['link'] ?? null,
                                'description' => $item['description'] ?? null,
                                'author' => $item['author'] ?? null,
                                'pubDate' => $item['pubDate'] ?? null,
                                'guid' => $item['guid'] ?? null
                            ];
                        }
                    }

                    return [
                        'success' => true,
                        'channel' => [
                            'title' => $data['channel']['title'] ?? null,
                            'description' => $data['channel']['description'] ?? null,
                            'link' => $data['channel']['link'] ?? null,
                            'language' => $data['channel']['language'] ?? $language
                        ],
                        'items' => $items
                    ];
                } else {
                    $errors = libxml_get_errors();
                    Log::error('Gagal parsing RSS XML dari BMKG', [
                        'url' => $rssUrl,
                        'errors' => $errors,
                        'response_body' => $response->body()
                    ]);
                }
            }

            Log::warning('Gagal mengambil data RSS feed Nowcast dari BMKG', [
                'url' => $rssUrl,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data RSS feed Nowcast dari BMKG', [
                'url' => $rssUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Mendapatkan data CAP Alert detail dari BMKG
     * @param string $alertCode Kode alert CAP (contoh: CJB20251013001)
     * @param string $language Bahasa (id/en)
     * @return array|null
     */
    public function getNowcastCapDetail($alertCode, $language = 'id')
    {
        try {
            $capUrl = "https://www.bmkg.go.id/alerts/nowcast/{$language}/{$alertCode}_alert.xml";
            $response = Http::timeout(30)->get($capUrl);

            if ($response->successful()) {
                // Parse XML CAP
                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($response->body());

                if ($xml !== false) {
                    // Register CAP namespace
                    $namespaces = $xml->getNamespaces(true);
                    $capNs = isset($namespaces[""])
                        ? $namespaces[""]
                        : "urn:oasis:names:tc:emergency:cap:1.2";

                    // Extract data using CAP namespace
                    $info = $xml->children($capNs)->info;

                    if ($info) {
                        $data = json_decode(json_encode($info), true);

                        // Extract polygon data
                        $polygons = [];
                        if (isset($data['area'])) {
                            $areas = $data['area'];
                            if (!isset($areas[0])) {
                                $areas = [$areas];
                            }

                            foreach ($areas as $area) {
                                if (isset($area['polygon'])) {
                                    $polygonStr = $area['polygon'];
                                    if (!is_array($polygonStr)) {
                                        $polygonStr = [$polygonStr];
                                    }

                                    foreach ($polygonStr as $polygon) {
                                        $coords = [];
                                        $coordPairs = explode(" ", trim($polygon));

                                        foreach ($coordPairs as $pair) {
                                            $latLon = explode(",", trim($pair));
                                            if (count($latLon) === 2 && is_numeric($latLon[0]) && is_numeric($latLon[1])) {
                                                $coords[] = [
                                                    (float) $latLon[0],
                                                    (float) $latLon[1]
                                                ];
                                            }
                                        }

                                        if (!empty($coords)) {
                                            $polygons[] = $coords;
                                        }
                                    }
                                }
                            }
                        }

                        return [
                            'success' => true,
                            'alert_code' => $alertCode,
                            'language' => $language,
                            'identifier' => $data['identifier'] ?? null,
                            'sender' => $data['sender'] ?? null,
                            'sent' => $data['sent'] ?? null,
                            'status' => $data['status'] ?? null,
                            'msgType' => $data['msgType'] ?? null,
                            'source' => $data['source'] ?? null,
                            'scope' => $data['scope'] ?? null,
                            'code' => $data['code'] ?? null,
                            'headline' => $data['headline'] ?? null,
                            'event' => $data['event'] ?? null,
                            'urgency' => $data['urgency'] ?? null,
                            'severity' => $data['severity'] ?? null,
                            'certainty' => $data['certainty'] ?? null,
                            'effective' => $data['effective'] ?? null,
                            'expires' => $data['expires'] ?? null,
                            'senderName' => $data['senderName'] ?? null,
                            'description' => $data['description'] ?? null,
                            'web' => $data['web'] ?? null,
                            'area' => [
                                'areaDesc' => $data['area']['areaDesc'] ?? null,
                                'polygons' => $polygons
                            ]
                        ];
                    }
                } else {
                    $errors = libxml_get_errors();
                    Log::error('Gagal parsing CAP XML dari BMKG', [
                        'alert_code' => $alertCode,
                        'url' => $capUrl,
                        'errors' => $errors,
                        'response_body' => $response->body()
                    ]);
                }
            }

            Log::warning('Gagal mengambil data CAP detail dari BMKG', [
                'alert_code' => $alertCode,
                'url' => $capUrl,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data CAP detail dari BMKG', [
                'alert_code' => $alertCode,
                'url' => $capUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Sinkronisasi data peringatan dini cuaca (Nowcast RSS) ke database
     * @param string $language Bahasa (id/en)
     * @return int Jumlah data yang disimpan
     */
    public function syncNowcastRssData($language = 'id')
    {
        $data = $this->getNowcastRssFeed($language);

        if (!$data || !$data['success']) {
            return 0;
        }

        $count = 0;

        foreach ($data['items'] as $item) {
            $alertCode = $this->extractAlertCodeFromLink($item['link']);

            if ($alertCode) {
                $judul = "Peringatan Dini Cuaca - " . $item['title'];
                $lokasi = $this->extractLocationFromTitle($item['title']);

                // Check if data already exists
                $existing = BencanaBmkg::where('jenis_bencana', 'peringatan_dini_cuaca')
                    ->where('id_bencana', $alertCode)
                    ->where('sumber_data', 'like', '%' . $alertCode . '%')
                    ->first();

                if (!$existing) {
                    BencanaBmkg::create([
                        'jenis_bencana' => 'peringatan_dini_cuaca',
                        'judul' => $judul,
                        'isi_data' => json_encode($item),
                        'waktu_pembaruan' => now(),
                        'lokasi' => $lokasi,
                        'peringkat' => $item['title'] ?? null,
                        'sumber_data' => $item['link'],
                        'id_bencana' => $alertCode
                    ]);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Sinkronisasi data CAP detail ke database
     * @param string $alertCode Kode alert CAP
     * @param string $language Bahasa (id/en)
     * @return int Jumlah data yang disimpan
     */
    public function syncNowcastCapDetail($alertCode, $language = 'id')
    {
        $data = $this->getNowcastCapDetail($alertCode, $language);

        if (!$data || !$data['success']) {
            return 0;
        }

        $count = 0;

        // Check if data already exists
        $existing = BencanaBmkg::where('jenis_bencana', 'detail_peringatan_dini')
            ->where('id_bencana', $alertCode)
            ->where('sumber_data', 'like', '%' . $alertCode . '_alert.xml%')
            ->first();

        if (!$existing) {
            // Extract coordinates from polygons
            $lat = null;
            $lon = null;

            if (!empty($data['area']['polygons'][0])) {
                $firstPolygon = $data['area']['polygons'][0];
                if (!empty($firstPolygon[0])) {
                    $lat = $firstPolygon[0][0];
                    $lon = $firstPolygon[0][1];
                }
            }

            BencanaBmkg::create([
                'jenis_bencana' => 'detail_peringatan_dini',
                'judul' => $data['headline'],
                'isi_data' => json_encode($data),
                'waktu_pembaruan' => now(),
                'lokasi' => $data['area']['areaDesc'] ?? null,
                'lintang' => $lat,
                'bujur' => $lon,
                'peringkat' => $data['severity'] ?? null,
                'sumber_data' => "https://www.bmkg.go.id/alerts/nowcast/{$language}/{$alertCode}_alert.xml",
                'id_bencana' => $alertCode
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Extract alert code from link URL
     * @param string $link
     * @return string|null
     */
    private function extractAlertCodeFromLink($link)
    {
        if (preg_match('/([A-Z]{3}\d{11})/', $link, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Extract location from title
     * @param string $title
     * @return string
     */
    private function extractLocationFromTitle($title)
    {
        // Extract location from title like "Peringatan Dini Cuaca Jawa Barat"
        if (preg_match('/Peringatan Dini Cuaca\s+(.+)/i', $title, $matches)) {
            return trim($matches[1]);
        }
        return $title;
    }
}