<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Siswa extends Model
{
    use HasHashId;
    use HasFactory, SoftDeletes;

    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'rombel_id',
        'nisn',
        'nama',
        'tanggal_lahir',
        'status_lulus',
        'keterangan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class);
    }
}
