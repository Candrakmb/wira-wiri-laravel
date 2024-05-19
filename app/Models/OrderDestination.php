<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDestination extends Model
{
    use HasFactory;
    protected $fillable = ['order_id','tipe_destination','alamat_detail','latitude','longitude'];

    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }
}
