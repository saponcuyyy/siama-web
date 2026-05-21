<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PilihanJawaban extends Model
{
    protected $table = 'pilihan_jawaban';

    protected $fillable = [
        'soal_id',
        'kode',
        'teks',
        'gambar_path',
        'is_benar',
        'urutan',
    ];

    protected $casts = [
        'is_benar' => 'boolean',
        'urutan' => 'integer',
    ];

    protected $appends = ['gambar_url'];

    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class);
    }

    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar_path
            ? Storage::disk('minio')->url($this->gambar_path)
            : null;
    }
}
