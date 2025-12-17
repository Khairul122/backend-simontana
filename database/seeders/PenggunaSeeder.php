<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PenggunaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'nama' => 'Administrator SIMONTA',
                'username' => 'admin',
                'password' => bcrypt('admin123'),
                'role' => 'Admin',
                'email' => 'admin@simonta.id',
                'no_telepon' => '081234567890',
                'alamat' => 'Kantor BPBD Pusat',
                'id_desa' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Petugas BPBD Demo',
                'username' => 'petugas_bpbd',
                'password' => bcrypt('petugas123'),
                'role' => 'PetugasBPBD',
                'email' => 'petugas@bpbd.go.id',
                'no_telepon' => '082345678901',
                'alamat' => 'Kantor BPBD Provinsi',
                'id_desa' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Operator Desa Demo',
                'username' => 'operator_desa',
                'password' => bcrypt('operator123'),
                'role' => 'OperatorDesa',
                'email' => 'operator@desa.go.id',
                'no_telepon' => '083456789012',
                'alamat' => 'Kantor Desa',
                'id_desa' => 1, // Akan disesuaikan setelah data desa ada
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Warga Demo',
                'username' => 'warga_demo',
                'password' => bcrypt('warga123'),
                'role' => 'Warga',
                'email' => 'warga@example.com',
                'no_telepon' => '084567890123',
                'alamat' => 'Alamat warga demo',
                'id_desa' => 1, // Akan disesuaikan setelah data desa ada
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \DB::table('pengguna')->insert($users);
    }
}
