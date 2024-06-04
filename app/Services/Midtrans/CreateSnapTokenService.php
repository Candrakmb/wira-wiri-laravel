<?php

namespace App\Services\Midtrans;

use App\Models\Menu;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Midtrans\Snap;

class CreateSnapTokenService extends Midtrans
{
    protected $order;

    public function __construct($order)
    {
        parent::__construct();

        $this->order = $order;
    }

    public function getSnapToken()
    {
        $user = auth()->guard('api')->user();

        // Ambil data item dari tabel order_detail
        $orderDetails = OrderDetail::where('order_id', $this->order->id)->get();
        
        // Inisialisasi total gross_amount
        $itemDetails = [];

        // Bangun array item_details dan hitung totalPay
        foreach ($orderDetails as $detail) {
            $menu = Menu::where('id',$detail->menu_id)->first();
            $itemDetails[] = [
                'id' => $detail->menu_id,  
                'name' => $menu->nama, 
                'price' => $menu->harga, 
                'quantity' => $detail->qty 
            ];
        }
        $totalPay = $this->order->total_pay;
        $adminBank = 5000; 
        $totalMidtrans = $totalPay + $adminBank;

        $params = [
            'transaction_details' => [
                'order_id' => $this->order->invoice_number,
                'gross_amount' => $totalMidtrans,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ]
        ];

        $snapToken = Snap::getSnapToken($params);

        return $snapToken;
    }
}
