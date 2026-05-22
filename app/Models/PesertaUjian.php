<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo, HasMany
};

class PesertaUjian extends Model
{
    protected $table = 'peserta_ujian';

    protected $fillable = [
        'sesi_ujian_id','siswa_id','status',
        'mulai_at','selesai_at','sisa_detik',
        'device_token','ip_address','browser',
        'jumlah_pelanggaran','urutan_soal','urutan_jawaban',
        'nilai_pg','nilai_bs','nilai_menjodohkan',
        'nilai_essay','nilai_akhir',
        'sudah_dikoreksi','essay_sudah_dinilai',
    ];

    protected $casts = [
        'mulai_at'            => 'datetime',
        'selesai_at'          => 'datetime',
        'urutan_soal'         => 'array',
        'urutan_jawaban'      => 'array',
        'sudah_dikoreksi'     => 'boolean',
        'essay_sudah_dinilai' => 'boolean',
    ];

    // Hidden fields — jangan expose ke response API siswa
    protected $hidden = ['device_token','urutan_soal','urutan_jawaban'];

    public function sesiUjian(): BelongsTo
    {
        return $this->belongsTo(SesiUjian::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jawabanSiswa(): HasMany
    {
        return $this->hasMany(JawabanSiswa::class);
    }

    public function logUjian(): HasMany
    {
        return $this->hasMany(LogUjian::class, 'peserta_ujian_id');
    }

    // Hitung sisa waktu real-time (detik)
    public function getSisaWaktuAttribute(): int
    {
        if (!$this->mulai_at) return 0;

        $durasi = $this->sesiUjian->paketUjian->durasi_menit * 60;
        $terpakai = now()->diffInSeconds($this->mulai_at);
        $sisaDariDurasi = max(0, $durasi - $terpakai);

        // Batasi dengan waktu_selesai sesi sebagai batas mutlak
        if ($this->sesiUjian->waktu_selesai && now()->lt($this->sesiUjian->waktu_selesai)) {
            $sisaDariSesi = now()->diffInSeconds($this->sesiUjian->waktu_selesai);
            return min($sisaDariDurasi, $sisaDariSesi);
        }

        return $sisaDariDurasi;
    }

    // Apakah siswa sedang aktif mengerjakan di device lain?
    public function isSesiAktifDiDevice(string $deviceToken): bool
    {
        return $this->status === 'mengerjakan'
            && $this->device_token
            && $this->device_token !== $deviceToken;
    }
}
