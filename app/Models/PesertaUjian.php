<?php
namespace App\Models;

use App\Enums\PesertaStatus;
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

        $durasiMenit = $this->sesiUjian?->paketUjian?->durasi_menit ?? 0;
        $durasi = $durasiMenit * 60;
        if ($durasi <= 0) return 0;

        // Waktu terpakai = selisih mulai_at → now (positif karena mulai_at di masa lalu)
        $terpakai = max(0, $this->mulai_at->diffInSeconds(now(), false));
        $sisaDariDurasi = max(0, $durasi - $terpakai);

        // Batasi dengan waktu_selesai + toleransi_menit sebagai batas mutlak sesi
        if ($this->sesiUjian?->waktu_selesai) {
            $toleransiDetik = ($this->sesiUjian->toleransi_menit ?? 0) * 60;
            $batasEfektif = $this->sesiUjian->waktu_selesai->copy()->addSeconds($toleransiDetik);
            // Sisa sesi = selisih now → batasEfektif (positif selama ujian masih berlangsung)
            $sisaDariSesi = max(0, now()->diffInSeconds($batasEfektif, false));
            return min($sisaDariDurasi, $sisaDariSesi);
        }

        return $sisaDariDurasi;
    }

    // Apakah siswa sedang aktif mengerjakan di device lain?
    public function isSesiAktifDiDevice(string $deviceToken): bool
    {
        return $this->status === PesertaStatus::MENGERJAKAN->value
            && $this->device_token
            && $this->device_token !== $deviceToken;
    }
}
