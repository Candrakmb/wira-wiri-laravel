<?php

namespace App\Models;

use App\Helpers\Haversine;
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
        'img',
    ];

    protected $appends = ['distance', 'img_url'];

    /**
     * Boot function from Laravel.
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
        return $this->belongsTo(User::class, 'user_id');
    }

    public function menu()
    {
        return $this->hasMany(Menu::class, 'kedai_id', 'id');
    }

    public function getDistanceAttribute()
    {
        $latitude = request()->input('latitude');
        $longitude = request()->input('longitude');

        if ($latitude && $longitude) {
            return Haversine::calculateDistance($latitude, $longitude, $this->latitude, $this->longitude);
        }

        return null;
    }
    // Accessor untuk URL gambar
    public function getImgUrlAttribute()
    {
        return $this->img ? url('storage/image/kedai/' . $this->img) : null;
    }



}
