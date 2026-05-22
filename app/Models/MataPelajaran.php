<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    use HasHashId;
    use SoftDeletes;

    protected $table = 'mata_pelajaran';

    protected $fillable = ['nama', 'kode'];

    public function bankSoal(): HasMany
    {
        return $this->hasMany(BankSoal::class);
    }

    public function paketUjian(): HasMany
    {
        return $this->hasMany(PaketUjian::class);
    }
}
