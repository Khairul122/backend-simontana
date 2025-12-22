<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Desa extends Model
{
    use HasFactory;

    protected $table = 'desa';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nama',
        'id_kecamatan'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke kecamatan
     */
    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan');
    }

    /**
     * Relasi ke pengguna
     */
    public function pengguna()
    {
        return $this->hasMany(Pengguna::class, 'id_desa');
    }

    /**
     * Relasi ke laporan
     */
    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'id_desa');
    }
}