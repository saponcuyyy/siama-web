<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};

class SesiUjian extends Model
{
    use HasHashId;
    use SoftDeletes;

    protected $table = 'sesi_ujian';

    protected $fillable = [
        'paket_ujian_id',
        'rombel_id',
        'nama_sesi',
        'token',
        'waktu_mulai',
        'waktu_selesai',
        'toleransi_menit',
        'status',
        'max_pelanggaran',
        'wajib_fullscreen',
        'catatan',
        'dibuat_oleh',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'toleransi_menit' => 'integer',
        'max_pelanggaran' => 'integer',
        'wajib_fullscreen' => 'boolean',
    ];

    public function paketUjian(): BelongsTo
    {
        return $this->belongsTo(PaketUjian::class);
    }

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class);
    }

    public function rombels(): BelongsToMany
    {
        return $this->belongsToMany(Rombel::class, 'rombel_sesi_ujian', 'sesi_ujian_id', 'rombel_id');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function pesertaUjian(): HasMany
    {
        return $this->hasMany(PesertaUjian::class);
    }
}
