<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Laporan extends Model
{
    protected $table = 'laporan';
    protected $primaryKey = 'id_laporan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_warga',
        'pengirim',
        'tanggal_lapor',
        'id_kategori',
        'lokasi',
        'deskripsi',
        'foto',
        'status_laporan'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_lapor' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the pengguna that owns the laporan.
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_warga', 'id');
    }

    /**
     * Alias for pengguna() method
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_warga', 'id');
    }

    /**
     * Get the desa that owns the laporan.
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id', 'id_desa');
    }

    /**
     * Get the riwayat_tindakan for the laporan.
     */
    public function riwayatTindakan(): HasMany
    {
        return $this->hasMany(RiwayatTindakan::class, 'laporan_id', 'id');
    }

    /**
     * Scope a query to only include laporan with specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status_laporan', $status);
    }

    /**
     * Scope a query to only include laporan with specific category.
     */
    public function scopeWithCategory($query, $categoryId)
    {
        return $query->where('id_kategori', $categoryId);
    }

    /**
     * Scope a query to only include laporan within date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_lapor', [$startDate, $endDate]);
    }

    /**
     * Scope a query to search laporan by description or location.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('deskripsi', 'LIKE', "%{$search}%")
              ->orWhere('lokasi', 'LIKE', "%{$search}%")
              ->orWhere('pengirim', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Get the human readable status.
     */
    public function getStatusTextAttribute()
    {
        $statuses = [
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'sudah_diverifikasi' => 'Sudah Diverifikasi',
            'dalam_penanganan' => 'Dalam Penanganan',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak'
        ];

        return $statuses[$this->status_laporan] ?? $this->status_laporan;
    }

    /**
     * Check if laporan can be responded to.
     */
    public function canBeResponded(): bool
    {
        return in_array($this->status_laporan, ['sudah_diverifikasi', 'dalam_penanganan']);
    }

    /**
     * Check if laporan is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status_laporan === 'selesai';
    }

    /**
     * Check if laporan needs verification.
     */
    public function needsVerification(): bool
    {
        return $this->status_laporan === 'menunggu_verifikasi';
    }

    /**
     * Get latest monitoring record.
     */
    public function latestMonitoring()
    {
        return $this->monitoring()->latest()->first();
    }

    /**
     * Count total tindaklanjut for this laporan.
     */
    public function getTotalTindaklanjutAttribute()
    {
        return $this->tindaklanjut()->count();
    }
}