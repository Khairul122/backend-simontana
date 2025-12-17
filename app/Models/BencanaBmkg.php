<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BencanaBmkg extends Model
{
    use HasFactory;

    protected $table = 'bencana_bmkg';
    protected $primaryKey = 'id_bencana';
    
    protected $fillable = [
        'jenis_bencana',      // gempa_bumi, cuaca_ekstrem, dll
        'judul',              // Judul kejadian
        'isi_data',           // JSON data yang diperoleh dari BMKG
        'waktu_pembaruan',    // Waktu data diperoleh dari BMKG
        'lokasi',             // Lokasi kejadian (opsional)
        'lintang',            // Koordinat lintang (opsional)
        'bujur',              // Koordinat bujur (opsional)
        'magnitude',          // Magnitude jika gempa (opsional)
        'kedalaman',          // Kedalaman jika gempa (opsional)
        'peringkat',          // Tingkat keparahan (opsional)
        'sumber_data',        // URL sumber data BMKG
    ];

    protected $casts = [
        'waktu_pembaruan' => 'datetime',
        'isi_data' => 'array',  // Data JSON dari BMKG
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'tanggal',
        'jam',
        'waktu_lengkap',
        'magnitudo',
        'kedalaman',
        'koordinat',
        'lintang_text',
        'bujur_text',
        'lokasi_lengkap',
        'potensi',
        'dirasakan',
        'peringkat_dirasakan',
        'shakemap'
    ];

    // Relasi ke model Laporan (jika diperlukan nanti)
    public function laporans()
    {
        return $this->belongsToMany(Laporan::class, 'relasi_bmkg_laporan', 'id_bencana_bmkg', 'id_laporan');
    }

    // Accessor untuk tanggal
    public function getTanggalAttribute()
    {
        $data = $this->isi_data;
        if (is_array($data)) {
            return $data['Tanggal'] ?? null;
        }
        return null;
    }

    // Accessor untuk jam
    public function getJamAttribute()
    {
        $data = $this->isi_data;
        if (is_array($data)) {
            return $data['Jam'] ?? null;
        }
        return null;
    }

    // Accessor untuk waktu lengkap (DateTime)
    public function getWaktuLengkapAttribute()
    {
        $data = $this->isi_data;
        if (is_array($data)) {
            return $data['DateTime'] ?? null;
        }
        return null;
    }

    // Accessor untuk magnitudo
    public function getMagnitudoAttribute()
    {
        $data = $this->isi_data;
        if (is_array($data)) {
            return $data['Magnitude'] ?? $data['Magnitudo'] ?? null;
        }
        return null;
    }

    // Accessor untuk kedalaman
    public function getKedalamanAttribute()
    {
        $data = $this->isi_data;
        if (is_array($data)) {
            return $data['Kedalaman'] ?? null;
        }
        return null;
    }

    // Accessor untuk koordinat
    public function getKoordinatAttribute()
    {
        $data = $this->isi_data;
        if (is_array($data)) {
            if (isset($data['point']['coordinates'])) {
                return $data['point']['coordinates'];
            }
            // Jika hanya ada string koordinat
            return $data['coordinates'] ?? null;
        }
        return null;
    }

    // Accessor untuk lintang dalam bentuk teks
    public function getLintangTextAttribute()
    {
        $data = $this->isi_data;
        if (is_array($data)) {
            return $data['Lintang'] ?? null;
        }
        return null;
    }

    // Accessor untuk bujur dalam bentuk teks
    public function getBujurTextAttribute()
    {
        $data = $this->isi_data;
        if (is_array($data)) {
            return $data['Bujur'] ?? null;
        }
        return null;
    }

    // Accessor untuk lokasi lengkap
    public function getLokasiLengkapAttribute()
    {
        $data = $this->isi_data;
        if (is_array($data)) {
            return $data['Wilayah'] ?? $data['Lokasi'] ?? null;
        }
        return null;
    }

    // Accessor untuk potensi
    public function getPotensiAttribute()
    {
        $data = $this->isi_data;
        if (is_array($data)) {
            return $data['Potensi'] ?? null;
        }
        return null;
    }

    // Accessor untuk dirasakan
    public function getDirasakanAttribute()
    {
        $data = $this->isi_data;
        if (is_array($data)) {
            return $data['Dirasakan'] ?? null;
        }
        return null;
    }

    // Accessor untuk peringkat dirasakan
    public function getPeringkatDirasakanAttribute()
    {
        $data = $this->isi_data;
        if (is_array($data)) {
            return $data['PeringkatDirasakan'] ?? $data['Dirasakan'] ?? null;
        }
        return null;
    }

    // Accessor untuk shakemap
    public function getShakemapAttribute()
    {
        $data = $this->isi_data;
        if (is_array($data)) {
            return $data['Shakemap'] ?? null;
        }
        return null;
    }
}