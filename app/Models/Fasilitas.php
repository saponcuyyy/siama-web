<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Fasilitas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fasilitas';
    protected $fillable = ['nama', 'slug', 'deskripsi', 'foto', 'urutan', 'status'];

    protected $appends = ['image_url'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->nama);
            }
        });
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => 
                isset($attributes['foto']) 
                    ? (str_starts_with($attributes['foto'], 'http') 
                        ? $attributes['foto'] 
                        : Storage::disk('minio')->url($attributes['foto']))
                    : null,
        );
    }
}
