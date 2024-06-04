<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Kedai extends Model
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
        'alamat',
        'latitude',
        'longitude',
        'status',    
    ];
    protected $appends = ['distance'];
    
   
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

    public function menu()
    {
        return $this->hasMany(Menu::class,'kedai_id','id');
    }
    public function getDistanceAttribute()
{
    // Mengembalikan nilai default jika 'distance' tidak ada
    return $this->attributes['distance'] ?? null;
}

    public function setDistanceAttribute($value)
    {
        $this->attributes['distance'] = $value;
    }
}
