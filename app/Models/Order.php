<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['pelanggan_id','driver_id','invoice_number','total_pay','status_pembayaran','metode_pembayaran','paid_at','snap_token','subtotal','status_order','ongkir'];
    protected $appends = ['pembayaran','order_status', 'order_time'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->invoice_number = self::generateInvoiceNumber();
        });
    }

    public static function generateInvoiceNumber()
    {
        return 'F-' . date('mY') . '-' . time() . '-' . rand(1000, 9999);
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class,'pelanggan_id');
    }
    public function driver()
    {
        return $this->belongsTo(Driver::class,'driver_id');
    }
    public function orderDestinasi()
    {
        return $this->hasMany(OrderDestination::class,'order_id','id');
    }
    public function getPembayaranAttribute()
    {
        $metode_pembayaran = [
            '0' => '<div class="badge rounded-pill bg-primary">Tunai</div>',
            '1' => '<div class="badge rounded-pill bg-primary">Midatrans</div>',
        ];
        return $metode_pembayaran[$this->metode_pembayaran];
    }
    public function getOrderStatusAttribute()
    {
        $status_order = [
            '0' => '<div class="badge rounded-pill bg-info">Proses</div>',
            '1' => '<div class="badge rounded-pill bg-primary">Mendapatkan Driver</div>',
            '2' => '<div class="badge rounded-pill bg-success">OTW Kedai</div>',
            '3' => '<div class="badge rounded-pill bg-success">OTW Kedai Pertama</div>',
            '4' => '<div class="badge rounded-pill bg-success">OTW Kedai Kedua</div>',
            '5' => '<div class="badge rounded-pill bg-success">Sampai Resto</div>',
            '6' => '<div class="badge rounded-pill bg-success">Mengantarkan Makanan</div>',
            '7' => '<div class="badge rounded-pill bg-success">Selesai</div>',
            '8' => '<div class="badge rounded-pill bg-danger">Batal</div>',
        ];

        if (isset($status_order[$this->status_order])) {
            return $status_order[$this->status_order];
        } else {
            return '<div class="badge rounded-pill bg-secondary">Unknown</div>'; // Atau status default lainnya
        }
    }
    public function getOrderTimeAttribute()
    {
        return Carbon::parse($this->created_at)->translatedFormat('d M, H.i');
    }

}
