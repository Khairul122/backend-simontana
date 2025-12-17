<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiwayatTindakan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_tindakan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'tindaklanjut_id',
        'id_petugas',
        'keterangan',
        'waktu_tindakan',
    ];

    protected $casts = [
        'waktu_tindakan' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke model TindakLanjut
    public function tindakLanjut()
    {
        return $this->belongsTo(TindakLanjut::class, 'tindaklanjut_id', 'id_tindaklanjut');
    }

    // Relasi ke model Pengguna (Petugas)
    public function petugas()
    {
        return $this->belongsTo(Pengguna::class, 'id_petugas');
    }
}