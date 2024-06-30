<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Driver extends Model
{
    use HasFactory;
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'no_whatsapp',
        'img_profil'
    ];
    protected $appends = ['img_url'];

   
    /**
     * Boot function from laravel.
     */
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');

    }
    public function orders()
    {
        return $this->hasMany(Order::class,'driver_id','id');
    }

    public function getTimeDifferenceAttribute()
    {
        $timeOne = Carbon::parse($this->time_on);
        $now = Carbon::now();
        $differenceInMinutes = $timeOne->diffInMinutes($now);
        return $differenceInMinutes / 60;
    }
    public function getImgUrlAttribute()
    {
        return $this->img_profil ? url('storage/image/driver/' . $this->img_profil) : null;
    }
}
