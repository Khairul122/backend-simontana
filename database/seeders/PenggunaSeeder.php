<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PenggunaSeeder extends Seeder
{
    
    public function run(): void
    {
        $users = [
            [
                'nama' => 'Administrator SIMONTA',
                'username' => 'admin_pusat',
                'password' => Hash::make('12345678'),
                'role' => 'Admin',
                'email' => 'admin@gmail.com',
                'no_telepon' => '081234567890',
                'alamat' => 'Kantor BPBD Pusat',
                'id_desa' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Petugas BPBD Demo',
                'username' => 'petugas_bpbd',
                'password' => Hash::make('password'),
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
                'password' => Hash::make('password'),
                'role' => 'OperatorDesa',
                'email' => 'operator@desa.go.id',
                'no_telepon' => '083456789012',
                'alamat' => 'Kantor Desa',
                'id_desa' => 3171011001,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Warga Demo',
                'username' => 'warga01',
                'password' => Hash::make('password'),
                'role' => 'Warga',
                'email' => 'warga@example.com',
                'no_telepon' => '084567890123',
                'alamat' => 'Alamat warga demo',
                'id_desa' => 3171011001,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('pengguna')->upsert(
            $users,
            ['username'],
            ['nama', 'password', 'role', 'email', 'no_telepon', 'alamat', 'id_desa', 'updated_at']
        );
    }
}
