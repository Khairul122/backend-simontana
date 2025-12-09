<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'nama',
        'username',
        'password',
        'role',
        'email',
        'no_telepon',
        'alamat',
        'id_desa'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed'
    ];

    // Relasi ke tabel desa
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'id_desa', 'id_desa');
    }

    // Relasi ke laporan (sebagai warga yang melaporkan)
    public function laporan(): HasMany
    {
        return $this->hasMany(Laporan::class, 'id_warga');
    }

    // Relasi ke tindaklanjut (sebagai petugas BPBD)
    public function tindaklanjut(): HasMany
    {
        return $this->hasMany(Tindaklanjut::class, 'id_petugas');
    }

    // Relasi ke monitoring (sebagai operator desa)
    public function monitoring(): HasMany
    {
        return $this->hasMany(Monitoring::class, 'id_operator');
    }

    // Relasi ke log activity
    public function logActivity(): HasMany
    {
        return $this->hasMany(LogActivity::class, 'user_id');
    }

    // Helper methods untuk role checking
    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function isPetugasBPBD(): bool
    {
        return $this->role === 'PetugasBPBD';
    }

    public function isOperatorDesa(): bool
    {
        return $this->role === 'OperatorDesa';
    }

    public function isWarga(): bool
    {
        return $this->role === 'Warga';
    }
}
