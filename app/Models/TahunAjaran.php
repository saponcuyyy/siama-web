<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjaran extends Model
{
    use HasHashId;

    protected $table = 'tahun_ajaran';

    protected $fillable = ['nama', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function rombel(): HasMany
    {
        return $this->hasMany(Rombel::class);
    }
}
