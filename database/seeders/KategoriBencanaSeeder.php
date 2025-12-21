<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriBencanaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriBencana = [
            [
                'nama_kategori' => 'Banjir',
                'deskripsi' => 'Bencana banjir yang disebabkan oleh meluapnya air sungai atau hujan deras',
                'icon' => 'flood.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Gempa Bumi',
                'deskripsi' => 'Bencana gempa bumi yang disebabkan oleh aktivitas tektonik',
                'icon' => 'earthquake.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Kebakaran Hutan',
                'deskripsi' => 'Bencana kebakaran hutan dan lahan',
                'icon' => 'forest_fire.png',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        \DB::table('kategori_bencana')->insert($kategoriBencana);
    }
}
