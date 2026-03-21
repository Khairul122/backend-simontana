<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job untuk mencatat aktivitas pengguna secara asinkron melalui queue.
 * Memindahkan DB insert dari request lifecycle ke background worker
 * sehingga response API tidak tertunda karena operasi logging.
 */
class LogActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        private readonly int $userId,
        private readonly string $role,
        private readonly string $aktivitas,
        private readonly string $endpoint,
        private readonly string $ipAddress,
        private readonly string $deviceInfo,
    ) {}

    public function handle(): void
    {
        DB::table('log_activity')->insert([
            'user_id'    => $this->userId,
            'role'       => $this->role,
            'aktivitas'  => $this->aktivitas,
            'endpoint'   => $this->endpoint,
            'ip_address' => $this->ipAddress,
            'device_info' => $this->deviceInfo,
            'created_at' => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('LogActivityJob gagal', [
            'user_id'   => $this->userId,
            'aktivitas' => $this->aktivitas,
            'error'     => $exception->getMessage(),
        ]);
    }
}
