<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provinsi extends Model
{
    use HasFactory;

    protected $table = 'provinsi';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nama'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke tabel kabupaten
     */
    public function kabupatens()
    {
        return $this->hasMany(Kabupaten::class, 'id_provinsi');
    }
}
