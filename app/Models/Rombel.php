<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rombel extends Model
{
    use HasHashId;

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

    public function sesiUjian(): BelongsToMany
    {
        return $this->belongsToMany(SesiUjian::class, 'rombel_sesi_ujian', 'rombel_id', 'sesi_ujian_id');
    }

    public function sesiUjianLegacy(): HasMany
    {
        return $this->hasMany(SesiUjian::class);
    }
}
