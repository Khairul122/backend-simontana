<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Laporans;
use App\Services\LaporanStatusService;
use App\Services\LogActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LaporanWorkflowController extends Controller
{
    public function __construct(
        private readonly LogActivityService $logActivityService,
        private readonly LaporanStatusService $statusService,
    ) {}

    private function auditStatusChange(Request $request, Laporans $laporan, string $from, string $to): void
    {
        $user = $this->ensureAuthenticated($request);
        if (!$user) {
            return;
        }

        $this->logActivityService->log(
            $user->id,
            $user->role,
            sprintf('Perubahan status laporan #%d: %s -> %s', $laporan->id, $from, $to),
            '/api/laporans/' . $laporan->id . '/workflow',
            $request->ip(),
            $request->userAgent()
        );
    }

    public function verifikasi(Request $request, $id): JsonResponse
    {
        try {
            $user = $this->ensureAuthenticated($request);
            if (!$user) {
                return $this->unauthorized();
            }

            $laporan = Laporans::find($id);
            if (!$laporan) {
                return $this->notFoundResponse('Laporan tidak ditemukan');
            }

            if (!$user->hasRole(['Admin', 'PetugasBPBD', 'OperatorDesa'])) {
                return $this->forbidden('Tidak memiliki izin untuk memverifikasi laporan ini');
            }

            $validator = Validator::make($request->all(), [
                'status'              => 'required|in:Diverifikasi,Ditolak',
                'catatan_verifikasi'  => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $fromStatus = (string) $laporan->status;
            $toStatus   = (string) $request->status;

            if (!$this->statusService->canTransition($fromStatus, $toStatus)) {
                return $this->errorResponse(
                    sprintf('Transisi status tidak valid: %s -> %s', $fromStatus, $toStatus),
                    422,
                    code: 'INVALID_STATUS_TRANSITION'
                );
            }

            $updateData = [
                'status'             => $toStatus,
                'waktu_verifikasi'   => now(),
                'id_verifikator'     => $user->id,
                'catatan_verifikasi' => $request->catatan_verifikasi,
            ];

            if ($toStatus === 'Ditolak') {
                $updateData['waktu_selesai'] = null;
            }

            if (is_null($laporan->id_penanggung_jawab)) {
                $updateData['id_penanggung_jawab'] = $user->id;
            }

            $laporan->update($updateData);
            $this->auditStatusChange($request, $laporan, $fromStatus, $toStatus);

            return $this->successResponse('Laporan berhasil diverifikasi', $laporan->load(Laporans::FULL_RELATIONS));
        } catch (\Exception $e) {
            Log::error('Gagal memverifikasi laporan', [
                'laporan_id' => $id,
                'error'      => $e->getMessage(),
            ]);

            return $this->internalError('Gagal memverifikasi laporan');
        }
    }

    public function proses(Request $request, $id): JsonResponse
    {
        try {
            $user = $this->ensureAuthenticated($request);
            if (!$user) {
                return $this->unauthorized();
            }

            $laporan = Laporans::find($id);
            if (!$laporan) {
                return $this->notFoundResponse('Laporan tidak ditemukan');
            }

            if (!$user->hasRole(['Admin', 'PetugasBPBD', 'OperatorDesa'])) {
                return $this->forbidden('Tidak memiliki izin untuk memproses laporan ini');
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:Diproses,Selesai',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $fromStatus = (string) $laporan->status;
            $toStatus   = (string) $request->status;

            if (!$this->statusService->canTransition($fromStatus, $toStatus)) {
                return $this->errorResponse(
                    sprintf('Transisi status tidak valid: %s -> %s', $fromStatus, $toStatus),
                    422,
                    code: 'INVALID_STATUS_TRANSITION'
                );
            }

            $updateData = ['status' => $toStatus];

            if (is_null($laporan->id_penanggung_jawab)) {
                $updateData['id_penanggung_jawab'] = $user->id;
            }

            if ($toStatus === 'Selesai') {
                $updateData['waktu_selesai'] = now();
            }

            $laporan->update($updateData);
            $this->auditStatusChange($request, $laporan, $fromStatus, $toStatus);

            return $this->successResponse('Status laporan berhasil diperbarui', $laporan->load(Laporans::FULL_RELATIONS));
        } catch (\Exception $e) {
            Log::error('Gagal memproses laporan', [
                'laporan_id' => $id,
                'error'      => $e->getMessage(),
            ]);

            return $this->internalError('Gagal memproses laporan');
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
                ->with([
                    'petugas.desa.kecamatan.kabupaten.provinsi',
                    'tindakLanjut.petugas.desa.kecamatan.kabupaten.provinsi',
                    'tindakLanjut.laporan.pelapor.desa.kecamatan.kabupaten.provinsi',
                    'tindakLanjut.laporan.kategori',
                    'tindakLanjut.laporan.desa.kecamatan.kabupaten.provinsi',
                    'tindakLanjut.laporan.verifikator.desa.kecamatan.kabupaten.provinsi',
                    'tindakLanjut.laporan.penanggungJawab.desa.kecamatan.kabupaten.provinsi',
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse('Riwayat laporan berhasil diambil', $history);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil riwayat laporan', [
                'laporan_id' => $id,
                'error'      => $e->getMessage(),
            ]);

            return $this->internalError('Gagal mengambil riwayat laporan');
        }
    }
}
