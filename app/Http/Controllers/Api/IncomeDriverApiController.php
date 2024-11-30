<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SpomkyLabs\Pki\ASN1\Component\Length;

class IncomeDriverApiController extends Controller
{
    public function index(Request $request)
    {
        $user =  auth()->guard('api')->user();
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        if($startDate == null || $endDate == null){
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth(); 
        }

        if (!$user) {
            return response()->json(['success' => false,'message'=>'Unauthenticated'],401);
        }

        if ($user->getRoleNames()->contains('driver')) {
            $driver = Driver::where('user_id', $user->id)->first();
            $order = Order::with(['pelanggan','pelanggan.user'])->where('driver_id', $driver->id)->where('status_order', '7')->whereBetween('created_at', [$startDate, $endDate])->get();

            $totalPendapatan = 0;
            $setor = 0;
            foreach ($order as $item) {
                $ongkir = $item->ongkir;

                // Kurangi 5000 jika metode pembayaran adalah 1
                if ($item->payment_method == 1) {
                    $ongkir -= 5000;
                }



                $totalPendapatan += $ongkir;
                $setor += 1500;
            }

            return response()->json(['success' => true, 'order'=> $order, 'total_pendapatan' => $totalPendapatan, 'total_setor' => $setor, 'startDate' => $startDate, 'endDate' => $endDate],200);

        } else {
            return response()->json(['success' => false,'message'=>'User not found'],404);
        }

    }
}
