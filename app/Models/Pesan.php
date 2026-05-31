<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{
    use HasHashId;

    protected $table = 'pesan';

    protected $fillable = ['nama', 'email', 'telepon', 'subjek', 'pesan', 'status', 'balasan', 'dibalas_at', 'ip_address'];

    protected $casts = ['dibalas_at' => 'datetime'];
}
