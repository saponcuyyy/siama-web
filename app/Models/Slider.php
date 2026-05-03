<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Slider extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;
    protected $fillable = ['judul','subjudul','file_path','link_url','link_text','urutan','status'];

    protected $appends = ['image_url'];

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => 
                isset($attributes['file_path']) 
                    ? (str_starts_with($attributes['file_path'], 'http') 
                        ? $attributes['file_path'] 
                        : Storage::disk('minio')->url($attributes['file_path']))
                    : null,
        );
    }
}
