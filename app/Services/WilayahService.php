<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Kabupaten;
use App\Models\Provinsi;

class WilayahService
{
    /**
     * Get full wilayah hierarchy by desa ID
     * 
     * @param int $desaId
     * @return array|null
     */
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

    /**
     * Get desa with full hierarchy by desa ID
     * 
     * @param int $desaId
     * @return \App\Models\Desa|null
     */
    public function getDesaWithHierarchy(int $desaId): ?\App\Models\Desa
    {
        return Desa::with(['kecamatan.kabupaten.provinsi'])->find($desaId);
    }

    /**
     * Get kecamatan with hierarchy by kecamatan ID
     * 
     * @param int $kecamatanId
     * @return \App\Models\Kecamatan|null
     */
    public function getKecamatanWithHierarchy(int $kecamatanId): ?\App\Models\Kecamatan
    {
        return Kecamatan::with(['kabupaten.provinsi'])->find($kecamatanId);
    }

    /**
     * Get kabupaten with hierarchy by kabupaten ID
     * 
     * @param int $kabupatenId
     * @return \App\Models\Kabupaten|null
     */
    public function getKabupatenWithHierarchy(int $kabupatenId): ?\App\Models\Kabupaten
    {
        return Kabupaten::with(['provinsi'])->find($kabupatenId);
    }

    /**
     * Get all kabupaten for a specific provinsi
     * 
     * @param int $provinsiId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getKabupatenByProvinsi(int $provinsiId)
    {
        return Kabupaten::where('id_provinsi', $provinsiId)
            ->orderBy('nama')
            ->get();
    }

    /**
     * Get all kecamatan for a specific kabupaten
     * 
     * @param int $kabupatenId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getKecamatanByKabupaten(int $kabupatenId)
    {
        return Kecamatan::where('id_kabupaten', $kabupatenId)
            ->orderBy('nama')
            ->get();
    }

    /**
     * Get all desa for a specific kecamatan
     * 
     * @param int $kecamatanId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDesaByKecamatan(int $kecamatanId)
    {
        return Desa::where('id_kecamatan', $kecamatanId)
            ->orderBy('nama')
            ->get();
    }

    /**
     * Search wilayah by name across all levels
     * 
     * @param string $search
     * @return array
     */
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

    /**
     * Validate if a desa ID exists and belongs to a specific kecamatan
     * 
     * @param int $desaId
     * @param int $kecamatanId
     * @return bool
     */
    public function validateDesaInKecamatan(int $desaId, int $kecamatanId): bool
    {
        $desa = Desa::find($desaId);
        return $desa && $desa->id_kecamatan === $kecamatanId;
    }

    /**
     * Validate if a kecamatan ID exists and belongs to a specific kabupaten
     * 
     * @param int $kecamatanId
     * @param int $kabupatenId
     * @return bool
     */
    public function validateKecamatanInKabupaten(int $kecamatanId, int $kabupatenId): bool
    {
        $kecamatan = Kecamatan::find($kecamatanId);
        return $kecamatan && $kecamatan->id_kabupaten === $kabupatenId;
    }

    /**
     * Validate if a kabupaten ID exists and belongs to a specific provinsi
     * 
     * @param int $kabupatenId
     * @param int $provinsiId
     * @return bool
     */
    public function validateKabupatenInProvinsi(int $kabupatenId, int $provinsiId): bool
    {
        $kabupaten = Kabupaten::find($kabupatenId);
        return $kabupaten && $kabupaten->id_provinsi === $provinsiId;
    }

    /**
     * Get formatted wilayah name by desa ID
     * 
     * @param int $desaId
     * @return string
     */
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

    /**
     * Get wilayah code by desa ID (for unique identification)
     * 
     * @param int $desaId
     * @return string
     */
    public function getWilayahCode(int $desaId): string
    {
        $hierarchy = $this->getWilayahHierarchyByDesaId($desaId);

        if (!$hierarchy) {
            return '';
        }

        // Format: P-K-K-D (Provinsi-Kabupaten-Kecamatan-Desa IDs)
        return sprintf('%02d.%02d.%02d.%03d',
            $hierarchy['provinsi']->id,
            $hierarchy['kabupaten']->id,
            $hierarchy['kecamatan']->id,
            $hierarchy['desa']->id
        );
    }
}