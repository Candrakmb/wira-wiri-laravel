<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuDetail extends Model
{
    use HasFactory;
    protected $fillable = ['kategori_pilih_menu_id ','nama_pilihan','stok','harga','status'];
    protected $appends = ['harga_formatted'];

   
    public function kategoriPilihan()
    {
        return $this->belongsTo(Menu::class,'kategori_pilih_menu_id');
    }
    
    public function getHargaFormattedAttribute()
    {
        if($this->harga == 0) {
            return 'gratis';
        }else {
            return 'Rp' . number_format($this->harga, 0, ',', '.');
        }
    }
}
