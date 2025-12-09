<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatTindakan extends Model
{
    protected $table = 'riwayat_tindakan';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tindaklanjut_id',
        'petugas',
        'keterangan',
        'waktu_tindakan'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_riwayat' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the laporan that owns the riwayat_tindakan.
     */
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'laporan_id', 'id');
    }

    /**
     * Get the pengguna that owns the riwayat_tindakan.
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'user_id', 'id');
    }

    /**
     * Alias for pengguna() method
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'user_id', 'id');
    }

    /**
     * Scope a query to only include riwayat with specific type.
     */
    public function scopeWithJenisRiwayat($query, $jenisRiwayat)
    {
        return $query->where('jenis_riwayat', $jenisRiwayat);
    }

    /**
     * Scope a query to only include riwayat within date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_riwayat', [$startDate, $endDate]);
    }

    /**
     * Scope a query to search riwayat by description.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('deskripsi_riwayat', 'LIKE', "%{$search}%");
    }

    /**
     * Get the human readable action type.
     */
    public function getJenisRiwayatTextAttribute()
    {
        $jenisRiwayat = [
            'pembuatan_laporan' => 'Pembuatan Laporan',
            'verifikasi_laporan' => 'Verifikasi Laporan',
            'penugasan_petugas' => 'Penugasan Petugas',
            'mulai_penanganan' => 'Mulai Penanganan',
            'update_progress' => 'Update Progress',
            'penyelesaian' => 'Penyelesaian',
            'pembatalan' => 'Pembatalan',
            'perubahan_status' => 'Perubahan Status',
            'dokumentasi' => 'Dokumentasi',
            'lainnya' => 'Lainnya'
        ];

        return $jenisRiwayat[$this->jenis_riwayat] ?? $this->jenis_riwayat;
    }

    /**
     * Get the human readable status before.
     */
    public function getStatusSebelumnyaTextAttribute()
    {
        $statuses = [
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'sudah_diverifikasi' => 'Sudah Diverifikasi',
            'dalam_penanganan' => 'Dalam Penanganan',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak'
        ];

        return $statuses[$this->status_sebelumnya] ?? $this->status_sebelumnya;
    }

    /**
     * Get the human readable status after.
     */
    public function getStatusSesudahnyaTextAttribute()
    {
        $statuses = [
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'sudah_diverifikasi' => 'Sudah Diverifikasi',
            'dalam_penanganan' => 'Dalam Penanganan',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak'
        ];

        return $statuses[$this->status_sesudahnya] ?? $this->status_sesudahnya;
    }

    /**
     * Check if this is a status change riwayat.
     */
    public function isStatusChange(): bool
    {
        return $this->jenis_riwayat === 'perubahan_status' ||
               ($this->status_sebelumnya && $this->status_sesudahnya && $this->status_sebelumnya !== $this->status_sesudahnya);
    }

    /**
     * Check if this is a completion riwayat.
     */
    public function isCompletion(): bool
    {
        return $this->jenis_riwayat === 'penyelesaian' || $this->status_sesudahnya === 'selesai';
    }

    /**
     * Check if this is a cancellation riwayat.
     */
    public function isCancellation(): bool
    {
        return $this->jenis_riwayat === 'pembatalan' || $this->status_sesudahnya === 'ditolak';
    }

    /**
     * Get formatted date for display.
     */
    public function getFormattedDateAttribute()
    {
        return $this->tanggal_riwayat->format('d M Y H:i');
    }

    /**
     * Get time elapsed since this riwayat.
     */
    public function getTimeElapsedAttribute()
    {
        return $this->tanggal_riwayat->diffForHumans();
    }
}
