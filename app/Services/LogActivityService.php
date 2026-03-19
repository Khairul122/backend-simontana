<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            DB::table('log_activity')->insert([
                'user_id' => $userId,
                'role' => $role,
                'aktivitas' => $aktivitas,
                'endpoint' => $endpoint,
                'ip_address' => $ipAddress ?? '-',
                'device_info' => $deviceInfo ?? '-',
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan log_activity', [
                'user_id' => $userId,
                'role' => $role,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
