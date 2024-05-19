<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriPilihMenu extends Model
{
    use HasFactory;
    protected $fillable = ['menu_id','nama','opsi '];

    public function Menu()
    {
        return $this->belongsTo(Menu::class,'menu_id');
    }
}
