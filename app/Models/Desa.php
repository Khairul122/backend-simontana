<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Desa extends Model
{
    protected $table = 'desa';
    protected $primaryKey = 'id_desa';

    protected $fillable = [
        'nama_desa',
        'kecamatan',
        'kabupaten'
    ];

    public function pengguna(): HasMany
    {
        return $this->hasMany(Pengguna::class, 'id_desa', 'id_desa');
    }
}