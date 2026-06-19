<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jadwal extends Model
{
    use HasHashId;

    protected $table = 'jadwal';

    protected $fillable = [
        'rombel_id',
        'mata_pelajaran_id',
        'guru_id',
        'tahun_ajaran_id',
        'hari',
        'jam_ke',
    ];

    protected function casts(): array
    {
        return [
            'jam_ke' => 'integer',
        ];
    }

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class);
    }

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
}
