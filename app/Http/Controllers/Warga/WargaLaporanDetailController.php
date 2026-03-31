<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Models\Laporans;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WargaLaporanDetailController extends Controller
{
    private const DETAIL_RELATIONS = [
        'pelapor.desa.kecamatan.kabupaten.provinsi',
        'kategori',
        'desa.kecamatan.kabupaten.provinsi',
        'verifikator.desa.kecamatan.kabupaten.provinsi',
        'penanggungJawab.desa.kecamatan.kabupaten.provinsi',
        'tindakLanjut.petugas.desa.kecamatan.kabupaten.provinsi',
        'tindakLanjut.riwayatTindakans.petugas.desa.kecamatan.kabupaten.provinsi',
    ];

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $user = $this->ensureAuthenticated($request);
            if (!$user) {
                return $this->unauthorized();
            }

            if ($user->role !== 'Warga') {
                return $this->forbidden('Endpoint ini khusus role Warga');
            }

            $laporan = Laporans::with(self::DETAIL_RELATIONS)->find($id);
            if (!$laporan) {
                return $this->notFoundResponse('Laporan tidak ditemukan');
            }

            if ((int) $laporan->id_pelapor !== (int) $user->id) {
                return $this->deniedByPolicy('Warga hanya dapat melihat detail laporan miliknya sendiri');
            }

            $laporan->incrementViewCount();
            $laporan->refresh()->load(self::DETAIL_RELATIONS);

            $tindakLanjut = $laporan->tindakLanjut
                ->sortByDesc('tanggal_tanggapan')
                ->values();

            $riwayatTindakan = $tindakLanjut
                ->flatMap(fn($item) => $item->riwayatTindakans)
                ->sortByDesc('waktu_tindakan')
                ->values();

            return $this->successResponse('Detail laporan warga berhasil diambil', [
                'detail_laporan' => $laporan,
                'tindak_lanjut' => $tindakLanjut,
                'riwayat_tindakan' => $riwayatTindakan,
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal mengambil detail lengkap laporan warga', [
                'laporan_id' => $id,
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->internalError('Gagal mengambil detail lengkap laporan warga');
        }
    }
}
