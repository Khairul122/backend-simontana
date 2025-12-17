<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'kecamatan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nama',
        'id_kabupaten'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke tabel kabupaten
     */
    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'id_kabupaten');
    }

    /**
     * Relasi ke tabel desa
     */
    public function desas()
    {
        return $this->hasMany(Desa::class, 'id_kecamatan');
    }
}
