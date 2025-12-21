<?php

namespace App\Models\Report;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBencana extends Model
{
    use HasFactory;

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

    public function laporans(): HasMany
    {
        return $this->hasMany(\App\Models\Report\Laporan::class, 'id_kategori_bencana');
    }
}