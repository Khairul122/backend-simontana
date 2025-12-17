<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TindakLanjut extends Model
{
    use HasFactory;

    protected $table = 'tindaklanjut';
    protected $primaryKey = 'id_tindaklanjut';

    protected $fillable = [
        'laporan_id',
        'id_petugas',
        'tanggal_tanggapan',
        'status',
    ];

    protected $casts = [
        'tanggal_tanggapan' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke model Laporan
    public function laporan()
    {
        return $this->belongsTo(Laporan::class, 'laporan_id', 'id_laporan');
    }

    // Relasi ke model Pengguna (Petugas)
    public function petugas()
    {
        return $this->belongsTo(Pengguna::class, 'id_petugas');
    }

    // Relasi ke riwayat tindakan
    public function riwayatTindakans()
    {
        return $this->hasMany(RiwayatTindakan::class, 'tindaklanjut_id', 'id_tindaklanjut');
    }
}