<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Rombel extends Model
{
    protected $table = 'rombel';

    protected $fillable = ['nama', 'tingkat', 'tahun_ajaran_id', 'guru_id'];

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    public function sesiUjian(): HasMany
    {
        return $this->hasMany(SesiUjian::class);
    }
}
