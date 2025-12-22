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
        'jenis', // provinsi, kabupaten, kecamatan, desa
        'id_parent', // ID dari wilayah induk (jika ada)
        'kode_pos', // jika diperlukan
        'kode_wilayah', // kode wilayah resmi
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

    /**
     * Relasi ke wilayah induk
     */
    public function parent()
    {
        return $this->belongsTo(Wilayah::class, 'id_parent');
    }

    /**
     * Relasi ke wilayah anak-anak
     */
    public function children()
    {
        return $this->hasMany(Wilayah::class, 'id_parent');
    }

    /**
     * Scope untuk mencari wilayah berdasarkan jenis
     */
    public function scopeOfType($query, $jenis)
    {
        return $query->where('jenis', strtolower($jenis));
    }

    /**
     * Scope untuk mencari wilayah berdasarkan parent
     */
    public function scopeOfParent($query, $parentId)
    {
        return $query->where('id_parent', $parentId);
    }

    /**
     * Scope untuk mencari wilayah provinsi
     */
    public function scopeProvinsi($query)
    {
        return $query->where('jenis', 'provinsi');
    }

    /**
     * Scope untuk mencari wilayah kabupaten
     */
    public function scopeKabupaten($query)
    {
        return $query->where('jenis', 'kabupaten');
    }

    /**
     * Scope untuk mencari wilayah kota
     */
    public function scopeKota($query)
    {
        return $query->where('jenis', 'kota');
    }

    /**
     * Scope untuk mencari wilayah kecamatan
     */
    public function scopeKecamatan($query)
    {
        return $query->where('jenis', 'kecamatan');
    }

    /**
     * Scope untuk mencari wilayah desa
     */
    public function scopeDesa($query)
    {
        return $query->where('jenis', 'desa');
    }

    /**
     * Scope untuk mencari wilayah kelurahan
     */
    public function scopeKelurahan($query)
    {
        return $query->where('jenis', 'kelurahan');
    }

    /**
     * Getter untuk mengecek apakah ini provinsi
     */
    public function getIsProvinsiAttribute()
    {
        return strtolower($this->jenis) === 'provinsi';
    }

    /**
     * Getter untuk mengecek apakah ini kabupaten/kota
     */
    public function getIsKabupatenAttribute()
    {
        return strtolower($this->jenis) === 'kabupaten' || strtolower($this->jenis) === 'kota';
    }

    /**
     * Getter untuk mengecek apakah ini kecamatan
     */
    public function getIsKecamatanAttribute()
    {
        return strtolower($this->jenis) === 'kecamatan';
    }

    /**
     * Getter untuk mengecek apakah ini desa/kelurahan
     */
    public function getIsDesaAttribute()
    {
        return strtolower($this->jenis) === 'desa' || strtolower($this->jenis) === 'kelurahan';
    }

    /**
     * Getter untuk mendapatkan hirarki wilayah lengkap
     */
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

    /**
     * Getter untuk mendapatkan provinsi dari hirarki
     */
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

    /**
     * Getter untuk mendapatkan kabupaten dari hirarki
     */
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

    /**
     * Getter untuk mendapatkan kecamatan dari hirarki
     */
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

    /**
     * Getter untuk mendapatkan desa dari hirarki
     */
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