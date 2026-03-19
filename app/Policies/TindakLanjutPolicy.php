<?php

namespace App\Policies;

use App\Models\Laporans;
use App\Models\Pengguna;
use App\Models\TindakLanjut;

class TindakLanjutPolicy
{
    public function viewAny(Pengguna $user): bool
    {
        return in_array($user->role, ['Admin', 'PetugasBPBD', 'OperatorDesa', 'Warga'], true);
    }

    public function view(Pengguna $user, TindakLanjut $tindakLanjut): bool
    {
        if (in_array($user->role, ['Admin', 'PetugasBPBD', 'OperatorDesa'], true)) {
            return true;
        }

        return $user->role === 'Warga' && $tindakLanjut->laporan?->id_pelapor === $user->id;
    }

    public function create(Pengguna $user, Laporans $laporan, int $petugasId): bool
    {
        if ($user->role === 'Admin') {
            return true;
        }

        if (!in_array($user->role, ['PetugasBPBD', 'OperatorDesa'], true)) {
            return false;
        }

        if ($petugasId !== $user->id) {
            return false;
        }

        return in_array($laporan->status, ['Diverifikasi', 'Diproses'], true);
    }

    public function update(Pengguna $user, TindakLanjut $tindakLanjut): bool
    {
        if ($user->role === 'Admin') {
            return true;
        }

        return in_array($user->role, ['PetugasBPBD', 'OperatorDesa'], true)
            && $tindakLanjut->id_petugas === $user->id;
    }

    public function delete(Pengguna $user, TindakLanjut $tindakLanjut): bool
    {
        return $this->update($user, $tindakLanjut);
    }
}
