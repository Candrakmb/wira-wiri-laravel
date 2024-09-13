<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $fillable = ['content','sender_id','receiver_id','is_read','order_id'];
    protected $appends = ['time'];

    public function sender()
    {
        return $this->belongsTo(User::class,'sender_id');
    }
    public function receiver()
    {
        return $this->belongsTo(User::class,'receiver_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }

    public function getTimeAttribute()
    {
        return waktu($this->created_at);
    }
}
