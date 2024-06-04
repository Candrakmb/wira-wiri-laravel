<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;
    protected $fillable = ['order_id','catatan','qty','menu_id','price'];

    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }
    public function menu()
    {
        return $this->belongsTo(Menu::class,'menu_id');
    }
    public function orderEkstra()
    {
        return $this->hasMany(OrderDetailEkstra::class,'order_detail_id','id');
    }
}
