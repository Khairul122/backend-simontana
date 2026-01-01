<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Pengguna extends Authenticatable implements JWTSubject
{
    use HasFactory;

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

    /**
     * Relasi ke tabel desa
     */
    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }

    /**
     * Relasi ke laporan yang dibuat oleh warga
     */
    public function laporan()
    {
        return $this->hasMany(Laporans::class, 'id_pelapor');
    }

    /**
     * Relasi ke tindaklanjut yang ditangani oleh petugas
     */
    public function tindaklanjut()
    {
        return $this->hasMany(Tindaklanjut::class, 'id_petugas');
    }

    /**
     * Relasi ke monitoring yang dilakukan oleh operator
     */
    public function monitoring()
    {
        return $this->hasMany(Monitoring::class, 'id_operator');
    }

    /**
     * Relasi ke riwayat tindakan yang dilakukan
     */
    public function riwayatTindakan()
    {
        return $this->hasMany(RiwayatTindakan::class, 'id_petugas');
    }

    /**
     * Relasi ke log activity
     */
    public function logActivity()
    {
        return $this->hasMany(LogActivity::class, 'user_id');
    }

    /**
     * Setter untuk password (auto hash)
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Check apakah user adalah admin
     */
    public function isAdmin()
    {
        return $this->role === 'Admin';
    }

    /**
     * Check apakah user adalah petugas BPBD
     */
    public function isPetugasBPBD()
    {
        return $this->role === 'PetugasBPBD';
    }

    /**
     * Check apakah user adalah operator desa
     */
    public function isOperatorDesa()
    {
        return $this->role === 'OperatorDesa';
    }

    /**
     * Check apakah user adalah warga
     */
    public function isWarga()
    {
        return $this->role === 'Warga';
    }

    /**
     * Get semua role yang tersedia
     */
    public static function getAvailableRoles()
    {
        return [
            'Admin' => 'Administrator',
            'PetugasBPBD' => 'Petugas BPBD',
            'OperatorDesa' => 'Operator Desa',
            'Warga' => 'Warga'
        ];
    }

    /**
     * Get label role yang readable
     */
    public function getRoleLabelAttribute()
    {
        return self::getAvailableRoles()[$this->role] ?? $this->role;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Check if user has specific role
     *
     * @param string|array $role
     * @return bool
     */
    public function hasRole($role): bool
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }

        return $this->role === $role;
    }
}
