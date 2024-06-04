<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['pelanggan_id','driver_id','invoice_number','total_pay','status_pembayaran','metode_pembayaran','paid_at','snap_token','subtotal','status_order','ongkir'];
    protected $appends = ['pembayaran','order_status'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->invoice_number = self::generateInvoiceNumber();
        });
    }

    public static function generateInvoiceNumber()
    {
        $latestInvoice = self::orderBy('invoice_number', 'desc')->first();
        $number = $latestInvoice ? intval(substr($latestInvoice->invoice_number, -5)) + 1 : 1;
        // dd($number);
        return 'F-' . date('mY') . str_pad($number, 5, '0', STR_PAD_LEFT);
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
            '1' => '<div class="badge rounded-pill bg-primary">Antar</div>',
            '2' => '<div class="badge rounded-pill bg-success">Selesai</div>',
            '3' => '<div class="badge rounded-pill bg-danger">Batal</div>'
        ];
        return $status_order[$this->status_order];
    }
}
