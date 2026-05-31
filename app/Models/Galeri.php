<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Galeri extends Model
{
    use HasFactory, SoftDeletes;
    use HasHashId;

    protected $table = 'galeri';

    protected $fillable = [
        'album_id',
        'judul',
        'deskripsi',
        'file_path',
        'kategori',
        'urutan',
        'status',
        'created_by',
    ];

    protected $appends = ['image_url'];

    /**
     * Accessor untuk URL publik foto dari MinIO.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => isset($attributes['file_path'])
                    ? (str_starts_with($attributes['file_path'], 'http')
                        ? $attributes['file_path']
                        : Storage::disk('public')->url($attributes['file_path']))
                    : null,
        );
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
