<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Laporan extends Model
{
    use HasFactory;

    protected $table = 'laporans';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_pelapor',
        'id_kategori_bencana',
        'id_desa',
        'judul_laporan',
        'deskripsi',
        'tingkat_keparahan',
        'status',
        'latitude',
        'longitude',
        'alamat_lengkap',
        'foto_bukti_1',
        'foto_bukti_2',
        'foto_bukti_3',
        'video_bukti',
        'id_verifikator',
        'id_penanggung_jawab',
        'waktu_verifikasi',
        'waktu_selesai',
        'catatan_verifikasi',
        'catatan_penanganan',
        'jumlah_korban',
        'jumlah_rumah_rusak',
        'data_tambahan',
        'is_prioritas',
        'view_count',
        'waktu_laporan'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'waktu_verifikasi' => 'datetime',
        'waktu_selesai' => 'datetime',
        'waktu_laporan' => 'datetime',
        'is_prioritas' => 'boolean',
        'jumlah_korban' => 'integer',
        'jumlah_rumah_rusak' => 'integer',
        'view_count' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'data_tambahan' => 'array'
    ];

    protected $hidden = [
        'updated_at'
    ];

    protected $appends = [
        'foto_urls',
        'video_url'
    ];

    public function pelapor()
    {
        return $this->belongsTo(Pengguna::class, 'id_pelapor');
    }

    public function kategoriBencana()
    {
        return $this->belongsTo(KategoriBencana::class, 'id_kategori_bencana');
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }

    public function verifikator()
    {
        return $this->belongsTo(Pengguna::class, 'id_verifikator');
    }

    public function penanggungJawab()
    {
        return $this->belongsTo(Pengguna::class, 'id_penanggung_jawab');
    }

    public function riwayatTindakans()
    {
        return $this->hasManyThrough(RiwayatTindakan::class, TindakLanjut::class, 'laporan_id', 'tindaklanjut_id', 'id_laporan');
    }

    public function tindakLanjuts()
    {
        return $this->hasMany(TindakLanjut::class, 'laporan_id', 'id_laporan');
    }

    public function monitorings()
    {
        return $this->hasMany(Monitoring::class, 'id_laporan', 'id_laporan');
    }

    public function getFotoUrlsAttribute()
    {
        $urls = [];

        for ($i = 1; $i <= 3; $i++) {
            $field = "foto_bukti_{$i}";
            if ($this->attributes[$field]) {
                $urls[$i] = Storage::url($this->attributes[$field]);
            }
        }

        return $urls;
    }

    public function getVideoUrlAttribute()
    {
        if ($this->video_bukti) {
            return Storage::url($this->video_bukti);
        }

        return null;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'Draft' => 'gray',
            'Menunggu Verifikasi' => 'yellow',
            'Diverifikasi' => 'blue',
            'Diproses' => 'indigo',
            'Selesai' => 'green',
            'Ditolak' => 'red',
            default => 'gray'
        };
    }

    public function getTingkatKeparahanColorAttribute()
    {
        return match($this->tingkat_keparahan) {
            'Rendah' => 'green',
            'Sedang' => 'yellow',
            'Tinggi' => 'orange',
            'Kritis' => 'red',
            default => 'gray'
        };
    }

    public function getIsOverdueAttribute()
    {
        if (!$this->waktu_verifikasi || in_array($this->status, ['Selesai', 'Ditolak'])) {
            return false;
        }

        return now()->diffInHours($this->waktu_verifikasi) > 48;
    }

    public function scopeFilter($query, $filters)
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['id_kategori_bencana'])) {
            $query->where('id_kategori_bencana', $filters['id_kategori_bencana']);
        }

        if (isset($filters['id_desa'])) {
            $query->where('id_desa', $filters['id_desa']);
        }

        if (isset($filters['tingkat_keparahan'])) {
            $query->where('tingkat_keparahan', $filters['tingkat_keparahan']);
        }

        if (isset($filters['is_prioritas'])) {
            $query->where('is_prioritas', $filters['is_prioritas']);
        }

        if (isset($filters['tanggal_mulai']) && isset($filters['tanggal_selesai'])) {
            $query->whereBetween('waktu_laporan', [
                $filters['tanggal_mulai'] . ' 00:00:00',
                $filters['tanggal_selesai'] . ' 23:59:59'
            ]);
        }

        if (isset($filters['id_pelapor'])) {
            $query->where('id_pelapor', $filters['id_pelapor']);
        }

        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul_laporan', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%")
                  ->orWhere('alamat_lengkap', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function scopeOrderByRelevance($query, $priority = 'is_prioritas')
    {
        return $query->orderBy($priority, 'desc')
                    ->orderBy('waktu_laporan', 'desc');
    }

    public function canBeVerifiedBy($user)
    {
        return in_array($user->role, ['PetugasBPBD', 'Admin']) &&
               in_array($this->status, ['Draft', 'Menunggu Verifikasi']) &&
               $this->id_pelapor !== $user->id;
    }

    public function canBeProcessedBy($user)
    {
        return in_array($user->role, ['PetugasBPBD', 'Admin']) &&
               $this->status === 'Diverifikasi';
    }

    public function canBeEditedBy($user)
    {
        return $this->id_pelapor === $user->id &&
               in_array($this->status, ['Draft', 'Menunggu Verifikasi']);
    }

    public function canBeDeletedBy($user)
    {
        return ($this->id_pelapor === $user->id && in_array($this->status, ['Draft', 'Menunggu Verifikasi'])) ||
               in_array($user->role, ['Admin']);
    }

    public function markAsVerified($userId, $notes = null)
    {
        $this->status = 'Diverifikasi';
        $this->id_verifikator = $userId;
        $this->waktu_verifikasi = now();
        if ($notes) {
            $this->catatan_verifikasi = $notes;
        }
        $this->save();
    }

    public function markAsRejected($userId, $notes)
    {
        $this->status = 'Ditolak';
        $this->id_verifikator = $userId;
        $this->waktu_verifikasi = now();
        $this->catatan_verifikasi = $notes;
        $this->save();
    }

    public function markAsProcessed($userId, $notes = null)
    {
        $this->status = 'Diproses';
        $this->id_penanggung_jawab = $userId;
        if ($notes) {
            $this->catatan_penanganan = $notes;
        }
        $this->save();
    }

    public function markAsCompleted($userId, $notes = null)
    {
        $this->status = 'Selesai';
        $this->id_penanggung_jawab = $userId;
        $this->waktu_selesai = now();
        if ($notes) {
            $this->catatan_penanganan = $notes;
        }
        $this->save();
    }

    public function incrementViewCount()
    {
        $this->increment('view_count');
    }
}