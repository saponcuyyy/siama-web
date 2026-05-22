<?php
namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo, HasMany
};
use Illuminate\Support\Facades\Storage;

class Soal extends Model
{
    use HasHashId;
    use SoftDeletes;

    protected $table = 'soal';

    protected $fillable = [
        'bank_soal_id','tipe','pertanyaan','bobot',
        'tingkat_kesulitan','bab','indikator',
        'kunci_jawaban','pembahasan','gambar_path','urutan',
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
        return $this->hasMany(PilihanJawaban::class)->orderBy('urutan');
    }

    public function pasanganMenjodohkan(): HasMany
    {
        return $this->hasMany(PasanganMenjodohkan::class)->orderBy('urutan');
    }

    // Accessor URL gambar soal dari MinIO
    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar_path
            ? Storage::disk('minio')->url($this->gambar_path)
            : null;
    }

    // Scope filter per tipe
    public function scopeTipe($query, string $tipe)
    {
        return $query->where('tipe', $tipe);
    }
}
