<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Soal extends Model
{
    use HasHashId;
    use SoftDeletes;

    protected $table = 'soal';

    protected $fillable = [
        'bank_soal_id', 'tipe', 'pertanyaan', 'bobot',
        'tingkat_kesulitan', 'bab', 'indikator',
        'kunci_jawaban', 'pembahasan', 'gambar_path', 'urutan',
    ];

    protected $hidden = [
        'kunci_jawaban',    // jangan expose ke frontend siswa
        'pembahasan',       // jangan expose sebelum ujian selesai
    ];

    protected $casts = [
        'bobot' => 'integer',
    ];

    protected $appends = ['gambar_url'];

    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(BankSoal::class);
    }

    public function pilihanJawaban(): HasMany
    {
        return $this->hasMany(PilihanJawaban::class);
    }

    public function pasanganMenjodohkan(): HasMany
    {
        return $this->hasMany(PasanganMenjodohkan::class);
    }

    // Accessor URL gambar soal — via proxy route Laravel (tidak bergantung hostname MinIO)
    public function getGambarUrlAttribute(): ?string
    {
        if (! $this->gambar_path) {
            return null;
        }

        // Hapus prefix 'soal-images/' agar cocok dengan route /media/soal/{path}
        $relativePath = ltrim(str_replace('soal-images/', '', $this->gambar_path), '/');

        return url('/media/soal/'.$relativePath);
    }

    // Scope filter per tipe
    public function scopeTipe($query, string $tipe)
    {
        return $query->where('tipe', $tipe);
    }
}
