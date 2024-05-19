<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    protected $fillable = ['kedai_id','nama','gambar','deskripsi','status','harga','kategori_id'];

    public function Kategori()
    {
        return $this->belongsTo(Kategori::class,'kategori_id');
    }
    public function Kedai()
    {
        return $this->belongsTo(Kedai::class,'kedai_id');
    }
    public function kategori_menu()
    {
        return $this->hasMany(KategoriPilihan::class,'menu_id','id');
    }

}
