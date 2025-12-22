<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporans extends Model
{
    use HasFactory;

    protected $table = 'laporans';

    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'tanggal_kejadian' => 'date',
        'waktu_kejadian' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Get the pelapor that owns the laporan.
     */
    public function pelapor()
    {
        return $this->belongsTo(Pengguna::class, 'id_pelapor');
    }

    /**
     * Get the kategori that owns the laporan.
     */
    public function kategori()
    {
        return $this->belongsTo(KategoriBencana::class, 'id_kategori_bencana');
    }

    /**
     * Get the desa that owns the laporan.
     */
    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }

    /**
     * Get the tindakLanjut for the laporan.
     */
    public function tindakLanjut()
    {
        return $this->hasMany(TindakLanjut::class, 'laporan_id');
    }

    /**
     * Get the monitoring for the laporan.
     */
    public function monitoring()
    {
        return $this->hasMany(Monitoring::class, 'id_laporan');
    }

    /**
     * Get the riwayatTindakan for the laporan.
     */
    public function riwayatTindakan()
    {
        return $this->hasMany(RiwayatTindakan::class, 'id_laporan');
    }

    /**
     * Scope a query to only include laporans with a given status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include laporans within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include laporans from the last N days.
     */
    public function scopeLastDays($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get the alamat lengkap attribute.
     */
    public function getAlamatLengkapAttribute()
    {
        $alamat = [];

        if ($this->alamat) {
            $alamat[] = $this->alamat;
        }

        if ($this->desa) {
            $alamat[] = $this->desa->nama ?? '';
        }

        if ($this->desa && $this->desa->kecamatan) {
            $alamat[] = $this->desa->kecamatan->nama ?? '';
        }

        if ($this->desa && $this->desa->kecamatan && $this->desa->kecamatan->kabupaten) {
            $alamat[] = $this->desa->kecamatan->kabupaten->nama ?? '';
        }

        if ($this->desa && $this->desa->kecamatan && $this->desa->kecamatan->kabupaten && $this->desa->kecamatan->kabupaten->provinsi) {
            $alamat[] = $this->desa->kecamatan->kabupaten->provinsi->nama ?? '';
        }

        return implode(', ', array_filter($alamat));
    }

    /**
     * Check if laporan needs verification.
     */
    public function needsVerification()
    {
        return in_array($this->status, ['Draft', 'Menunggu Verifikasi']);
    }

    /**
     * Check if laporan is being processed.
     */
    public function isBeingProcessed()
    {
        return in_array($this->status, ['Diverifikasi', 'Diproses']);
    }

    /**
     * Check if laporan is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'Selesai';
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'Draft' => 'secondary',
            'Menunggu Verifikasi' => 'warning',
            'Diverifikasi' => 'info',
            'Diproses' => 'primary',
            'Selesai' => 'success',
            'Ditolak' => 'danger',
            default => 'secondary',
        };
    }
}