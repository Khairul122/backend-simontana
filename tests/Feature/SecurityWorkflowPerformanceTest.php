<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\KategoriBencana;
use App\Models\Kecamatan;
use App\Models\Laporans;
use App\Models\Monitoring;
use App\Models\Pengguna;
use App\Models\Provinsi;
use App\Models\TindakLanjut;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class SecurityWorkflowPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function seedWilayahMinimal(): Desa
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

        return Desa::create([
            'nama' => 'Desa Uji',
            'id_kecamatan' => $kecamatan->id,
        ]);
    }

    private function createUser(string $role, ?int $idDesa = null): Pengguna
    {
        return Pengguna::create([
            'nama' => $role . ' Tester',
            'username' => strtolower($role) . '_tester_' . random_int(1000, 9999),
            'password' => 'password123',
            'role' => $role,
            'email' => strtolower($role) . '_tester_' . random_int(1000, 9999) . '@example.com',
            'id_desa' => $idDesa,
        ]);
    }

    private function makeLaporan(Pengguna $pelapor, int $desaId, string $status = 'Draft', ?KategoriBencana $kategori = null): Laporans
    {
        $kategori ??= KategoriBencana::create([
            'nama_kategori' => 'Banjir-' . random_int(1000, 9999),
            'deskripsi' => 'Kategori uji',
        ]);

        return Laporans::create([
            'id_pelapor' => $pelapor->id,
            'id_kategori_bencana' => $kategori->id,
            'id_desa' => $desaId,
            'judul_laporan' => 'Laporan Uji',
            'deskripsi' => 'Deskripsi laporan uji',
            'tingkat_keparahan' => 'Tinggi',
            'status' => $status,
            'latitude' => -6.20000000,
            'longitude' => 106.80000000,
            'waktu_laporan' => now(),
        ]);
    }

    public function test_login_endpoint_is_throttled_after_too_many_attempts(): void
    {
        Cache::flush();

        $user = $this->createUser('Warga');
        $ip = '10.10.10.10';
        $identifier = strtolower($user->username);

        RateLimiter::clear($ip . '|login');
        RateLimiter::clear($ip . '|' . $identifier);
        RateLimiter::clear('127.0.0.1|login');
        RateLimiter::clear('127.0.0.1|' . $identifier);
        RateLimiter::clear('::1|login');
        RateLimiter::clear('::1|' . $identifier);

        for ($attempt = 1; $attempt <= 6; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->postJson('/api/auth/login', [
                    'username' => $user->username,
                    'password' => 'wrong-password',
                ])
                ->assertStatus(401);
        }

        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson('/api/auth/login', [
                'username' => $user->username,
                'password' => 'wrong-password',
            ])
            ->assertStatus(429);
    }

    public function test_register_endpoint_is_throttled_after_too_many_attempts(): void
    {
        Cache::flush();

        $ip = '10.10.10.20';
        RateLimiter::clear($ip . '|register');
        RateLimiter::clear('127.0.0.1|register');
        RateLimiter::clear('::1|register');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->postJson('/api/auth/register', [
                    'nama' => 'Registrant ' . $attempt,
                    'username' => 'registrant_' . $attempt,
                    'email' => 'registrant_' . $attempt . '@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                    'role' => 'Warga',
                ])
                ->assertStatus(201);
        }

        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson('/api/auth/register', [
                'nama' => 'Registrant 6',
                'username' => 'registrant_6',
                'email' => 'registrant_6@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'Warga',
            ])
            ->assertStatus(429);
    }

    public function test_monitoring_update_is_denied_for_non_owner_operator(): void
    {
        $desa = $this->seedWilayahMinimal();
        $pelapor = $this->createUser('Warga', $desa->id);
        $operatorA = $this->createUser('OperatorDesa', $desa->id);
        $operatorB = $this->createUser('OperatorDesa', $desa->id);
        $laporan = $this->makeLaporan($pelapor, $desa->id, 'Diproses');

        $monitoring = Monitoring::create([
            'id_laporan' => $laporan->id,
            'id_operator' => $operatorA->id,
            'waktu_monitoring' => now(),
            'hasil_monitoring' => 'Monitoring awal',
            'koordinat_gps' => '-6.2,106.8',
        ]);

        $token = JWTAuth::fromUser($operatorB);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/monitoring/' . $monitoring->id_monitoring, [
                'hasil_monitoring' => 'Diubah oleh operator lain',
            ])
            ->assertStatus(403)
            ->assertJsonPath('code', 'INSUFFICIENT_PERMISSIONS');
    }

    public function test_tindak_lanjut_create_denies_operator_spoofing_other_petugas(): void
    {
        $desa = $this->seedWilayahMinimal();
        $pelapor = $this->createUser('Warga', $desa->id);
        $operatorA = $this->createUser('OperatorDesa', $desa->id);
        $operatorB = $this->createUser('OperatorDesa', $desa->id);
        $laporan = $this->makeLaporan($pelapor, $desa->id, 'Diverifikasi');

        $token = JWTAuth::fromUser($operatorA);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/tindak-lanjut', [
                'laporan_id' => $laporan->id,
                'id_petugas' => $operatorB->id,
                'tanggal_tanggapan' => now()->toDateTimeString(),
                'status' => 'Menuju Lokasi',
            ])
            ->assertStatus(403)
            ->assertJsonPath('code', 'INSUFFICIENT_PERMISSIONS');
    }

    public function test_tindak_lanjut_index_allows_warga_and_scopes_to_own_laporan(): void
    {
        $desa = $this->seedWilayahMinimal();
        $wargaA = $this->createUser('Warga', $desa->id);
        $wargaB = $this->createUser('Warga', $desa->id);
        $operator = $this->createUser('OperatorDesa', $desa->id);

        $laporanA = $this->makeLaporan($wargaA, $desa->id, 'Diverifikasi');
        $laporanB = $this->makeLaporan($wargaB, $desa->id, 'Diverifikasi');

        $tlA = TindakLanjut::create([
            'laporan_id' => $laporanA->id,
            'id_petugas' => $operator->id,
            'tanggal_tanggapan' => now(),
            'status' => 'Menuju Lokasi',
        ]);

        $tlB = TindakLanjut::create([
            'laporan_id' => $laporanB->id,
            'id_petugas' => $operator->id,
            'tanggal_tanggapan' => now(),
            'status' => 'Selesai',
        ]);

        $token = JWTAuth::fromUser($wargaA);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tindak-lanjut');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $data = collect($response->json('data'));
        $ids = $data->pluck('id_tindaklanjut')->all();

        $this->assertContains($tlA->id_tindaklanjut, $ids);
        $this->assertNotContains($tlB->id_tindaklanjut, $ids);
    }

    public function test_tindak_lanjut_show_allows_warga_for_own_laporan_only(): void
    {
        $desa = $this->seedWilayahMinimal();
        $wargaA = $this->createUser('Warga', $desa->id);
        $wargaB = $this->createUser('Warga', $desa->id);
        $operator = $this->createUser('OperatorDesa', $desa->id);

        $laporanA = $this->makeLaporan($wargaA, $desa->id, 'Diverifikasi');
        $laporanB = $this->makeLaporan($wargaB, $desa->id, 'Diverifikasi');

        $tlA = TindakLanjut::create([
            'laporan_id' => $laporanA->id,
            'id_petugas' => $operator->id,
            'tanggal_tanggapan' => now(),
            'status' => 'Menuju Lokasi',
        ]);

        $tlB = TindakLanjut::create([
            'laporan_id' => $laporanB->id,
            'id_petugas' => $operator->id,
            'tanggal_tanggapan' => now(),
            'status' => 'Selesai',
        ]);

        $token = JWTAuth::fromUser($wargaA);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tindak-lanjut/' . $tlA->id_tindaklanjut)
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id_tindaklanjut', $tlA->id_tindaklanjut);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tindak-lanjut/' . $tlB->id_tindaklanjut)
            ->assertStatus(403)
            ->assertJsonPath('code', 'INSUFFICIENT_PERMISSIONS');
    }

    public function test_riwayat_index_allows_warga_and_scopes_to_own_laporan(): void
    {
        $desa = $this->seedWilayahMinimal();
        $wargaA = $this->createUser('Warga', $desa->id);
        $wargaB = $this->createUser('Warga', $desa->id);
        $operator = $this->createUser('OperatorDesa', $desa->id);

        $laporanA = $this->makeLaporan($wargaA, $desa->id, 'Diverifikasi');
        $laporanB = $this->makeLaporan($wargaB, $desa->id, 'Diverifikasi');

        $tlA = TindakLanjut::create([
            'laporan_id' => $laporanA->id,
            'id_petugas' => $operator->id,
            'tanggal_tanggapan' => now(),
            'status' => 'Menuju Lokasi',
        ]);

        $tlB = TindakLanjut::create([
            'laporan_id' => $laporanB->id,
            'id_petugas' => $operator->id,
            'tanggal_tanggapan' => now(),
            'status' => 'Selesai',
        ]);

        $riwayatA = \App\Models\RiwayatTindakan::create([
            'tindaklanjut_id' => $tlA->id_tindaklanjut,
            'id_petugas' => $operator->id,
            'keterangan' => 'Aksi untuk laporan A',
            'waktu_tindakan' => now(),
        ]);

        $riwayatB = \App\Models\RiwayatTindakan::create([
            'tindaklanjut_id' => $tlB->id_tindaklanjut,
            'id_petugas' => $operator->id,
            'keterangan' => 'Aksi untuk laporan B',
            'waktu_tindakan' => now(),
        ]);

        $token = JWTAuth::fromUser($wargaA);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/riwayat-tindakan');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $data = collect($response->json('data'));
        $ids = $data->pluck('id')->all();

        $this->assertContains($riwayatA->id, $ids);
        $this->assertNotContains($riwayatB->id, $ids);
    }

    public function test_riwayat_show_allows_warga_for_own_laporan_only(): void
    {
        $desa = $this->seedWilayahMinimal();
        $wargaA = $this->createUser('Warga', $desa->id);
        $wargaB = $this->createUser('Warga', $desa->id);
        $operator = $this->createUser('OperatorDesa', $desa->id);

        $laporanA = $this->makeLaporan($wargaA, $desa->id, 'Diverifikasi');
        $laporanB = $this->makeLaporan($wargaB, $desa->id, 'Diverifikasi');

        $tlA = TindakLanjut::create([
            'laporan_id' => $laporanA->id,
            'id_petugas' => $operator->id,
            'tanggal_tanggapan' => now(),
            'status' => 'Menuju Lokasi',
        ]);

        $tlB = TindakLanjut::create([
            'laporan_id' => $laporanB->id,
            'id_petugas' => $operator->id,
            'tanggal_tanggapan' => now(),
            'status' => 'Selesai',
        ]);

        $riwayatA = \App\Models\RiwayatTindakan::create([
            'tindaklanjut_id' => $tlA->id_tindaklanjut,
            'id_petugas' => $operator->id,
            'keterangan' => 'Aksi untuk laporan A',
            'waktu_tindakan' => now(),
        ]);

        $riwayatB = \App\Models\RiwayatTindakan::create([
            'tindaklanjut_id' => $tlB->id_tindaklanjut,
            'id_petugas' => $operator->id,
            'keterangan' => 'Aksi untuk laporan B',
            'waktu_tindakan' => now(),
        ]);

        $token = JWTAuth::fromUser($wargaA);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/riwayat-tindakan/' . $riwayatA->id)
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $riwayatA->id);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/riwayat-tindakan/' . $riwayatB->id)
            ->assertStatus(403)
            ->assertJsonPath('code', 'INSUFFICIENT_PERMISSIONS');
    }

    public function test_warga_detail_lengkap_route_returns_laporan_tindak_lanjut_and_riwayat(): void
    {
        $desa = $this->seedWilayahMinimal();
        $warga = $this->createUser('Warga', $desa->id);
        $operator = $this->createUser('OperatorDesa', $desa->id);
        $laporan = $this->makeLaporan($warga, $desa->id, 'Diverifikasi');

        $tindakLanjut = TindakLanjut::create([
            'laporan_id' => $laporan->id,
            'id_petugas' => $operator->id,
            'tanggal_tanggapan' => now(),
            'status' => 'Menuju Lokasi',
        ]);

        $riwayat = \App\Models\RiwayatTindakan::create([
            'tindaklanjut_id' => $tindakLanjut->id_tindaklanjut,
            'id_petugas' => $operator->id,
            'keterangan' => 'Evakuasi awal',
            'waktu_tindakan' => now(),
        ]);

        $token = JWTAuth::fromUser($warga);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/warga/laporans/' . $laporan->id . '/detail-lengkap')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.detail_laporan.id', $laporan->id)
            ->assertJsonPath('data.tindak_lanjut.0.id_tindaklanjut', $tindakLanjut->id_tindaklanjut)
            ->assertJsonPath('data.riwayat_tindakan.0.id', $riwayat->id);
    }

    public function test_warga_detail_lengkap_route_rejects_non_warga_role(): void
    {
        $desa = $this->seedWilayahMinimal();
        $admin = $this->createUser('Admin', $desa->id);
        $warga = $this->createUser('Warga', $desa->id);
        $laporan = $this->makeLaporan($warga, $desa->id, 'Draft');

        $token = JWTAuth::fromUser($admin);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/warga/laporans/' . $laporan->id . '/detail-lengkap')
            ->assertStatus(403)
            ->assertJsonPath('code', 'INSUFFICIENT_PERMISSIONS');
    }

    public function test_riwayat_create_denies_non_assigned_petugas(): void
    {
        $desa = $this->seedWilayahMinimal();
        $pelapor = $this->createUser('Warga', $desa->id);
        $operatorA = $this->createUser('OperatorDesa', $desa->id);
        $operatorB = $this->createUser('OperatorDesa', $desa->id);
        $laporan = $this->makeLaporan($pelapor, $desa->id, 'Diverifikasi');

        $tindakLanjut = TindakLanjut::create([
            'laporan_id' => $laporan->id,
            'id_petugas' => $operatorA->id,
            'tanggal_tanggapan' => now(),
            'status' => 'Menuju Lokasi',
        ]);

        $token = JWTAuth::fromUser($operatorB);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/riwayat-tindakan', [
                'tindaklanjut_id' => $tindakLanjut->id_tindaklanjut,
                'id_petugas' => $operatorB->id,
                'keterangan' => 'Coba update riwayat',
                'waktu_tindakan' => now()->toDateTimeString(),
            ])
            ->assertStatus(403)
            ->assertJsonPath('code', 'INSUFFICIENT_PERMISSIONS');
    }

    public function test_workflow_rejects_invalid_transition_from_draft_to_selesai(): void
    {
        $desa = $this->seedWilayahMinimal();
        $pelapor = $this->createUser('Warga', $desa->id);
        $admin = $this->createUser('Admin', $desa->id);
        $laporan = $this->makeLaporan($pelapor, $desa->id, 'Draft');

        $token = JWTAuth::fromUser($admin);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/laporans/' . $laporan->id . '/proses', [
                'status' => 'Selesai',
            ])
            ->assertStatus(422)
            ->assertJsonPath('code', 'INVALID_STATUS_TRANSITION');
    }

    public function test_workflow_valid_transition_sequence_logs_audit_trail(): void
    {
        $desa = $this->seedWilayahMinimal();
        $pelapor = $this->createUser('Warga', $desa->id);
        $admin = $this->createUser('Admin', $desa->id);
        $laporan = $this->makeLaporan($pelapor, $desa->id, 'Draft');

        $token = JWTAuth::fromUser($admin);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/laporans/' . $laporan->id . '/verifikasi', [
                'status' => 'Diverifikasi',
                'catatan_verifikasi' => 'Laporan valid',
            ])
            ->assertStatus(200);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/laporans/' . $laporan->id . '/proses', [
                'status' => 'Diproses',
            ])
            ->assertStatus(200);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/laporans/' . $laporan->id . '/proses', [
                'status' => 'Selesai',
            ])
            ->assertStatus(200);

        $laporan->refresh();

        $this->assertSame('Selesai', $laporan->status);
        $this->assertNotNull($laporan->waktu_selesai);

        $this->assertDatabaseHas('log_activity', [
            'user_id' => $admin->id,
            'aktivitas' => 'Perubahan status laporan #' . $laporan->id . ': Draft -> Diverifikasi',
        ]);
    }

    public function test_laporan_location_radius_is_capped_to_100km_guardrail(): void
    {
        $desa = $this->seedWilayahMinimal();
        $pelapor = $this->createUser('Warga', $desa->id);
        $admin = $this->createUser('Admin', $desa->id);
        $kategori = KategoriBencana::create([
            'nama_kategori' => 'Gempa-' . random_int(1000, 9999),
            'deskripsi' => 'Kategori gempa',
        ]);

        $nearby = $this->makeLaporan($pelapor, $desa->id, 'Draft', $kategori);

        Laporans::create([
            'id_pelapor' => $pelapor->id,
            'id_kategori_bencana' => $kategori->id,
            'id_desa' => $desa->id,
            'judul_laporan' => 'Laporan Jauh',
            'deskripsi' => 'Lokasi jauh',
            'tingkat_keparahan' => 'Sedang',
            'status' => 'Draft',
            'latitude' => -7.25044500,
            'longitude' => 112.76884500,
            'waktu_laporan' => now(),
        ]);

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/laporans?lat=-6.2&lng=106.8&radius=1000&limit=20');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame($nearby->id, $data[0]['id']);
    }
}
