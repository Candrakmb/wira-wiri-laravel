<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    protected $fillable = ['kedai_id','nama','gambar','deskripsi','status','harga','kategori_id'];
    protected $appends = ['img_url', 'has_custom', 'harga_formatted'];

    public function Kategori()
    {
        return $this->belongsTo(Kategori::class,'kategori_id');
    }
    public function Kedai()
    {
        return $this->belongsTo(Kedai::class,'kedai_id');
    }
    public function customOptions()
    {
        return $this->hasMany(KategoriPilihMenu::class,'menu_id','id');
    }
    public function getHasCustomAttribute()
    {
        return $this->customOptions()->exists();
    }

    public function getHargaFormattedAttribute()
    {
        return 'Rp' . number_format($this->harga, 0, ',', '.');
    }
    public function getImgUrlAttribute()
    {
        return $this->gambar ? url('storage/image/menu/' . $this->gambar) : null;
    }
}
