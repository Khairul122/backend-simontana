<?php

namespace App\Policies;

use App\Models\Laporans;
use App\Models\Monitoring;
use App\Models\Pengguna;

class MonitoringPolicy
{
    public function viewAny(Pengguna $user): bool
    {
        return in_array($user->role, ['Admin', 'PetugasBPBD', 'OperatorDesa', 'Warga'], true);
    }

    public function view(Pengguna $user, Monitoring $monitoring): bool
    {
        if ($user->role === 'Admin' || $user->role === 'PetugasBPBD' || $user->role === 'OperatorDesa') {
            return true;
        }

        return $user->role === 'Warga' && $monitoring->laporan?->id_pelapor === $user->id;
    }

    public function create(Pengguna $user, Laporans $laporan, int $operatorId): bool
    {
        if ($user->role === 'Admin') {
            return true;
        }

        if (!in_array($user->role, ['PetugasBPBD', 'OperatorDesa'], true)) {
            return false;
        }

        return $operatorId === $user->id && $laporan->status !== 'Ditolak';
    }

    public function update(Pengguna $user, Monitoring $monitoring, int $operatorId): bool
    {
        if ($user->role === 'Admin') {
            return true;
        }

        if (!in_array($user->role, ['PetugasBPBD', 'OperatorDesa'], true)) {
            return false;
        }

        return $monitoring->id_operator === $user->id && $operatorId === $user->id;
    }

    public function delete(Pengguna $user, Monitoring $monitoring): bool
    {
        if ($user->role === 'Admin') {
            return true;
        }

        return in_array($user->role, ['PetugasBPBD', 'OperatorDesa'], true)
            && $monitoring->id_operator === $user->id;
    }
}
