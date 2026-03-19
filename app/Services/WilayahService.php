<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Kabupaten;
use App\Models\Provinsi;

class WilayahService
{
    
    public function getWilayahHierarchyByDesaId(int $desaId): ?array
    {
        $desa = Desa::with(['kecamatan.kabupaten.provinsi'])->find($desaId);

        if (!$desa) {
            return null;
        }

        return [
            'desa' => $desa,
            'kecamatan' => $desa->kecamatan,
            'kabupaten' => $desa->kecamatan->kabupaten,
            'provinsi' => $desa->kecamatan->kabupaten->provinsi
        ];
    }

    
    public function getDesaWithHierarchy(int $desaId): ?\App\Models\Desa
    {
        return Desa::with(['kecamatan.kabupaten.provinsi'])->find($desaId);
    }

    
    public function getKecamatanWithHierarchy(int $kecamatanId): ?\App\Models\Kecamatan
    {
        return Kecamatan::with(['kabupaten.provinsi'])->find($kecamatanId);
    }

    
    public function getKabupatenWithHierarchy(int $kabupatenId): ?\App\Models\Kabupaten
    {
        return Kabupaten::with(['provinsi'])->find($kabupatenId);
    }

    
    public function getKabupatenByProvinsi(int $provinsiId)
    {
        return Kabupaten::where('id_provinsi', $provinsiId)
            ->orderBy('nama')
            ->get();
    }

    
    public function getKecamatanByKabupaten(int $kabupatenId)
    {
        return Kecamatan::where('id_kabupaten', $kabupatenId)
            ->orderBy('nama')
            ->get();
    }

    
    public function getDesaByKecamatan(int $kecamatanId)
    {
        return Desa::where('id_kecamatan', $kecamatanId)
            ->orderBy('nama')
            ->get();
    }

    
    public function searchWilayah(string $search): array
    {
        $search = trim($search);

        if (empty($search)) {
            return [
                'provinsi' => collect([]),
                'kabupaten' => collect([]),
                'kecamatan' => collect([]),
                'desa' => collect([])
            ];
        }

        return [
            'provinsi' => Provinsi::where('nama', 'LIKE', "%{$search}%")->get(),
            'kabupaten' => Kabupaten::where('nama', 'LIKE', "%{$search}%")->with('provinsi')->get(),
            'kecamatan' => Kecamatan::where('nama', 'LIKE', "%{$search}%")->with('kabupaten.provinsi')->get(),
            'desa' => Desa::where('nama', 'LIKE', "%{$search}%")->with('kecamatan.kabupaten.provinsi')->get()
        ];
    }

    
    public function validateDesaInKecamatan(int $desaId, int $kecamatanId): bool
    {
        $desa = Desa::find($desaId);
        return $desa && $desa->id_kecamatan === $kecamatanId;
    }

    
    public function validateKecamatanInKabupaten(int $kecamatanId, int $kabupatenId): bool
    {
        $kecamatan = Kecamatan::find($kecamatanId);
        return $kecamatan && $kecamatan->id_kabupaten === $kabupatenId;
    }

    
    public function validateKabupatenInProvinsi(int $kabupatenId, int $provinsiId): bool
    {
        $kabupaten = Kabupaten::find($kabupatenId);
        return $kabupaten && $kabupaten->id_provinsi === $provinsiId;
    }

    
    public function getFormattedWilayahName(int $desaId): string
    {
        $hierarchy = $this->getWilayahHierarchyByDesaId($desaId);

        if (!$hierarchy) {
            return 'Wilayah tidak ditemukan';
        }

        return $hierarchy['desa']->nama . ', ' .
               $hierarchy['kecamatan']->nama . ', ' .
               $hierarchy['kabupaten']->nama . ', ' .
               $hierarchy['provinsi']->nama;
    }

    
    public function getWilayahCode(int $desaId): string
    {
        $hierarchy = $this->getWilayahHierarchyByDesaId($desaId);

        if (!$hierarchy) {
            return '';
        }

        
        return sprintf('%02d.%02d.%02d.%03d',
            $hierarchy['provinsi']->id,
            $hierarchy['kabupaten']->id,
            $hierarchy['kecamatan']->id,
            $hierarchy['desa']->id
        );
    }
}