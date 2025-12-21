<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBencana extends Model
{
    protected $table = 'kategori_bencana';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'icon'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke laporan
     */
    public function laporans(): HasMany
    {
        return $this->hasMany(Laporan::class, 'id_kategori_bencana');
    }
}