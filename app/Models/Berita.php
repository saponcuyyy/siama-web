<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Berita extends Model
{
    use HasHashId;
    use HasFactory, SoftDeletes;

    protected $table = 'berita';

    protected $fillable = [
        'judul',
        'slug',
        'kategori_id',
        'ringkasan',
        'konten',
        'tags',
        'thumbnail',
        'status',
        'published_at',
        'created_by'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'tags' => 'array',
    ];

    protected $appends = ['image_url'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->judul);
            }
        });
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => 
                isset($attributes['thumbnail']) 
                    ? (str_starts_with($attributes['thumbnail'], 'http') 
                        ? $attributes['thumbnail'] 
                        : Storage::disk('public')->url($attributes['thumbnail']))
                    : null,
        );
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriBerita::class, 'kategori_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
