<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Page extends Model {
    use SoftDeletes;
    protected $fillable = ['title','slug','content','meta_title','meta_description','status','created_by'];

    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->slug = $m->slug ?: Str::slug($m->title));
    }
    public function author() { return $this->belongsTo(User::class, 'created_by'); }
}
