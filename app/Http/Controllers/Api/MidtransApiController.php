<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;

class MidtransApiController extends Controller
{
    public function callback(Request $request)
    {
        // dd($request->all());
        try {
            $serverKey = config('midtrans.server_key');
            $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
            if ($hashed == $request->signature_key) {
                $order = Order::where('invoice_number', $request->order_id)->first();

                if ($order) {
                    if ($request->transaction_status == 'capture') {
                        if ($request->payment_type == 'credit_card') {
                            if ($request->fraud_status == 'challenge') {
                                $order->update(['status_pembayaran' => '0']);
                            } else {
                                $order->update(['status_pembayaran' => '1', 'paid_at' => Carbon::now(), 'status_order' => '0']);
                            }
                        }
                    }
                    else if ($request->transaction_status == 'settlement') {
                        $order->update(['status_pembayaran' => '1', 'paid_at' => Carbon::now(), 'status_order' => '0']);
                    } else if ($request->transaction_status == 'pending') {
                        $order->update(['status_pembayaran' => '0']);
                    } else if ($request->transaction_status == 'deny') {
                        $order->update(['status_pembayaran' => '4']);
                    } else if ($request->transaction_status == 'expire') {
                        $order->update(['status_pembayaran' => '5']);
                    } else if ($request->transaction_status == 'cancel') {
                        $order->update(['status_pembayaran' => '4']);
                    }

                    return response()
                        ->json([
                            'success' => true,
                            'message' => $order->invoice_number,
                        ]);
                } else {
                    return response()
                        ->json([
                            'success' => false,
                            'message' => 'Order not found',
                        ], 404);
                }
            } else {
                return response()
                    ->json([
                        'success' => false,
                        'message' => 'Invalid signature key',
                    ], 400);
            }
        } catch (\Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage(),
                ], 500);
        }
    }
}
