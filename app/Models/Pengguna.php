<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Pengguna extends Authenticatable implements JWTSubject
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengguna';
    protected $primaryKey = 'id';

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
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    
    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }

    
    public function laporan()
    {
        return $this->hasMany(Laporans::class, 'id_pelapor');
    }

    
    public function tindaklanjut()
    {
        return $this->hasMany(TindakLanjut::class, 'id_petugas');
    }

    
    public function monitoring()
    {
        return $this->hasMany(Monitoring::class, 'id_operator');
    }

    
    public function riwayatTindakan()
    {
        return $this->hasMany(RiwayatTindakan::class, 'id_petugas');
    }

    
    public function logActivity()
    {
        return $this->hasMany(LogActivity::class, 'user_id');
    }

    
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    
    public function isAdmin()
    {
        return $this->role === 'Admin';
    }

    
    public function isPetugasBPBD()
    {
        return $this->role === 'PetugasBPBD';
    }

    
    public function isOperatorDesa()
    {
        return $this->role === 'OperatorDesa';
    }

    
    public function isWarga()
    {
        return $this->role === 'Warga';
    }

    
    public static function getAvailableRoles()
    {
        return [
            'Admin' => 'Administrator',
            'PetugasBPBD' => 'Petugas BPBD',
            'OperatorDesa' => 'Operator Desa',
            'Warga' => 'Warga'
        ];
    }

    
    public function getRoleLabelAttribute()
    {
        return self::getAvailableRoles()[$this->role] ?? $this->role;
    }

    
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    
    public function getJWTCustomClaims()
    {
        return [];
    }

    
    public function hasRole($role): bool
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }

        return $this->role === $role;
    }
}
