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
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function seedWilayahMinimal(): array
    {
        $provinsi = Provinsi::create(['nama' => 'Provinsi Uji']);
        $kabupaten = Kabupaten::create([
            'nama' => 'Kabupaten Uji',
            'id_provinsi' => $provinsi->id,
        ]);
        $kecamatan = Kecamatan::create([
            'nama' => 'Kecamatan Uji',
            'id_kabupaten' => $kabupaten->id,
        ]);
        $desa = Desa::create([
            'nama' => 'Desa Uji',
            'id_kecamatan' => $kecamatan->id,
        ]);

        return compact('provinsi', 'kabupaten', 'kecamatan', 'desa');
    }

    private function createUser(string $role, ?int $idDesa = null): Pengguna
    {
        return Pengguna::create([
            'nama' => $role . ' Tester',
            'username' => strtolower($role) . '_tester_' . random_int(1000, 9999),
            'password' => 'password123',
            'role' => $role,
            'email' => strtolower($role) . '_tester@example.com',
            'id_desa' => $idDesa,
        ]);
    }

    private function makeLaporan(Pengguna $pelapor, int $desaId): Laporans
    {
        $kategori = KategoriBencana::create([
            'nama_kategori' => 'Banjir',
            'deskripsi' => 'Kategori uji',
        ]);

        return Laporans::create([
            'id_pelapor' => $pelapor->id,
            'id_kategori_bencana' => $kategori->id,
            'id_desa' => $desaId,
            'judul_laporan' => 'Laporan Uji',
            'deskripsi' => 'Deskripsi laporan uji',
            'tingkat_keparahan' => 'Tinggi',
            'status' => 'Draft',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'waktu_laporan' => now(),
        ]);
    }

    public function test_wilayah_get_index_returns_200(): void
    {
        $this->seedWilayahMinimal();

        $response = $this->getJson('/api/wilayah?jenis=provinsi');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Data wilayah berhasil diambil');
    }

    public function test_wilayah_show_not_found_returns_404(): void
    {
        $this->seedWilayahMinimal();

        $response = $this->getJson('/api/wilayah/999999?jenis=provinsi');

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Wilayah tidak ditemukan');
    }

    public function test_wilayah_store_validation_returns_422(): void
    {
        $this->seedWilayahMinimal();
        $admin = $this->createUser('Admin');
        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/wilayah', [
                'jenis' => 'kabupaten',
                'nama' => 'Kabupaten Baru',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validasi gagal');
    }

    public function test_workflow_verifikasi_returns_200(): void
    {
        $wilayah = $this->seedWilayahMinimal();
        $admin = $this->createUser('Admin');
        $pelapor = $this->createUser('Warga', $wilayah['desa']->id);
        $laporan = $this->makeLaporan($pelapor, $wilayah['desa']->id);

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/laporans/' . $laporan->id . '/verifikasi', [
                'status' => 'Diverifikasi',
                'catatan_verifikasi' => 'Laporan valid',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Laporan berhasil diverifikasi');
    }

    public function test_workflow_verifikasi_not_found_returns_404(): void
    {
        $this->seedWilayahMinimal();
        $admin = $this->createUser('Admin');
        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/laporans/999999/verifikasi', [
                'status' => 'Diverifikasi',
            ]);

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Laporan tidak ditemukan');
    }

    public function test_workflow_verifikasi_validation_returns_422(): void
    {
        $wilayah = $this->seedWilayahMinimal();
        $admin = $this->createUser('Admin');
        $pelapor = $this->createUser('Warga', $wilayah['desa']->id);
        $laporan = $this->makeLaporan($pelapor, $wilayah['desa']->id);

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/laporans/' . $laporan->id . '/verifikasi', [
                'status' => 'INVALID_STATUS',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validasi gagal');
    }

    public function test_laporan_by_pelapor_returns_200(): void
    {
        $wilayah = $this->seedWilayahMinimal();
        $admin = $this->createUser('Admin', $wilayah['desa']->id);
        $pelapor = $this->createUser('Warga', $wilayah['desa']->id);
        $laporan = $this->makeLaporan($pelapor, $wilayah['desa']->id);

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/laporans/pelapor/' . $pelapor->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Data laporan pelapor berhasil diambil')
            ->assertJsonPath('data.0.id', $laporan->id);
    }

    public function test_laporan_store_defaults_status_to_draft_when_not_provided(): void
    {
        $wilayah = $this->seedWilayahMinimal();
        $warga = $this->createUser('Warga', $wilayah['desa']->id);
        $kategori = KategoriBencana::create([
            'nama_kategori' => 'Banjir Create',
            'deskripsi' => 'Kategori create',
        ]);

        $token = JWTAuth::fromUser($warga);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/laporans', [
                'judul_laporan' => 'Laporan Baru',
                'deskripsi' => 'Deskripsi laporan baru',
                'tingkat_keparahan' => 'Sedang',
                'latitude' => -6.2,
                'longitude' => 106.8,
                'id_kategori_bencana' => $kategori->id,
                'id_desa' => $wilayah['desa']->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'Draft');
    }

    public function test_laporan_store_accepts_status_menunggu_verifikasi(): void
    {
        $wilayah = $this->seedWilayahMinimal();
        $warga = $this->createUser('Warga', $wilayah['desa']->id);
        $kategori = KategoriBencana::create([
            'nama_kategori' => 'Longsor Create',
            'deskripsi' => 'Kategori create',
        ]);

        $token = JWTAuth::fromUser($warga);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/laporans', [
                'judul_laporan' => 'Laporan Menunggu Verifikasi',
                'deskripsi' => 'Deskripsi laporan',
                'tingkat_keparahan' => 'Tinggi',
                'status' => 'Menunggu Verifikasi',
                'latitude' => -6.21,
                'longitude' => 106.81,
                'id_kategori_bencana' => $kategori->id,
                'id_desa' => $wilayah['desa']->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'Menunggu Verifikasi');
    }

    public function test_laporan_store_rejects_invalid_status_value(): void
    {
        $wilayah = $this->seedWilayahMinimal();
        $warga = $this->createUser('Warga', $wilayah['desa']->id);
        $kategori = KategoriBencana::create([
            'nama_kategori' => 'Gempa Create',
            'deskripsi' => 'Kategori create',
        ]);

        $token = JWTAuth::fromUser($warga);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/laporans', [
                'judul_laporan' => 'Laporan Invalid Status',
                'deskripsi' => 'Deskripsi laporan',
                'tingkat_keparahan' => 'Rendah',
                'status' => 'Diproses',
                'latitude' => -6.22,
                'longitude' => 106.82,
                'id_kategori_bencana' => $kategori->id,
                'id_desa' => $wilayah['desa']->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validasi gagal');
    }
}
