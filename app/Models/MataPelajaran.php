<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany};

class MataPelajaran extends Model
{
    use HasHashId;
    use SoftDeletes;

    protected $table = 'mata_pelajaran';

    protected $fillable = ['nama', 'kode', 'tingkat', 'jurusan'];

    public function bankSoal(): HasMany
    {
        return $this->hasMany(BankSoal::class);
    }

    public function paketUjian(): HasMany
    {
        return $this->hasMany(PaketUjian::class);
    }

    public function gurus(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'guru_mata_pelajaran')
            ->withTimestamps();
    }
}
