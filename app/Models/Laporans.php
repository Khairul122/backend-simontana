<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laporans extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Relasi lengkap yang digunakan oleh LaporansController dan LaporanWorkflowController.
     * Sentralisasi di model untuk menghindari duplikasi (DRY).
     */
    public const FULL_RELATIONS = [
        'pelapor.desa.kecamatan.kabupaten.provinsi',
        'kategori',
        'desa.kecamatan.kabupaten.provinsi',
        'verifikator.desa.kecamatan.kabupaten.provinsi',
        'penanggungJawab.desa.kecamatan.kabupaten.provinsi',
        'tindakLanjut.petugas.desa.kecamatan.kabupaten.provinsi',
        'tindakLanjut.laporan.pelapor.desa.kecamatan.kabupaten.provinsi',
        'tindakLanjut.laporan.kategori',
        'tindakLanjut.laporan.desa.kecamatan.kabupaten.provinsi',
        'monitoring.operator.desa.kecamatan.kabupaten.provinsi',
        'monitoring.laporan.pelapor.desa.kecamatan.kabupaten.provinsi',
        'monitoring.laporan.kategori',
        'monitoring.laporan.desa.kecamatan.kabupaten.provinsi',
    ];

    
    protected $table = 'laporans';

    
    protected $guarded = ['id'];

    
    protected $hidden = ['created_at', 'updated_at'];

    
    protected $appends = [
        'foto_bukti_1_url',
        'foto_bukti_2_url',
        'foto_bukti_3_url',
        'video_bukti_url',
        'administrative_area'
    ];

    
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

    
    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pelapor');
    }

    
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBencana::class, 'id_kategori_bencana');
    }

    
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }

    
    public function tindakLanjut(): HasMany
    {
        return $this->hasMany(TindakLanjut::class, 'laporan_id');
    }

    
    public function monitoring(): HasMany
    {
        return $this->hasMany(Monitoring::class, 'id_laporan');
    }

    
    public function riwayatTindakan(): HasManyThrough
    {
        return $this->hasManyThrough(
            RiwayatTindakan::class,
            TindakLanjut::class,
            'laporan_id',
            'tindaklanjut_id',
            'id',
            'id_tindaklanjut'
        );
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_verifikator');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_penanggung_jawab');
    }

    
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    
    public function scopeLastDays($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    
    public function scopePrioritas($query)
    {
        return $query->where('is_prioritas', true);
    }

    
    public function getAlamatLengkapAttribute(): ?string
    {
        return $this->attributes['alamat_lengkap'] ?? null;
    }

    
    public function getAdministrativeAreaAttribute(): ?string
    {
        
        if (!$this->relationLoaded('desa') || !$this->desa) {
            return null;
        }

        
        $regionNames = array_filter([
            $this->desa?->nama,
            $this->desa?->kecamatan?->nama,
            $this->desa?->kecamatan?->kabupaten?->nama,
            $this->desa?->kecamatan?->kabupaten?->provinsi?->nama
        ]);

        return implode(', ', $regionNames);
    }

    
    public function needsVerification(): bool
    {
        return in_array($this->status, ['Draft', 'Menunggu Verifikasi']);
    }

    
    public function isBeingProcessed(): bool
    {
        return in_array($this->status, ['Diverifikasi', 'Diproses']);
    }

    
    public function isCompleted(): bool
    {
        return $this->status === 'Selesai';
    }

    
    public function isRejected(): bool
    {
        return $this->status === 'Ditolak';
    }

    
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

    
    public function getCoordinatesAttribute(): array
    {
        return [
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude,
        ];
    }

    
    public function incrementViewCount(): int
    {
        $this->increment('view_count');
        return $this->view_count;
    }

    
    public function getTimeAgoAttribute(): string
    {
        return $this->waktu_laporan->diffForHumans();
    }

    
    public function scopeByUser($query, $userId)
    {
        return $query->where('id_pelapor', $userId);
    }

    
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('id_kategori_bencana', $categoryId);
    }

    
    public function scopeByLocationRadius($query, $lat, $lng, $radiusKm = 10)
    {
        return $query->selectRaw('*,
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance
        ', [$lat, $lng, $lat])
        ->having('distance', '<=', $radiusKm)
        ->orderBy('distance');
    }

    
    public function getFotoBukti1UrlAttribute(): ?string
    {
        if ($this->foto_bukti_1) {
            return asset('storage/laporans/' . $this->foto_bukti_1);
        }

        return null;
    }

    
    public function getFotoBukti2UrlAttribute(): ?string
    {
        if ($this->foto_bukti_2) {
            return asset('storage/laporans/' . $this->foto_bukti_2);
        }

        return null;
    }

    
    public function getFotoBukti3UrlAttribute(): ?string
    {
        if ($this->foto_bukti_3) {
            return asset('storage/laporans/' . $this->foto_bukti_3);
        }

        return null;
    }

    
    public function getVideoBuktiUrlAttribute(): ?string
    {
        if ($this->video_bukti) {
            return asset('storage/laporans/' . $this->video_bukti);
        }

        return null;
    }
}
