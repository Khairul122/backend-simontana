<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $provinsi = DB::table('provinsi')->insert([
            [
                'nama' => 'Jawa Barat',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'nama' => 'DKI Jakarta',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'nama' => 'Jawa Tengah',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        $kabupaten = DB::table('kabupaten')->insert([
            [
                'id_provinsi' => 1,
                'nama' => 'Bandung',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id_provinsi' => 2,
                'nama' => 'Jakarta Pusat',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id_provinsi' => 3,
                'nama' => 'Semarang',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        $kecamatan = DB::table('kecamatan')->insert([
            [
                'id_kabupaten' => 1,
                'nama' => 'Coblong',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id_kabupaten' => 2,
                'nama' => 'Menteng',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id_kabupaten' => 3,
                'nama' => 'Candisari',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        $desa = DB::table('desa')->insert([
            [
                'id_kecamatan' => 1,
                'nama' => 'Sukajadi',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id_kecamatan' => 2,
                'nama' => 'Menteng',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id_kecamatan' => 3,
                'nama' => 'Candisari',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);
    }
}
