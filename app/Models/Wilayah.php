<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wilayah extends Model
{
    use HasFactory;

    protected $table = 'wilayah';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nama',
        'jenis', 
        'id_parent', 
        'kode_pos', 
        'kode_wilayah', 
        'latitude',
        'longitude',
        'luas_wilayah',
        'jumlah_penduduk',
        'tahun_pembentukan',
        'ibu_kota',
        'deskripsi',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'jumlah_penduduk' => 'integer',
        'tahun_pembentukan' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    
    public function parent()
    {
        return $this->belongsTo(Wilayah::class, 'id_parent');
    }

    
    public function children()
    {
        return $this->hasMany(Wilayah::class, 'id_parent');
    }

    
    public function scopeOfType($query, $jenis)
    {
        return $query->where('jenis', strtolower($jenis));
    }

    
    public function scopeOfParent($query, $parentId)
    {
        return $query->where('id_parent', $parentId);
    }

    
    public function scopeProvinsi($query)
    {
        return $query->where('jenis', 'provinsi');
    }

    
    public function scopeKabupaten($query)
    {
        return $query->where('jenis', 'kabupaten');
    }

    
    public function scopeKota($query)
    {
        return $query->where('jenis', 'kota');
    }

    
    public function scopeKecamatan($query)
    {
        return $query->where('jenis', 'kecamatan');
    }

    
    public function scopeDesa($query)
    {
        return $query->where('jenis', 'desa');
    }

    
    public function scopeKelurahan($query)
    {
        return $query->where('jenis', 'kelurahan');
    }

    
    public function getIsProvinsiAttribute()
    {
        return strtolower($this->jenis) === 'provinsi';
    }

    
    public function getIsKabupatenAttribute()
    {
        return strtolower($this->jenis) === 'kabupaten' || strtolower($this->jenis) === 'kota';
    }

    
    public function getIsKecamatanAttribute()
    {
        return strtolower($this->jenis) === 'kecamatan';
    }

    
    public function getIsDesaAttribute()
    {
        return strtolower($this->jenis) === 'desa' || strtolower($this->jenis) === 'kelurahan';
    }

    
    public function getHierarchyAttribute()
    {
        $hierarchy = [];
        
        $current = $this;
        while ($current) {
            array_unshift($hierarchy, $current);
            $current = $current->parent;
        }
        
        return $hierarchy;
    }

    
    public function getProvinsiAttribute()
    {
        $hierarchy = $this->hierarchy;
        foreach ($hierarchy as $wilayah) {
            if ($wilayah->isProvinsi) {
                return $wilayah;
            }
        }
        return null;
    }

    
    public function getKabupatenAttribute()
    {
        $hierarchy = $this->hierarchy;
        foreach ($hierarchy as $wilayah) {
            if ($wilayah->isKabupaten) {
                return $wilayah;
            }
        }
        return null;
    }

    
    public function getKecamatanAttribute()
    {
        $hierarchy = $this->hierarchy;
        foreach ($hierarchy as $wilayah) {
            if ($wilayah->isKecamatan) {
                return $wilayah;
            }
        }
        return null;
    }

    
    public function getDesaAttribute()
    {
        $hierarchy = $this->hierarchy;
        foreach ($hierarchy as $wilayah) {
            if ($wilayah->isDesa) {
                return $wilayah;
            }
        }
        return null;
    }
}