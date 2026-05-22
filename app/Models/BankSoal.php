<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class BankSoal extends Model
{
    use HasHashId;
    use SoftDeletes;

    protected $table = 'bank_soal';

    protected $fillable = [
        'mata_pelajaran_id',
        'guru_id',
        'tahun_ajaran_id',
        'judul',
        'deskripsi',
        'tingkat',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function soal(): HasMany
    {
        return $this->hasMany(Soal::class)->orderBy('urutan');
    }
}
