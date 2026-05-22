<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengumuman extends Model
{
    use HasHashId;
    use HasFactory, SoftDeletes;

    protected $table = 'pengumuman';

    protected $fillable = [
        'judul',
        'konten',
        'lampiran',
        'prioritas',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'created_by'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
