<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuDetail extends Model
{
    use HasFactory;
    protected $fillable = ['kategori_pilih_menu_id ','nama_pilihan','stok','harga','status'];

   
    public function kategoriPilihan()
    {
        return $this->belongsTo(Menu::class,'kategori_pilih_menu_id');
    }
}
