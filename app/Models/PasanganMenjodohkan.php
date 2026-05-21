<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasanganMenjodohkan extends Model
{
    protected $table = 'pasangan_menjodohkan';

    protected $fillable = [
        'soal_id',
        'kiri',
        'kanan',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class);
    }
}
