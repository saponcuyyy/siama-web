<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semester';

    protected $fillable = ['nama', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
