<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pesan extends Model {
    protected $table = 'pesan';
    protected $fillable = ['nama','email','telepon','subjek','pesan','status','balasan','dibalas_at','ip_address'];
    protected $casts = ['dibalas_at' => 'datetime'];
}
