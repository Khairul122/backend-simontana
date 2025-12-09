<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBencana extends Model
{
    protected $table = 'kategori_bencana';
    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'nama_kategori'
    ];

    public function laporan(): HasMany
    {
        return $this->hasMany(Laporan::class, 'id_kategori', 'id_kategori');
    }
}