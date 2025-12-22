<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WilayahFallbackProvider
{
    /**
     * Get comprehensive fallback provinces data
     */
    public function getProvinces(): array
    {
        return [
            ['id' => 11, 'name' => 'Aceh'],
            ['id' => 12, 'name' => 'Sumatera Utara'],
            ['id' => 13, 'name' => 'Sumatera Barat'],
            ['id' => 14, 'name' => 'Riau'],
            ['id' => 15, 'name' => 'Jambi'],
            ['id' => 16, 'name' => 'Sumatera Selatan'],
            ['id' => 17, 'name' => 'Bengkulu'],
            ['id' => 18, 'name' => 'Lampung'],
            ['id' => 19, 'name' => 'Kepulauan Bangka Belitung'],
            ['id' => 21, 'name' => 'Kepulauan Riau'],
            ['id' => 31, 'name' => 'DKI Jakarta'],
            ['id' => 32, 'name' => 'Jawa Barat'],
            ['id' => 33, 'name' => 'Jawa Tengah'],
            ['id' => 34, 'name' => 'DI Yogyakarta'],
            ['id' => 35, 'name' => 'Jawa Timur'],
            ['id' => 36, 'name' => 'Banten'],
            ['id' => 51, 'name' => 'Bali'],
            ['id' => 52, 'name' => 'Nusa Tenggara Barat'],
            ['id' => 53, 'name' => 'Nusa Tenggara Timur'],
            ['id' => 61, 'name' => 'Kalimantan Barat'],
            ['id' => 62, 'name' => 'Kalimantan Tengah'],
            ['id' => 63, 'name' => 'Kalimantan Selatan'],
            ['id' => 64, 'name' => 'Kalimantan Timur'],
            ['id' => 65, 'name' => 'Kalimantan Utara'],
            ['id' => 71, 'name' => 'Sulawesi Utara'],
            ['id' => 72, 'name' => 'Sulawesi Tengah'],
            ['id' => 73, 'name' => 'Sulawesi Selatan'],
            ['id' => 74, 'name' => 'Sulawesi Tenggara'],
            ['id' => 75, 'name' => 'Gorontalo'],
            ['id' => 76, 'name' => 'Sulawesi Barat'],
            ['id' => 81, 'name' => 'Maluku'],
            ['id' => 82, 'name' => 'Maluku Utara'],
            ['id' => 91, 'name' => 'Papua Barat'],
            ['id' => 94, 'name' => 'Papua'],
        ];
    }

    /**
     * Get comprehensive fallback regencies data
     */
    public function getRegencies(): array
    {
        return [
            // Aceh (11)
            ['id' => 1101, 'province_id' => 11, 'name' => 'Kabupaten Simeulue'],
            ['id' => 1102, 'province_id' => 11, 'name' => 'Kabupaten Aceh Singkil'],
            ['id' => 1103, 'province_id' => 11, 'name' => 'Kabupaten Aceh Selatan'],
            ['id' => 1104, 'province_id' => 11, 'name' => 'Kabupaten Aceh Tenggara'],
            ['id' => 1105, 'province_id' => 11, 'name' => 'Kabupaten Aceh Timur'],
            ['id' => 1106, 'province_id' => 11, 'name' => 'Kabupaten Aceh Tengah'],
            ['id' => 1107, 'province_id' => 11, 'name' => 'Kabupaten Aceh Barat'],
            ['id' => 1108, 'province_id' => 11, 'name' => 'Kabupaten Aceh Besar'],
            ['id' => 1109, 'province_id' => 11, 'name' => 'Kabupaten Pidie'],
            ['id' => 1110, 'province_id' => 11, 'name' => 'Kabupaten Bireuen'],
            ['id' => 1111, 'province_id' => 11, 'name' => 'Kabupaten Aceh Utara'],
            ['id' => 1112, 'province_id' => 11, 'name' => 'Kabupaten Aceh Barat Daya'],
            ['id' => 1113, 'province_id' => 11, 'name' => 'Kabupaten Gayo Lues'],
            ['id' => 1114, 'province_id' => 11, 'name' => 'Kabupaten Aceh Tamiang'],
            ['id' => 1115, 'province_id' => 11, 'name' => 'Kabupaten Nagan Raya'],
            ['id' => 1116, 'province_id' => 11, 'name' => 'Kabupaten Aceh Jaya'],
            ['id' => 1171, 'province_id' => 11, 'name' => 'Kota Banda Aceh'],
            ['id' => 1172, 'province_id' => 11, 'name' => 'Kota Sabang'],
            ['id' => 1173, 'province_id' => 11, 'name' => 'Kota Langsa'],
            ['id' => 1174, 'province_id' => 11, 'name' => 'Kota Lhokseumawe'],
            ['id' => 1175, 'province_id' => 11, 'name' => 'Kota Subulussalam'],

            // Sumatera Utara (12)
            ['id' => 1201, 'province_id' => 12, 'name' => 'Kabupaten Nias'],
            ['id' => 1202, 'province_id' => 12, 'name' => 'Kabupaten Mandailing Natal'],
            ['id' => 1203, 'province_id' => 12, 'name' => 'Kabupaten Tapanuli Selatan'],
            ['id' => 1204, 'province_id' => 12, 'name' => 'Kabupaten Tapanuli Tengah'],
            ['id' => 1205, 'province_id' => 12, 'name' => 'Kabupaten Tapanuli Utara'],
            ['id' => 1206, 'province_id' => 12, 'name' => 'Kabupaten Toba'],
            ['id' => 1207, 'province_id' => 12, 'name' => 'Kabupaten Asahan'],
            ['id' => 1208, 'province_id' => 12, 'name' => 'Kabupaten Simalungun'],
            ['id' => 1209, 'province_id' => 12, 'name' => 'Kabupaten Dairi'],
            ['id' => 1210, 'province_id' => 12, 'name' => 'Kabupaten Karo'],
            ['id' => 1212, 'province_id' => 12, 'name' => 'Kabupaten Labuhanbatu'],
            ['id' => 1213, 'province_id' => 12, 'name' => 'Kabupaten Langkat'],
            ['id' => 1214, 'province_id' => 12, 'name' => 'Kabupaten Deli Serdang'],

            // DKI Jakarta (31)
            ['id' => 3171, 'province_id' => 31, 'name' => 'Kota Administrasi Jakarta Pusat'],
            ['id' => 3172, 'province_id' => 31, 'name' => 'Kota Administrasi Jakarta Utara'],
            ['id' => 3173, 'province_id' => 31, 'name' => 'Kota Administrasi Jakarta Barat'],
            ['id' => 3174, 'province_id' => 31, 'name' => 'Kota Administrasi Jakarta Selatan'],
            ['id' => 3175, 'province_id' => 31, 'name' => 'Kota Administrasi Jakarta Timur'],
            ['id' => 3176, 'province_id' => 31, 'name' => 'Kabupaten Administrasi Kepulauan Seribu'],

            // Jawa Barat (32)
            ['id' => 3201, 'province_id' => 32, 'name' => 'Kabupaten Bogor'],
            ['id' => 3202, 'province_id' => 32, 'name' => 'Kabupaten Sukabumi'],
            ['id' => 3203, 'province_id' => 32, 'name' => 'Kabupaten Cianjur'],
            ['id' => 3204, 'province_id' => 32, 'name' => 'Kabupaten Bandung'],
            ['id' => 3205, 'province_id' => 32, 'name' => 'Kabupaten Garut'],
            ['id' => 3206, 'province_id' => 32, 'name' => 'Kabupaten Tasikmalaya'],
            ['id' => 3207, 'province_id' => 32, 'name' => 'Kabupaten Ciamis'],
            ['id' => 3208, 'province_id' => 32, 'name' => 'Kabupaten Kuningan'],
            ['id' => 3209, 'province_id' => 32, 'name' => 'Kabupaten Cirebon'],
            ['id' => 3210, 'province_id' => 32, 'name' => 'Kabupaten Majalengka'],
            ['id' => 3211, 'province_id' => 32, 'name' => 'Kabupaten Sumedang'],
            ['id' => 3212, 'province_id' => 32, 'name' => 'Kabupaten Indramayu'],
            ['id' => 3213, 'province_id' => 32, 'name' => 'Kabupaten Subang'],
            ['id' => 3214, 'province_id' => 32, 'name' => 'Kabupaten Purwakarta'],
            ['id' => 3215, 'province_id' => 32, 'name' => 'Kabupaten Karawang'],
            ['id' => 3216, 'province_id' => 32, 'name' => 'Kabupaten Bekasi'],
            ['id' => 3271, 'province_id' => 32, 'name' => 'Kota Bogor'],
            ['id' => 3272, 'province_id' => 32, 'name' => 'Kota Sukabumi'],
            ['id' => 3273, 'province_id' => 32, 'name' => 'Kota Bandung'],
            ['id' => 3274, 'province_id' => 32, 'name' => 'Kota Cirebon'],
            ['id' => 3275, 'province_id' => 32, 'name' => 'Kota Bekasi'],
            ['id' => 3276, 'province_id' => 32, 'name' => 'Kota Depok'],
            ['id' => 3277, 'province_id' => 32, 'name' => 'Kota Cimahi'],
            ['id' => 3278, 'province_id' => 32, 'name' => 'Kota Tasikmalaya'],
            ['id' => 3279, 'province_id' => 32, 'name' => 'Kota Banjar'],

            // Jawa Tengah (33)
            ['id' => 3301, 'province_id' => 33, 'name' => 'Kabupaten Cilacap'],
            ['id' => 3302, 'province_id' => 33, 'name' => 'Kabupaten Banyumas'],
            ['id' => 3303, 'province_id' => 33, 'name' => 'Kabupaten Purbalingga'],
            ['id' => 3304, 'province_id' => 33, 'name' => 'Kabupaten Banjarnegara'],
            ['id' => 3305, 'province_id' => 33, 'name' => 'Kabupaten Kebumen'],
            ['id' => 3306, 'province_id' => 33, 'name' => 'Kabupaten Purworejo'],
            ['id' => 3307, 'province_id' => 33, 'name' => 'Kabupaten Wonosobo'],
            ['id' => 3308, 'province_id' => 33, 'name' => 'Kabupaten Magelang'],
            ['id' => 3309, 'province_id' => 33, 'name' => 'Kabupaten Boyolali'],
            ['id' => 3310, 'province_id' => 33, 'name' => 'Kabupaten Klaten'],
            ['id' => 3311, 'province_id' => 33, 'name' => 'Kabupaten Sukoharjo'],
            ['id' => 3312, 'province_id' => 33, 'name' => 'Kabupaten Wonogiri'],
            ['id' => 3313, 'province_id' => 33, 'name' => 'Kabupaten Karanganyar'],
            ['id' => 3314, 'province_id' => 33, 'name' => 'Kabupaten Sragen'],
            ['id' => 3315, 'province_id' => 33, 'name' => 'Kabupaten Grobogan'],
            ['id' => 3316, 'province_id' => 33, 'name' => 'Kabupaten Blora'],
            ['id' => 3317, 'province_id' => 33, 'name' => 'Kabupaten Rembang'],
            ['id' => 3318, 'province_id' => 33, 'name' => 'Kabupaten Pati'],
            ['id' => 3319, 'province_id' => 33, 'name' => 'Kabupaten Kudus'],
            ['id' => 3320, 'province_id' => 33, 'name' => 'Kabupaten Jepara'],
            ['id' => 3321, 'province_id' => 33, 'name' => 'Kabupaten Demak'],
            ['id' => 3322, 'province_id' => 33, 'name' => 'Kabupaten Semarang'],
            ['id' => 3323, 'province_id' => 33, 'name' => 'Kabupaten Temanggung'],
            ['id' => 3324, 'province_id' => 33, 'name' => 'Kabupaten Kendal'],
            ['id' => 3325, 'province_id' => 33, 'name' => 'Kabupaten Batang'],
            ['id' => 3326, 'province_id' => 33, 'name' => 'Kabupaten Pekalongan'],
            ['id' => 3327, 'province_id' => 33, 'name' => 'Kabupaten Pemalang'],
            ['id' => 3328, 'province_id' => 33, 'name' => 'Kabupaten Tegal'],
            ['id' => 3329, 'province_id' => 33, 'name' => 'Kabupaten Brebes'],
            ['id' => 3371, 'province_id' => 33, 'name' => 'Kota Magelang'],
            ['id' => 3372, 'province_id' => 33, 'name' => 'Kota Surakarta'],
            ['id' => 3373, 'province_id' => 33, 'name' => 'Kota Salatiga'],
            ['id' => 3374, 'province_id' => 33, 'name' => 'Kota Semarang'],
            ['id' => 3375, 'province_id' => 33, 'name' => 'Kota Pekalongan'],
            ['id' => 3376, 'province_id' => 33, 'name' => 'Kota Tegal'],

            // Jawa Timur (35)
            ['id' => 3501, 'province_id' => 35, 'name' => 'Kabupaten Pacitan'],
            ['id' => 3502, 'province_id' => 35, 'name' => 'Kabupaten Ponorogo'],
            ['id' => 3503, 'province_id' => 35, 'name' => 'Kabupaten Trenggalek'],
            ['id' => 3504, 'province_id' => 35, 'name' => 'Kabupaten Tulungagung'],
            ['id' => 3505, 'province_id' => 35, 'name' => 'Kabupaten Blitar'],
            ['id' => 3506, 'province_id' => 35, 'name' => 'Kabupaten Kediri'],
            ['id' => 3507, 'province_id' => 35, 'name' => 'Kabupaten Malang'],
            ['id' => 3508, 'province_id' => 35, 'name' => 'Kabupaten Lumajang'],
            ['id' => 3509, 'province_id' => 35, 'name' => 'Kabupaten Jember'],
            ['id' => 3510, 'province_id' => 35, 'name' => 'Kabupaten Banyuwangi'],
            ['id' => 3511, 'province_id' => 35, 'name' => 'Kabupaten Bondowoso'],
            ['id' => 3512, 'province_id' => 35, 'name' => 'Kabupaten Situbondo'],
            ['id' => 3513, 'province_id' => 35, 'name' => 'Kabupaten Probolinggo'],
            ['id' => 3514, 'province_id' => 35, 'name' => 'Kabupaten Pasuruan'],
            ['id' => 3515, 'province_id' => 35, 'name' => 'Kabupaten Sidoarjo'],
            ['id' => 3516, 'province_id' => 35, 'name' => 'Kabupaten Mojokerto'],
            ['id' => 3517, 'province_id' => 35, 'name' => 'Kabupaten Jombang'],
            ['id' => 3518, 'province_id' => 35, 'name' => 'Kabupaten Nganjuk'],
            ['id' => 3519, 'province_id' => 35, 'name' => 'Kabupaten Madiun'],
            ['id' => 3520, 'province_id' => 35, 'name' => 'Kabupaten Magetan'],
            ['id' => 3521, 'province_id' => 35, 'name' => 'Kabupaten Ngawi'],
            ['id' => 3522, 'province_id' => 35, 'name' => 'Kabupaten Bojonegoro'],
            ['id' => 3523, 'province_id' => 35, 'name' => 'Kabupaten Tuban'],
            ['id' => 3524, 'province_id' => 35, 'name' => 'Kabupaten Lamongan'],
            ['id' => 3525, 'province_id' => 35, 'name' => 'Kabupaten Gresik'],
            ['id' => 3526, 'province_id' => 35, 'name' => 'Kabupaten Bangkalan'],
            ['id' => 3527, 'province_id' => 35, 'name' => 'Kabupaten Sampang'],
            ['id' => 3528, 'province_id' => 35, 'name' => 'Kabupaten Pamekasan'],
            ['id' => 3529, 'province_id' => 35, 'name' => 'Kabupaten Sumenep'],
            ['id' => 3571, 'province_id' => 35, 'name' => 'Kota Kediri'],
            ['id' => 3572, 'province_id' => 35, 'name' => 'Kota Blitar'],
            ['id' => 3573, 'province_id' => 35, 'name' => 'Kota Malang'],
            ['id' => 3574, 'province_id' => 35, 'name' => 'Kota Probolinggo'],
            ['id' => 3575, 'province_id' => 35, 'name' => 'Kota Pasuruan'],
            ['id' => 3576, 'province_id' => 35, 'name' => 'Kota Mojokerto'],
            ['id' => 3577, 'province_id' => 35, 'name' => 'Kota Madiun'],
            ['id' => 3578, 'province_id' => 35, 'name' => 'Kota Surabaya'],
            ['id' => 3579, 'province_id' => 35, 'name' => 'Kota Batu'],
        ];
    }

    /**
     * Get sample districts data (comprehensive sample)
     */
    public function getDistricts(): array
    {
        return [
            // Sample districts from major cities
            ['id' => 3201010, 'regency_id' => 3201, 'name' => 'Kecamatan Cibinong'],
            ['id' => 3201011, 'regency_id' => 3201, 'name' => 'Kecamatan Bojonggede'],
            ['id' => 3201012, 'regency_id' => 3201, 'name' => 'Kecamatan Tajurhalang'],
            ['id' => 3201013, 'regency_id' => 3201, 'name' => 'Kecamatan Sukaraja'],
            ['id' => 3201014, 'regency_id' => 3201, 'name' => 'Kecamatan Babakanmadang'],
            ['id' => 3201015, 'regency_id' => 3201, 'name' => 'Kecamatan Citeureup'],
            ['id' => 3201016, 'regency_id' => 3201, 'name' => 'Kecamatan Cileungsi'],
            ['id' => 3201017, 'regency_id' => 3201, 'name' => 'Kecamatan Jonggol'],
            ['id' => 3201018, 'regency_id' => 3201, 'name' => 'Kecamatan Klapanunggal'],
            ['id' => 3201019, 'regency_id' => 3201, 'name' => 'Kecamatan Ciseeng'],
            ['id' => 3271010, 'regency_id' => 3271, 'name' => 'Kecamatan Bogor Tengah'],
            ['id' => 3271011, 'regency_id' => 3271, 'name' => 'Kecamatan Bogor Selatan'],
            ['id' => 3271012, 'regency_id' => 3271, 'name' => 'Kecamatan Bogor Timur'],
            ['id' => 3271013, 'regency_id' => 3271, 'name' => 'Kecamatan Bogor Utara'],
            ['id' => 3271014, 'regency_id' => 3271, 'name' => 'Kecamatan Bogor Barat'],
            ['id' => 3271015, 'regency_id' => 3271, 'name' => 'Kecamatan Tanah Sareal'],
            ['id' => 3171010, 'regency_id' => 3171, 'name' => 'Kecamatan Menteng'],
            ['id' => 3171011, 'regency_id' => 3171, 'name' => 'Kecamatan Gambir'],
            ['id' => 3171012, 'regency_id' => 3171, 'name' => 'Kecamatan Tanah Abang'],
            ['id' => 3171013, 'regency_id' => 3171, 'name' => 'Kecamatan Cempaka Putih'],
            ['id' => 3171014, 'regency_id' => 3171, 'name' => 'Kecamatan Sawah Besar'],
        ];
    }

    /**
     * Get sample villages data
     */
    public function getVillages(): array
    {
        return [
            ['id' => 3201010001, 'district_id' => 3201010, 'name' => 'Kelurahan Cibinong'],
            ['id' => 3201010002, 'district_id' => 3201010, 'name' => 'Kelurahan Pakansari'],
            ['id' => 3201010003, 'district_id' => 3201010, 'name' => 'Kelurahan Tengah'],
            ['id' => 3201010004, 'district_id' => 3201010, 'name' => 'Kelurahan Pabuaran'],
            ['id' => 3201010005, 'district_id' => 3201010, 'name' => 'Kelurahan Sukahati'],
            ['id' => 3201011001, 'district_id' => 3201011, 'name' => 'Kelurahan Bojonggede'],
            ['id' => 3201011002, 'district_id' => 3201011, 'name' => 'Kelurahan Kedunghalang'],
            ['id' => 3201011003, 'district_id' => 3201011, 'name' => 'Kelurahan Ragajaya'],
            ['id' => 3201011004, 'district_id' => 3201011, 'name' => 'Kelurahan Bojonggede Baru'],
            ['id' => 3201011005, 'district_id' => 3201011, 'name' => 'Kelurahan Citayam'],
            ['id' => 3171010001, 'district_id' => 3171010, 'name' => 'Kelurahan Menteng'],
            ['id' => 3171010002, 'district_id' => 3171011, 'name' => 'Kelurahan Gambir'],
            ['id' => 3171010003, 'district_id' => 3171012, 'name' => 'Kelurahan Tanah Abang'],
            ['id' => 3171010004, 'district_id' => 3171013, 'name' => 'Kelurahan Cempaka Putih'],
            ['id' => 3171010005, 'district_id' => 3171014, 'name' => 'Kelurahan Sawah Besar'],
        ];
    }

    /**
     * Get regencies by province ID from fallback data
     */
    public function getRegenciesByProvince(int $provinceId): array
    {
        return array_filter($this->getRegencies(), fn($r) => $r['province_id'] === $provinceId);
    }

    /**
     * Get districts by regency ID from fallback data
     */
    public function getDistrictsByRegency(int $regencyId): array
    {
        return array_filter($this->getDistricts(), fn($d) => $d['regency_id'] === $regencyId);
    }

    /**
     * Get villages by district ID from fallback data
     */
    public function getVillagesByDistrict(int $districtId): array
    {
        return array_filter($this->getVillages(), fn($v) => $v['district_id'] === $districtId);
    }
}