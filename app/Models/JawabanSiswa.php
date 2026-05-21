<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanSiswa extends Model
{
    protected $table = 'jawaban_siswa';

    protected $fillable = [
        'peserta_ujian_id',
        'soal_id',
        'jawaban',
        'jawaban_menjodohkan',
        'is_benar',
        'nilai',
        'catatan_guru',
        'dinilai_oleh',
        'dinilai_at',
        'dijawab_at',
        'durasi_detik',
    ];

    protected $casts = [
        'jawaban_menjodohkan' => 'array',
        'is_benar' => 'boolean',
        'nilai' => 'float',
        'dinilai_at' => 'datetime',
        'dijawab_at' => 'datetime',
        'durasi_detik' => 'integer',
    ];

    public function pesertaUjian(): BelongsTo
    {
        return $this->belongsTo(PesertaUjian::class);
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class);
    }

    public function dinilaiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }
}
