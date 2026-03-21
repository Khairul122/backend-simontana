<?php

namespace App\Services;

use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk mencatat aktivitas pengguna.
 * Logging dilakukan secara ASYNC via queue job agar tidak
 * memperlambat response API (fire-and-forget pattern).
 */
class LogActivityService
{
    public function log(
        ?int $userId,
        ?string $role,
        string $aktivitas,
        string $endpoint,
        ?string $ipAddress,
        ?string $deviceInfo
    ): void {
        if (!$userId || !$role) {
            return;
        }

        try {
            LogActivityJob::dispatch(
                $userId,
                $role,
                $aktivitas,
                $endpoint,
                $ipAddress ?? '-',
                $deviceInfo ?? '-',
            );
        } catch (\Throwable $e) {
            // Jika queue tidak tersedia, fallback ke log file
            Log::error('Gagal mendispatch LogActivityJob', [
                'user_id'   => $userId,
                'role'      => $role,
                'endpoint'  => $endpoint,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
