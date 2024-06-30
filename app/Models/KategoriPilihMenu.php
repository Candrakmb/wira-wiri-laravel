<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriPilihMenu extends Model
{
    use HasFactory;
    protected $fillable = ['menu_id','nama','opsi','max_pilih'];

    public function Menu()
    {
        return $this->belongsTo(Menu::class,'menu_id');
    }

    public function menuDetail()
    {
        return $this->hasMany(MenuDetail::class,'kategori_pilih_menu_id','id');
    }

}
