<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany};

class Guru extends Model
{
    use HasHashId, SoftDeletes;

    protected $table = 'guru';

    protected $fillable = ['user_id', 'nip', 'nama', 'jabatan', 'tanggal_lahir'];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bankSoal(): HasMany
    {
        return $this->hasMany(BankSoal::class);
    }

    public function paketUjian(): HasMany
    {
        return $this->hasMany(PaketUjian::class);
    }

    public function rombelWali(): HasMany
    {
        return $this->hasMany(Rombel::class, 'guru_id');
    }

    public function mataPelajarans(): BelongsToMany
    {
        return $this->belongsToMany(MataPelajaran::class, 'guru_mata_pelajaran')
            ->withTimestamps();
    }
}
