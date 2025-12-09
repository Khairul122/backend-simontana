<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogActivity extends Model
{
    protected $table = 'log_activity';
    protected $primaryKey = 'id_log';

    protected $fillable = [
        'user_id',
        'role',
        'aktivitas',
        'endpoint',
        'ip_address',
        'device_info',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }
}
