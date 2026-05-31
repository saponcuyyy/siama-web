<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaketUjian extends Model
{
    use HasHashId;
    use SoftDeletes;

    protected $table = 'paket_ujian';

    protected $fillable = [
        'mata_pelajaran_id',
        'guru_id',
        'tahun_ajaran_id',
        'semester_id',
        'dibuat_oleh',
        'nama',
        'kode',
        'deskripsi',
        'jenis',
        'tingkat',
        'durasi_menit',
        'jumlah_soal_pg',
        'jumlah_soal_bs',
        'jumlah_soal_menjodohkan',
        'jumlah_soal_essay',
        'nilai_maksimal',
        'acak_soal',
        'acak_jawaban',
        'petunjuk',
        'status',
    ];

    protected $casts = [
        'durasi_menit' => 'integer',
        'jumlah_soal_pg' => 'integer',
        'jumlah_soal_bs' => 'integer',
        'jumlah_soal_menjodohkan' => 'integer',
        'jumlah_soal_essay' => 'integer',
        'nilai_maksimal' => 'integer',
        'acak_soal' => 'boolean',
        'acak_jawaban' => 'boolean',
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

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function soal(): BelongsToMany
    {
        return $this->belongsToMany(Soal::class, 'paket_soal')
            ->withPivot('urutan', 'bobot_override')
            ->withTimestamps();
    }

    public function sesiUjian(): HasMany
    {
        return $this->hasMany(SesiUjian::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
