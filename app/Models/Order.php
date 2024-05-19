<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['pelanggan_id','driver_id','invoice_number','total_pay','status_pembayaran','metode_pembayaran','paid_at','snap_token','subtotal','status_order'];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class,'pelanggan_id');
    }
    public function driver()
    {
        return $this->belongsTo(Driver::class,'driver');
    }
}
