<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laporans extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = 'laporans';

    /**
     * Mass assignment - guarded approach for security
     */
    protected $guarded = ['id'];

    /**
     * Hidden fields from API responses
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Appended attributes to model's JSON form
     */
    protected $appends = [
        'foto_bukti_1_url',
        'foto_bukti_2_url',
        'foto_bukti_3_url',
        'video_bukti_url',
        'administrative_area'
    ];

    /**
     * Strict type casting for data consistency and JSON serialization
     */
    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
        'waktu_laporan' => 'datetime',
        'waktu_verifikasi' => 'datetime',
        'waktu_selesai' => 'datetime',
        'data_tambahan' => 'array',
        'is_prioritas' => 'boolean',
        'view_count' => 'integer',
        'jumlah_korban' => 'integer',
        'jumlah_rumah_rusak' => 'integer',
    ];

    /**
     * Get the pelapor (user who reported) that owns the laporan.
     */
    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pelapor');
    }

    /**
     * Get the kategori bencana that owns the laporan.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBencana::class, 'id_kategori_bencana');
    }

    /**
     * Get the desa that owns the laporan.
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }

    /**
     * Get the tindak lanjut records for the laporan.
     */
    public function tindakLanjut(): HasMany
    {
        return $this->hasMany(TindakLanjut::class, 'laporan_id');
    }

    /**
     * Get the monitoring records for the laporan.
     */
    public function monitoring(): HasMany
    {
        return $this->hasMany(Monitoring::class, 'id_laporan');
    }

    /**
     * Get the riwayat tindakan records for the laporan.
     */
    public function riwayatTindakan(): HasMany
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
     * Scope a query to only include priority reports.
     */
    public function scopePrioritas($query)
    {
        return $query->where('is_prioritas', true);
    }

    /**
     * Get the alamat lengkap attribute.
     */
    public function getAlamatLengkapAttribute(): string
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
     * Get the administrative area attribute.
     * Concatenates region names from Desa -> Kecamatan -> Kabupaten -> Provinsi
     * Returns full names as stored in database
     */
    public function getAdministrativeAreaAttribute(): ?string
    {
        // Check if desa relationship is loaded to prevent N+1 issues
        if (!$this->relationLoaded('desa') || !$this->desa) {
            return null;
        }

        // Use null-safe operator to traverse the hierarchy and filter out null values
        $regionNames = array_filter([
            $this->desa?->nama,
            $this->desa?->kecamatan?->nama,
            $this->desa?->kecamatan?->kabupaten?->nama,
            $this->desa?->kecamatan?->kabupaten?->provinsi?->nama
        ]);

        return implode(', ', $regionNames);
    }

    /**
     * Check if laporan needs verification.
     */
    public function needsVerification(): bool
    {
        return in_array($this->status, ['Draft', 'Menunggu Verifikasi']);
    }

    /**
     * Check if laporan is being processed.
     */
    public function isBeingProcessed(): bool
    {
        return in_array($this->status, ['Diverifikasi', 'Diproses']);
    }

    /**
     * Check if laporan is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'Selesai';
    }

    /**
     * Check if laporan is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'Ditolak';
    }

    /**
     * Get status badge class for UI.
     */
    public function getStatusBadgeClass(): string
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

    /**
     * Get formatted coordinates for mapping.
     */
    public function getCoordinatesAttribute(): array
    {
        return [
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude,
        ];
    }

    /**
     * Increment view count safely.
     */
    public function incrementViewCount(): int
    {
        $this->increment('view_count');
        return $this->view_count;
    }

    /**
     * Get human readable time difference.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->waktu_laporan->diffForHumans();
    }

    /**
     * Query scope for reports by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('id_pelapor', $userId);
    }

    /**
     * Query scope for reports by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('id_kategori_bencana', $categoryId);
    }

    /**
     * Query scope for reports by location radius.
     */
    public function scopeByLocationRadius($query, $lat, $lng, $radiusKm = 10)
    {
        return $query->selectRaw('*,
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance
        ', [$lat, $lng, $lat])
        ->having('distance', '<=', $radiusKm)
        ->orderBy('distance');
    }

    /**
     * Get the full URL for foto bukti 1.
     */
    public function getFotoBukti1UrlAttribute(): ?string
    {
        if ($this->foto_bukti_1) {
            return asset('storage/laporans/' . $this->foto_bukti_1);
        }

        return null;
    }

    /**
     * Get the full URL for foto bukti 2.
     */
    public function getFotoBukti2UrlAttribute(): ?string
    {
        if ($this->foto_bukti_2) {
            return asset('storage/laporans/' . $this->foto_bukti_2);
        }

        return null;
    }

    /**
     * Get the full URL for foto bukti 3.
     */
    public function getFotoBukti3UrlAttribute(): ?string
    {
        if ($this->foto_bukti_3) {
            return asset('storage/laporans/' . $this->foto_bukti_3);
        }

        return null;
    }

    /**
     * Get the full URL for video bukti.
     */
    public function getVideoBuktiUrlAttribute(): ?string
    {
        if ($this->video_bukti) {
            return asset('storage/laporans/' . $this->video_bukti);
        }

        return null;
    }
}