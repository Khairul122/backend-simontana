<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kabupaten extends Model
{
    use HasFactory;

    protected $table = 'kabupaten';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nama',
        'id_provinsi'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke tabel provinsi
     */
    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'id_provinsi');
    }

    /**
     * Relasi ke tabel kecamatan
     */
    public function kecamatans()
    {
        return $this->hasMany(Kecamatan::class, 'id_kabupaten');
    }
}
