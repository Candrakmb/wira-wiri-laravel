<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDestination extends Model
{
    use HasFactory;
    protected $fillable = ['order_id','tipe_destination','kedai_id','alamat_pelanggan_id'];

    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }
    public function kedai()
    {
        return $this->belongsTo(Kedai::class,'kedai_id');
    }
    public function alamatPelanggan()
    {
        return $this->belongsTo(AlamatPelanggan::class,'alamat_pelanggan_id');
    }
}
