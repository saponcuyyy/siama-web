<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasHashId;

    protected $table = 'semester';

    protected $fillable = ['nama', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
