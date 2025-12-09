<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monitoring extends Model
{
    protected $table = 'monitoring';
    protected $primaryKey = 'id_monitoring';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_laporan',
        'id_operator',
        'waktu_monitoring',
        'hasil_monitoring',
        'koordinat_gps'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'waktu_monitoring' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the laporan that owns the monitoring.
     */
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'id_laporan', 'id_laporan');
    }

    /**
     * Get the operator that owns the monitoring.
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_operator', 'id');
    }

    /**
     * Alias for operator() method
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_operator', 'id');
    }

    /**
     * Scope a query to only include monitoring within date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('waktu_monitoring', [$startDate, $endDate]);
    }

    /**
     * Scope a query to search monitoring by hasil monitoring or koordinat.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('hasil_monitoring', 'LIKE', "%{$search}%")
              ->orWhere('koordinat_gps', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by specific laporan.
     */
    public function scopeForLaporan($query, $laporanId)
    {
        return $query->where('id_laporan', $laporanId);
    }

    /**
     * Scope a query to filter by operator.
     */
    public function scopeForOperator($query, $operatorId)
    {
        return $query->where('id_operator', $operatorId);
    }

    /**
     * Get the human readable monitoring status.
     */
    public function getStatusTextAttribute()
    {
        // Logic untuk menentukan status berdasarkan hasil monitoring
        $hasil = strtolower($this->hasil_monitoring);

        if (strpos($hasil, 'aman') !== false || strpos($hasil, 'selesai') !== false) {
            return 'Situasi Aman';
        } elseif (strpos($hasil, 'waspada') !== false || strpos($hasil, 'pantau') !== false) {
            return 'Perlu Pemantauan';
        } elseif (strpos($hasil, 'bahaya') !== false || strpos($hasil, 'darurat') !== false) {
            return 'Situasi Darurat';
        }

        return 'Sedang Dipantau';
    }

    /**
     * Check if monitoring is recent (within last 24 hours).
     */
    public function isRecent(): bool
    {
        return $this->waktu_monitoring->diffInHours(now()) <= 24;
    }

    /**
     * Get formatted date for display.
     */
    public function getFormattedDateAttribute()
    {
        return $this->waktu_monitoring->format('d M Y H:i');
    }

    /**
     * Get time elapsed since monitoring.
     */
    public function getTimeElapsedAttribute()
    {
        return $this->waktu_monitoring->diffForHumans();
    }

    /**
     * Parse GPS coordinates if available.
     */
    public function getParsedCoordinatesAttribute()
    {
        if (!$this->koordinat_gps) {
            return null;
        }

        // Parse koordinat format "latitude,longitude"
        $coords = explode(',', $this->koordinat_gps);

        return [
            'latitude' => isset($coords[0]) ? floatval(trim($coords[0])) : null,
            'longitude' => isset($coords[1]) ? floatval(trim($coords[1])) : null,
            'full_string' => $this->koordinat_gps
        ];
    }

    /**
     * Get latest monitoring for specific laporan.
     */
    public static function getLatestForLaporan($laporanId)
    {
        return static::where('id_laporan', $laporanId)
                    ->orderBy('waktu_monitoring', 'desc')
                    ->first();
    }

    /**
     * Count total monitoring records for specific laporan.
     */
    public static function getTotalForLaporan($laporanId)
    {
        return static::where('id_laporan', $laporanId)->count();
    }
}
