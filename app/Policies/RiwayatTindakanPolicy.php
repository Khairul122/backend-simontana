<?php

namespace App\Policies;

use App\Models\Pengguna;
use App\Models\RiwayatTindakan;
use App\Models\TindakLanjut;

class RiwayatTindakanPolicy
{
    public function viewAny(Pengguna $user): bool
    {
        return in_array($user->role, ['Admin', 'PetugasBPBD', 'OperatorDesa', 'Warga'], true);
    }

    public function view(Pengguna $user, RiwayatTindakan $riwayatTindakan): bool
    {
        if (in_array($user->role, ['Admin', 'PetugasBPBD', 'OperatorDesa'], true)) {
            return true;
        }

        return $user->role === 'Warga'
            && $riwayatTindakan->tindakLanjut?->laporan?->id_pelapor === $user->id;
    }

    public function create(Pengguna $user, TindakLanjut $tindakLanjut, int $petugasId): bool
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

        return $tindakLanjut->id_petugas === $user->id;
    }

    public function update(Pengguna $user, RiwayatTindakan $riwayatTindakan): bool
    {
        if ($user->role === 'Admin') {
            return true;
        }

        return in_array($user->role, ['PetugasBPBD', 'OperatorDesa'], true)
            && $riwayatTindakan->id_petugas === $user->id;
    }

    public function delete(Pengguna $user, RiwayatTindakan $riwayatTindakan): bool
    {
        return $this->update($user, $riwayatTindakan);
    }
}
