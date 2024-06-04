<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetailEkstra extends Model
{
    use HasFactory;
    protected $fillable = ['order_detail_id','nama_ekstra','menu_detail_id'];

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class,'order_detail_id');
    }
    public function menuDetail()
    {
        return $this->belongsTo(MenuDetail::class,'menu_detail_id');
    }
}
