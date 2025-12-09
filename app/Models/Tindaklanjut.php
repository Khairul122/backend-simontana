<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tindaklanjut extends Model
{
    protected $table = 'tindaklanjut';
    protected $primaryKey = 'id_tindaklanjut';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_laporan',
        'id_petugas',
        'tanggal_tanggapan',
        'jenis_tindakan',
        'deskripsi_tindakan',
        'status_tindakan',
        'prioritas',
        'estimasi_waktu',
        'dokumentasi'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_tanggapan' => 'datetime',
        'estimasi_waktu' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the laporan that owns the tindaklanjut.
     */
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'id_laporan', 'id_laporan');
    }

    /**
     * Get the petugas (pengguna) that owns the tindaklanjut.
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_petugas', 'id');
    }

    /**
     * Alias for petugas() method
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_petugas', 'id');
    }

    /**
     * Get the riwayat_tindakan for the tindaklanjut.
     */
    public function riwayatTindakan(): HasMany
    {
        return $this->hasMany(RiwayatTindakan::class, 'id_tindaklanjut', 'id_tindaklanjut');
    }

    /**
     * Scope a query to only include tindaklanjut with specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status_tindakan', $status);
    }

    /**
     * Scope a query to only include tindaklanjut with specific priority.
     */
    public function scopeWithPriority($query, $priority)
    {
        return $query->where('prioritas', $priority);
    }

    /**
     * Scope a query to only include tindaklanjut within date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_tanggapan', [$startDate, $endDate]);
    }

    /**
     * Scope a query to search tindaklanjut by description or action type.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('deskripsi_tindakan', 'LIKE', "%{$search}%")
              ->orWhere('jenis_tindakan', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Get the human readable status.
     */
    public function getStatusTextAttribute()
    {
        $statuses = [
            'menunggu_penugasan' => 'Menunggu Penugasan',
            'sedang_diproses' => 'Sedang Diproses',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan'
        ];

        return $statuses[$this->status_tindakan] ?? $this->status_tindakan;
    }

    /**
     * Get the human readable priority.
     */
    public function getPriorityTextAttribute()
    {
        $priorities = [
            'rendah' => 'Rendah',
            'sedang' => 'Sedang',
            'tinggi' => 'Tinggi',
            'darurat' => 'Darurat'
        ];

        return $priorities[$this->prioritas] ?? $this->prioritas;
    }

    /**
     * Get the human readable action type.
     */
    public function getActionTypeTextAttribute()
    {
        $actions = [
            'evakuasi' => 'Evakuasi',
            'penanganan_medis' => 'Penanganan Medis',
            'distribusi_bantuan' => 'Distribusi Bantuan',
            'perbaikan_infrastruktur' => 'Perbaikan Infrastruktur',
            'pembersihan' => 'Pembersihan',
            'lainnya' => 'Lainnya'
        ];

        return $actions[$this->jenis_tindakan] ?? $this->jenis_tindakan;
    }

    /**
     * Check if tindaklanjut is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status_tindakan === 'selesai';
    }

    /**
     * Check if tindaklanjut is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status_tindakan === 'dibatalkan';
    }

    /**
     * Check if tindaklanjut needs assignment.
     */
    public function needsAssignment(): bool
    {
        return $this->status_tindakan === 'menunggu_penugasan';
    }

    /**
     * Check if tindaklanjut is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status_tindakan === 'sedang_diproses';
    }

    /**
     * Get latest riwayat tindakan.
     */
    public function latestRiwayat()
    {
        return $this->riwayatTindakan()->latest()->first();
    }

    /**
     * Count total riwayat tindakan for this tindaklanjut.
     */
    public function getTotalRiwayatAttribute()
    {
        return $this->riwayatTindakan()->count();
    }

    /**
     * Check if the action is overdue.
     */
    public function isOverdue(): bool
    {
        if (!$this->estimasi_waktu || $this->isCompleted() || $this->isCancelled()) {
            return false;
        }
        return now()->isAfter($this->estimasi_waktu);
    }
}