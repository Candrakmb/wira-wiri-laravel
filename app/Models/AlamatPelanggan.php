<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlamatPelanggan extends Model
{
    use HasFactory;
    protected $fillable = ['pelanggan_id','alamat','tipe_alamat','detail_alamat','latitude','longitude'];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class,'pelanggan_id');
    } 

}
