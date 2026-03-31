<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\KategoriBencana;
use App\Models\Kecamatan;
use App\Models\Laporans;
use App\Models\Pengguna;
use App\Models\Provinsi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    private function seedWilayahMinimal(): Desa
    {
        $provinsi  = Provinsi::create(['nama' => 'Provinsi Test']);
        $kabupaten = Kabupaten::create(['nama' => 'Kabupaten Test', 'id_provinsi' => $provinsi->id]);
        $kecamatan = Kecamatan::create(['nama' => 'Kecamatan Test', 'id_kabupaten' => $kabupaten->id]);
        return Desa::create(['nama' => 'Desa Test', 'id_kecamatan' => $kecamatan->id]);
    }

    private function createUser(string $role, ?int $idDesa = null): Pengguna
    {
        return Pengguna::create([
            'nama'     => $role . ' Test',
            'username' => strtolower($role) . '_' . random_int(1000, 9999),
            'password' => 'password123',
            'role'     => $role,
            'email'    => strtolower($role) . '_' . random_int(1000, 9999) . '@test.com',
            'id_desa'  => $idDesa,
        ]);
    }

    private function makeLaporan(Pengguna $pelapor, int $desaId): Laporans
    {
        $kategori = KategoriBencana::create(['nama_kategori' => 'Banjir-' . random_int(1, 999), 'deskripsi' => 'Uji']);
        return Laporans::create([
            'id_pelapor'          => $pelapor->id,
            'id_kategori_bencana' => $kategori->id,
            'id_desa'             => $desaId,
            'judul_laporan'       => 'Laporan Test',
            'deskripsi'           => 'Deskripsi test',
            'tingkat_keparahan'   => 'Tinggi',
            'status'              => 'Draft',
            'latitude'            => -6.2,
            'longitude'           => 106.8,
            'waktu_laporan'       => now(),
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // P1: SoftDeletes
    // ────────────────────────────────────────────────────────────────────────

    public function test_soft_deleted_laporan_not_in_listing_but_remains_in_db(): void
    {
        $desa    = $this->seedWilayahMinimal();
        $warga   = $this->createUser('Warga', $desa->id);
        $laporan = $this->makeLaporan($warga, $desa->id);
        $id      = $laporan->id;

        $token = JWTAuth::fromUser($warga);

        // Hapus laporan (soft delete)
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/laporans/' . $id)
            ->assertStatus(200);

        // Tidak muncul di listing
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/laporans')
            ->assertStatus(200)
            ->assertJsonMissing(['id' => $id]);

        // Masih ada di DB (soft delete, bukan hard delete)
        $this->assertSoftDeleted('laporans', ['id' => $id]);
    }

    public function test_soft_deleted_pengguna_cannot_login(): void
    {
        $desa  = $this->seedWilayahMinimal();
        $warga = $this->createUser('Warga', $desa->id);
        $warga->delete(); // soft delete

        $response = $this->postJson('/api/auth/login', [
            'username' => $warga->username,
            'password' => 'password123',
        ]);

        // User ter-soft-delete tidak bisa login
        $response->assertStatus(401);
    }

    // ────────────────────────────────────────────────────────────────────────
    // P2: Statistics Cache
    // ────────────────────────────────────────────────────────────────────────

    public function test_statistics_response_is_cached_on_second_request(): void
    {
        $desa  = $this->seedWilayahMinimal();
        $admin = $this->createUser('Admin', $desa->id);
        $token = JWTAuth::fromUser($admin);

        // Flush cache terlebih dahulu
        Cache::flush();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/laporans/statistics')
            ->assertStatus(200);

        $cacheContext = [
            'period' => 'all',
            'id_desa' => null,
            'id_pelapor' => null,
        ];
        $cacheKey = 'laporans.statistics.' . md5(json_encode($cacheContext));

        // Setelah request pertama, cache harus sudah terisi
        $this->assertTrue(
            Cache::has($cacheKey),
            'Cache statistik terfilter harus terisi setelah request pertama'
        );

        // Request kedua harus mengambil dari cache (key masih sama)
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/laporans/statistics')
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_statistics_can_be_filtered_by_id_desa_and_id_pelapor(): void
    {
        $desaA = $this->seedWilayahMinimal();

        $provinsiB  = Provinsi::create(['nama' => 'Provinsi Test B']);
        $kabupatenB = Kabupaten::create(['nama' => 'Kabupaten Test B', 'id_provinsi' => $provinsiB->id]);
        $kecamatanB = Kecamatan::create(['nama' => 'Kecamatan Test B', 'id_kabupaten' => $kabupatenB->id]);
        $desaB = Desa::create(['nama' => 'Desa Test B', 'id_kecamatan' => $kecamatanB->id]);

        $admin = $this->createUser('Admin', $desaA->id);
        $pelaporA = $this->createUser('Warga', $desaA->id);
        $pelaporB = $this->createUser('Warga', $desaB->id);

        $this->makeLaporan($pelaporA, $desaA->id);
        $this->makeLaporan($pelaporA, $desaA->id);
        $this->makeLaporan($pelaporB, $desaB->id);

        $token = JWTAuth::fromUser($admin);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/laporans/statistics?id_desa=' . $desaA->id)
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_laporan', 2);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/laporans/statistics?id_pelapor=' . $pelaporB->id)
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_laporan', 1);
    }

    public function test_statistics_for_warga_forbids_other_pelapor_filter(): void
    {
        $desa = $this->seedWilayahMinimal();
        $warga = $this->createUser('Warga', $desa->id);
        $otherWarga = $this->createUser('Warga', $desa->id);

        $token = JWTAuth::fromUser($warga);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/laporans/statistics?id_pelapor=' . $otherWarga->id)
            ->assertStatus(403)
            ->assertJsonPath('code', 'INSUFFICIENT_PERMISSIONS');
    }

    // ────────────────────────────────────────────────────────────────────────
    // P3: Security Headers
    // ────────────────────────────────────────────────────────────────────────

    public function test_api_response_includes_security_headers(): void
    {
        $response = $this->getJson('/api/wilayah/provinsi');

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    // ────────────────────────────────────────────────────────────────────────
    // P3: API Versioning
    // ────────────────────────────────────────────────────────────────────────

    public function test_api_v1_routes_accessible(): void
    {
        // v1 route harus jalan
        $this->getJson('/api/v1/wilayah/provinsi')->assertStatus(200);
    }

    public function test_legacy_routes_still_accessible_for_backward_compat(): void
    {
        // Route lama tanpa /v1/ masih harus bisa diakses
        $this->getJson('/api/wilayah/provinsi')->assertStatus(200);
    }
}
