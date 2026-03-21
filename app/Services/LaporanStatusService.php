<?php

namespace App\Services;

/**
 * Service untuk mengelola state machine status laporan bencana.
 * Sentralisasi logika transisi status agar dapat digunakan oleh
 * berbagai controller tanpa duplikasi kode.
 */
class LaporanStatusService
{
    /**
     * Peta transisi status yang valid.
     * Key: status asal, Value: array status tujuan yang diizinkan.
     */
    private const TRANSITION_MAP = [
        'Draft'                => ['Diverifikasi', 'Ditolak'],
        'Menunggu Verifikasi'  => ['Diverifikasi', 'Ditolak'],
        'Diverifikasi'         => ['Diproses', 'Selesai'],
        'Diproses'             => ['Selesai'],
        'Selesai'              => [],
        'Ditolak'              => [],
    ];

    /**
     * Semua status yang valid dalam sistem.
     */
    public const ALL_STATUSES = [
        'Draft',
        'Menunggu Verifikasi',
        'Diverifikasi',
        'Diproses',
        'Selesai',
        'Ditolak',
    ];

    /**
     * Cek apakah transisi dari status $from ke $to diizinkan.
     */
    public function canTransition(string $from, string $to): bool
    {
        $allowed = self::TRANSITION_MAP[$from] ?? [];
        return in_array($to, $allowed, true);
    }

    /**
     * Kembalikan semua status tujuan yang diizinkan dari status $from.
     */
    public function getAllowedTransitions(string $from): array
    {
        return self::TRANSITION_MAP[$from] ?? [];
    }

    /**
     * Cek apakah suatu status valid dalam sistem.
     */
    public function isValidStatus(string $status): bool
    {
        return in_array($status, self::ALL_STATUSES, true);
    }
}
