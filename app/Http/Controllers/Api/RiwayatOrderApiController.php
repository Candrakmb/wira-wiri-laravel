<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Kedai;
use App\Models\Order;
use App\Models\OrderDestination;
use App\Models\OrderDetail;
use App\Models\OrderDetailEkstra;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RiwayatOrderApiController extends Controller
{
    public function riwayatDriverPelanggan()
    {
        $user = auth()->guard('api')->user();
        if (!$user) {
            return response()->json(['success' => false],401);
        }

        try {
            $role = $user->getRoleNames()->contains('driver') ? 'driver' : 'pelanggan';

            if ($role) {
                $roleId = $role === 'driver' ? Driver::where('user_id', $user->id)->value('id') : Pelanggan::where('user_id', $user->id)->value('id');
                $orderKey = $role === 'driver' ? 'driver_id' : 'pelanggan_id';

                $orders = Order::with(['pelanggan','pelanggan.user','driver','driver.user'])->where($orderKey, $roleId)->orderBy('created_at', 'desc')->get();

                $dataOrder = $orders->flatMap(function ($order) {
                    return OrderDetail::with('menu')
                        ->where('order_id', $order->id)
                        ->get()
                        ->map(function ($detail) use ($order) {
                            $dataOrderEkstra = OrderDetailEkstra::with(['menuDetail', 'menuDetail.kategoriPilihan'])
                                ->where('order_detail_id', $detail->id)
                                ->get()
                                ->map(function ($ekstra) {
                                    return [
                                        'nama_kategori' => $ekstra->menuDetail->kategoriPilihan->nama,
                                        'nama_pilihan' => $ekstra->menuDetail->nama_pilihan,
                                        'price_ekstra' => $ekstra->menuDetail->harga,
                                    ];
                                });

                            return [
                                'invoice_number' => $order->invoice_number,
                                'catatan' => $detail->catatan,
                                'kedai_id' => $detail->menu->kedai_id,
                                'price' => $detail->price,
                                'qty' => $detail->qty,
                                'menu' => $detail->menu->nama,
                                'ekstra' => $dataOrderEkstra,
                            ];
                        });
                });


                $orderDestination = OrderDestination::whereIn('order_id', $orders->pluck('id'))->get();
                $filteredDestinasi = array_filter(
                    $orderDestination->load(['order','alamatPelanggan', 'kedai', 'kedai.user'])->toArray(),
                    fn($value) => !is_array($value) || count($value) > 0
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Data order berhasil diambil',
                'data' => [
                    'order' => $orders,
                    'menu' => $dataOrder,
                    'destination' => $filteredDestinasi,
                ],
            ], 200);
        } catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' =>  $e->getMessage(),
            ], 500);
        }
    }

    public function RiWayatKedai()
    {
        $user = auth()->guard('api')->user();

    }
}
