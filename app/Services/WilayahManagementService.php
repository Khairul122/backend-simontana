<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Provinsi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WilayahManagementService
{
    private const CACHE_TTL_MINUTES = 30;

    private const MODEL_MAP = [
        'provinsi' => Provinsi::class,
        'kabupaten' => Kabupaten::class,
        'kecamatan' => Kecamatan::class,
        'desa' => Desa::class,
    ];

    private const INCLUDE_MAP = [
        'provinsi' => ['children' => 'kabupatens'],
        'kabupaten' => ['parent' => 'provinsi', 'children' => 'kecamatans'],
        'kecamatan' => ['parent' => 'kabupaten', 'children' => 'desas'],
        'desa' => ['parent' => 'kecamatan'],
    ];

    private const PARENT_CONFIG = [
        'kabupaten' => ['model' => Provinsi::class, 'field' => 'id_provinsi', 'message' => 'Provinsi tidak ditemukan'],
        'kecamatan' => ['model' => Kabupaten::class, 'field' => 'id_kabupaten', 'message' => 'Kabupaten tidak ditemukan'],
        'desa' => ['model' => Kecamatan::class, 'field' => 'id_kecamatan', 'message' => 'Kecamatan tidak ditemukan'],
    ];

    public function normalizeJenis(?string $jenis): ?string
    {
        if (!$jenis) {
            return null;
        }

        $jenis = strtolower($jenis);
        return array_key_exists($jenis, self::MODEL_MAP) ? $jenis : null;
    }

    public function buildQuery(string $jenis, ?string $include = null): Builder
    {
        $modelClass = self::MODEL_MAP[$jenis];
        $query = $modelClass::query();

        foreach ($this->parseIncludes($include) as $includeKey) {
            $relation = self::INCLUDE_MAP[$jenis][$includeKey] ?? null;
            if ($relation) {
                $query->with($relation);
            }
        }

        return $query;
    }

    public function buildAllWilayahData(): array
    {
        return Cache::remember('wilayah:all-hierarchy', now()->addMinutes(self::CACHE_TTL_MINUTES), static function () {
            return [
                'provinsi' => Provinsi::with('kabupatens')->orderBy('nama')->get(),
                'kabupaten' => Kabupaten::with('provinsi', 'kecamatans')->orderBy('nama')->get(),
                'kecamatan' => Kecamatan::with('kabupaten', 'desas')->orderBy('nama')->get(),
                'desa' => Desa::with('kecamatan')->orderBy('nama')->get(),
            ];
        });
    }

    public function search(string $q, ?string $jenis = null)
    {
        $normalized = $this->normalizeJenis($jenis);

        if ($normalized === 'provinsi') {
            return Provinsi::select(['id', 'nama'])->where('nama', 'LIKE', "%{$q}%")->limit(30)->get();
        }

        if ($normalized === 'kabupaten') {
            return Kabupaten::select(['id', 'nama', 'id_provinsi'])
                ->where('nama', 'LIKE', "%{$q}%")
                ->with('provinsi:id,nama')
                ->limit(30)
                ->get();
        }

        if ($normalized === 'kecamatan') {
            return Kecamatan::select(['id', 'nama', 'id_kabupaten'])
                ->where('nama', 'LIKE', "%{$q}%")
                ->with('kabupaten:id,nama,id_provinsi')
                ->limit(30)
                ->get();
        }

        if ($normalized === 'desa') {
            return Desa::select(['id', 'nama', 'id_kecamatan'])
                ->where('nama', 'LIKE', "%{$q}%")
                ->with('kecamatan:id,nama,id_kabupaten')
                ->limit(30)
                ->get();
        }

        return collect([
            'provinsi' => Provinsi::select(['id', 'nama'])->where('nama', 'LIKE', "%{$q}%")->limit(20)->get(),
            'kabupaten' => Kabupaten::select(['id', 'nama', 'id_provinsi'])
                ->where('nama', 'LIKE', "%{$q}%")
                ->with('provinsi:id,nama')
                ->limit(20)
                ->get(),
            'kecamatan' => Kecamatan::select(['id', 'nama', 'id_kabupaten'])
                ->where('nama', 'LIKE', "%{$q}%")
                ->with('kabupaten:id,nama,id_provinsi')
                ->limit(20)
                ->get(),
            'desa' => Desa::select(['id', 'nama', 'id_kecamatan'])
                ->where('nama', 'LIKE', "%{$q}%")
                ->with('kecamatan:id,nama,id_kabupaten')
                ->limit(20)
                ->get(),
        ]);
    }

    public function createByJenis(string $jenis, string $nama, ?int $idParent): array
    {
        $parentError = $this->validateParent($jenis, $idParent, true);
        if ($parentError) {
            return ['error' => $parentError];
        }

        if ($jenis === 'provinsi') {
            $wilayah = Provinsi::create(['nama' => $nama]);
            return ['model' => $wilayah];
        }

        $field = self::PARENT_CONFIG[$jenis]['field'];
        $modelClass = self::MODEL_MAP[$jenis];
        $wilayah = $modelClass::create([
            'nama' => $nama,
            $field => $idParent,
        ]);

        return ['model' => $wilayah];
    }

    public function updateByJenis(string $jenis, int $id, string $nama, ?int $idParent): array
    {
        $modelClass = self::MODEL_MAP[$jenis];
        $model = $modelClass::find($id);

        if (!$model) {
            return ['not_found' => $this->notFoundMessage($jenis)];
        }

        $parentError = $this->validateParent($jenis, $idParent, true);
        if ($parentError) {
            return ['error' => $parentError];
        }

        if ($jenis === 'provinsi') {
            $model->update(['nama' => $nama]);
            return ['model' => $model];
        }

        $field = self::PARENT_CONFIG[$jenis]['field'];
        $model->update([
            'nama' => $nama,
            $field => $idParent,
        ]);

        return ['model' => $model];
    }

    public function deleteByJenis(string $jenis, int $id): array
    {
        $modelClass = self::MODEL_MAP[$jenis];
        $model = $modelClass::find($id);

        if (!$model) {
            return ['not_found' => 'Wilayah tidak ditemukan'];
        }

        if ($jenis === 'provinsi' && $model->kabupatens()->count() > 0) {
            return ['error' => 'Tidak dapat menghapus provinsi karena masih memiliki kabupaten terkait'];
        }

        if ($jenis === 'kabupaten' && $model->kecamatans()->count() > 0) {
            return ['error' => 'Tidak dapat menghapus kabupaten karena masih memiliki kecamatan terkait'];
        }

        if ($jenis === 'kecamatan' && $model->desas()->count() > 0) {
            return ['error' => 'Tidak dapat menghapus kecamatan karena masih memiliki desa terkait'];
        }

        if ($jenis === 'desa' && ($model->pengguna()->count() > 0 || $model->laporan()->count() > 0)) {
            return ['error' => 'Tidak dapat menghapus desa karena masih memiliki data terkait'];
        }

        $deleted = $model->delete();
        if (!$deleted) {
            return ['failed' => true];
        }

        return ['deleted' => true];
    }

    public function getByParent(string $jenis, int $parentId, ?string $include = null)
    {
        $parentConfig = self::PARENT_CONFIG[$jenis] ?? null;
        if (!$parentConfig) {
            return null;
        }

        $parent = $parentConfig['model']::find($parentId);
        if (!$parent) {
            return ['error' => $parentConfig['message']];
        }

        $query = $this->buildQuery($jenis, $include)->where($parentConfig['field'], $parentId);

        return ['data' => $query->orderBy('nama')->get()];
    }

    private function parseIncludes(?string $include): array
    {
        if (!$include) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $include))));
    }

    private function validateParent(string $jenis, ?int $idParent, bool $required): ?string
    {
        if (!isset(self::PARENT_CONFIG[$jenis])) {
            return null;
        }

        if ($required && !$idParent) {
            return 'ID parent wajib diisi untuk jenis ' . $jenis . '.';
        }

        if (!$idParent) {
            return null;
        }

        $config = self::PARENT_CONFIG[$jenis];
        $exists = $config['model']::find($idParent);

        return $exists ? null : $config['message'];
    }

    private function notFoundMessage(string $jenis): string
    {
        return match ($jenis) {
            'provinsi' => 'Provinsi tidak ditemukan',
            'kabupaten' => 'Kabupaten tidak ditemukan',
            'kecamatan' => 'Kecamatan tidak ditemukan',
            'desa' => 'Desa tidak ditemukan',
            default => 'Wilayah tidak ditemukan',
        };
    }
}
