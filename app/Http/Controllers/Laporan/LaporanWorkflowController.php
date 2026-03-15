<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Laporans;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LaporanWorkflowController extends Controller
{
    public function verifikasi(Request $request, $id): JsonResponse
    {
        try {
            $laporan = Laporans::find($id);
            if (!$laporan) {
                return $this->notFoundResponse('Laporan tidak ditemukan');
            }

            if (!auth()->user()->hasRole(['Admin', 'PetugasBPBD', 'OperatorDesa'])) {
                return $this->forbidden('Tidak memiliki izin untuk memverifikasi laporan ini');
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:Diverifikasi,Ditolak',
                'catatan_verifikasi' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $updateData = [
                'status' => $request->status,
                'waktu_verifikasi' => now(),
                'id_verifikator' => auth()->id(),
                'catatan_verifikasi' => $request->catatan_verifikasi,
            ];

            if (is_null($laporan->id_penanggung_jawab)) {
                $updateData['id_penanggung_jawab'] = auth()->id();
            }

            $laporan->update($updateData);

            return $this->successResponse('Laporan berhasil diverifikasi', $laporan->load([
                    'pelapor:id,nama,email,alamat,no_telepon',
                    'kategori:id,nama_kategori,deskripsi',
                    'desa:id,nama,id_kecamatan',
                    'desa.kecamatan:id,nama,id_kabupaten',
                    'desa.kecamatan.kabupaten:id,nama,id_provinsi',
                    'desa.kecamatan.kabupaten.provinsi:id,nama',
                ]));
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal memverifikasi laporan: ' . $e->getMessage(), 500, ['data' => null]);
        }
    }

    public function proses(Request $request, $id): JsonResponse
    {
        try {
            $laporan = Laporans::find($id);
            if (!$laporan) {
                return $this->notFoundResponse('Laporan tidak ditemukan');
            }

            if (!auth()->user()->hasRole(['Admin', 'PetugasBPBD', 'OperatorDesa'])) {
                return $this->forbidden('Tidak memiliki izin untuk memproses laporan ini');
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:Diproses,Tindak Lanjut,Selesai',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $updateData = [
                'status' => $request->status,
            ];

            if (is_null($laporan->id_penanggung_jawab)) {
                $updateData['id_penanggung_jawab'] = auth()->id();
            }

            $laporan->update($updateData);

            if ($request->status === 'Selesai') {
                $laporan->update(['waktu_selesai' => now()]);
            }

            return $this->successResponse('Status laporan berhasil diperbarui', $laporan->load([
                    'pelapor:id,nama,email,alamat,no_telepon',
                    'kategori:id,nama_kategori,deskripsi',
                    'desa:id,nama,id_kecamatan',
                    'desa.kecamatan:id,nama,id_kabupaten',
                    'desa.kecamatan.kabupaten:id,nama,id_provinsi',
                    'desa.kecamatan.kabupaten.provinsi:id,nama',
                ]));
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal memproses laporan: ' . $e->getMessage(), 500, ['data' => null]);
        }
    }

    public function riwayat($id): JsonResponse
    {
        try {
            $laporan = Laporans::find($id);
            if (!$laporan) {
                return $this->notFoundResponse('Laporan tidak ditemukan');
            }

            $history = $laporan->riwayatTindakan()
                ->with('user:id,nama')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse('Riwayat laporan berhasil diambil', $history);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil riwayat laporan: ' . $e->getMessage(), 500, ['data' => null]);
        }
    }
}
