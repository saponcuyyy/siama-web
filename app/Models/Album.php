<?php

namespace App\Models;

use App\Traits\HasHashId;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Album extends Model
{
    use HasHashId;
    use HasFactory, SoftDeletes;

    protected $fillable = ['nama', 'slug', 'deskripsi', 'cover', 'status'];

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
                isset($attributes['cover']) 
                    ? (str_starts_with($attributes['cover'], 'http') 
                        ? $attributes['cover'] 
                        : Storage::disk('public')->url($attributes['cover']))
                    : null,
        );
    }

    public function galeri()
    {
        return $this->hasMany(Galeri::class);
    }
}
