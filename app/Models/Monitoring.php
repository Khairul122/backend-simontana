<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Monitoring extends Model
{
    use HasFactory;

    protected $table = 'monitoring';
    protected $primaryKey = 'id_monitoring';

    protected $fillable = [
        'id_laporan',
        'id_operator',
        'waktu_monitoring',
        'hasil_monitoring',
        'koordinat_gps',
    ];

    protected $casts = [
        'waktu_monitoring' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke model Laporan
    public function laporan()
    {
        return $this->belongsTo(Laporan::class, 'id_laporan', 'id_laporan');
    }

    // Relasi ke model Pengguna (Operator)
    public function operator()
    {
        return $this->belongsTo(Pengguna::class, 'id_operator');
    }
}