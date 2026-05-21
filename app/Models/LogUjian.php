<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogUjian extends Model
{
    protected $table = 'log_ujian';

    public $timestamps = false;

    protected $fillable = [
        'peserta_ujian_id',
        'tipe_event',
        'keterangan',
        'ip_address',
        'user_agent',
        'terjadi_at',
    ];

    protected $casts = [
        'terjadi_at' => 'datetime',
    ];

    public function pesertaUjian(): BelongsTo
    {
        return $this->belongsTo(PesertaUjian::class);
    }
}
