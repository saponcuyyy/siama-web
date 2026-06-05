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
        if (! $this->gambar_path) {
            return null;
        }

        // Hapus prefix 'soal-images/' agar cocok dengan route /media/soal/{path}
        $relativePath = ltrim(str_replace('soal-images/', '', $this->gambar_path), '/');

        return url('/media/soal/'.$relativePath);
    }
}
