<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueDriver extends Model
{
    use HasFactory;
    protected $fillable = ['driver_id','order_id','end_queue','delete_queue'];

    public function driver()
    {
        return $this->belongsTo(Driver::class,'driver_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }
}
