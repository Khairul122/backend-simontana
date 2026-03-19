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

    
    public function laporan()
    {
        return $this->belongsTo(Laporans::class, 'laporan_id', 'id');
    }

    
    public function petugas()
    {
        return $this->belongsTo(Pengguna::class, 'id_petugas');
    }

    
    public function riwayatTindakans()
    {
        return $this->hasMany(RiwayatTindakan::class, 'tindaklanjut_id', 'id_tindaklanjut');
    }
}