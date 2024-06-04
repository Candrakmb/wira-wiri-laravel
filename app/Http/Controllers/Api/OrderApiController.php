<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use App\Models\OrderDestination;
use App\Models\OrderDetail;
use App\Models\OrderDetailEkstra;
use App\Models\Pelanggan;
use App\Services\Midtrans\CreateSnapTokenService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderApiController extends Controller
{

    public function show_midtrans($id){
        $order = Order::find($id);
        $snapToken = $order->snap_token;
         if (is_null($snapToken)) {
            $midtrans = new CreateSnapTokenService($order);
            $snapToken = $midtrans->getSnapToken();

            $order->snap_token = $snapToken;
            $order->save();
         }

         return view('midtrans.show', compact('order', 'snapToken'));
    }
    public function data_order($invoice){
        $order = Order::where('invoice_number',$invoice)->first();
       
        if ($order) {
            if($order->driver_id != null) {
                $driver =  Driver::select('id', 'user_id', 'no_whatsapp','no_plat','latitude','longitude')
                ->with(['user' => function($query) {
                    $query->select('id', 'name', 'email');
                }])
                ->where('id', $order->driver_id)
                ->first();
            }else {
                $driver= null;
            }
            $pelanggan = Pelanggan::select('id', 'user_id', 'no_whatsapp')
                ->with(['user' => function($query) {
                    $query->select('id', 'name', 'email');
                }])
                ->where('id', $order->pelanggan_id)
                ->first();
            $orderDetail = OrderDetail::with(['orderEkstra','menu'])->where('order_id',$order->id)->get();
            $orderDestination = OrderDestination::where('order_id',$order->id)->get();
            if ($orderDestination) {
                // Load only the non-empty relationships
                $orderDestination->load(['alamatPelanggan', 'kedai']);
        
                // Filter out empty relationships
                $filteredDestinasi = $orderDestination->toArray();
                $filteredDestinasi = array_filter($filteredDestinasi, function($value, $key) {
                    return !is_array($value) || count($value) > 0;
                }, ARRAY_FILTER_USE_BOTH);
            }
            return response()->json([
                'success' => true,
                'pelanggan' => $pelanggan,
                'driver' => $driver,
                'order_detail'=> $orderDetail,
                'order_destination'=> $filteredDestinasi,
            ], 200);
        } else {
            // Kembalikan respon JSON dengan pesan error jika user tidak ditemukan
            return response()->json([
                'success' => false,
                'message' => 'order not found',
            ], 404);
        }
    }
    
    public function create_order(Request $request){
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'total_pay' => 'required',
            'subtotal' => 'required',
            'ongkir' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['title' => 'Error', 'icon' => 'error', 'text' => 'Validasi gagal. ' . $validator->errors()->first(), 'ButtonColor' => '#EF5350', 'type' => 'error'], 400);
        }

        $userAuth = auth()->guard('api')->user();
        $pelanggan = Pelanggan::where('user_id',$userAuth->id)->first();
        if($request->pembayaran == 'midtrans'){
            $metode_pembayaran = '1';
            $status_pembayaran = '0';
            $status_order = null;
        }else{
            $metode_pembayaran = '0';
            $status_pembayaran = null;
            $status_order = '0';
        }
        DB::beginTransaction();
         try {
            
            $order = Order::create([
                        'pelanggan_id' => $pelanggan->id,
                        'total_pay' => $request->total_pay,
                        'status_pembayaran' => $status_pembayaran,
                        'metode_pembayaran' => $metode_pembayaran,
                        'subtotal' => $request->subtotal,
                        'ongkir' => $request->ongkir,
                        'status_order' => $status_order,
                    ]);

            $data = $request->only(['tipe_destinasi', 'kedai_id', 'alamat_pelanggan_id']);
            // dd($data);
            // Loop melalui setiap elemen tipe_destinasi
            foreach ($data['tipe_destinasi'] as $key => $value) {
                if ($value == 'tujuan' ) {
                    OrderDestination::create([
                        'order_id' => $order->id,
                        'tipe_destination' => '0',
                        'kedai_id' => $data['kedai_id'][$key],
                        'alamat_pelanggan_id' =>  $data['alamat_pelanggan_id'][$key],
                    ]);
                } elseif ($value == 'kedai') {
                    OrderDestination::create([
                        'order_id' => $order->id,
                        'tipe_destination' => '1',
                        'kedai_id' => $data['kedai_id'][$key],
                        'alamat_pelanggan_id' => $data['alamat_pelanggan_id'][$key],
                    ]);
                }
            }
            $dataMenu = $request->only(['menu_id', 'catatan', 'qty','price']);
            foreach ($dataMenu['menu_id'] as $menu => $value) {
                $orderDetail = OrderDetail::create([
                                'order_id' => $order->id,
                                'menu_id' => $dataMenu['menu_id'][$menu],
                                'catatan' => $dataMenu['catatan'][$menu],
                                'qty' => $dataMenu['qty'][$menu],
                                'price' => $dataMenu['price'][$menu],
                            ]);

                $dataEkstra = $request->only(['menu_id_ekstra', 'nama_ekstra', 'menu_detail_id']);
                foreach ($dataEkstra['nama_ekstra'] as $ekstra => $value) {
                    if ($dataEkstra['menu_id_ekstra'][$ekstra] == $dataMenu['menu_id'][$menu]) {  
                        OrderDetailEkstra::create([
                        'order_detail_id' => $orderDetail->id,
                        'nama_ekstra' => $dataEkstra['nama_ekstra'][$ekstra],
                        'menu_detail_id' => $dataEkstra['menu_detail_id'][$ekstra],
                    ]);}
                }
            }
            if ($order){
                if($request->pembayaran == 'midtrans'){
                    $midtrans = new CreateSnapTokenService($order);
                    $snapToken = $midtrans->getSnapToken();
        
                    $order->snap_token = $snapToken;
                    $order->save();
                }
            }
            DB::commit();
            return response()->json([
                'title' => 'Success!',
                'icon' => 'success',
                'text' => 'Data Berhasil Ditambah!',
                'ButtonColor' => '#66BB6A',
                'type' => 'success',
                'data_order' => $order
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'text' => $e->getMessage(),
                'ButtonColor' => '#EF5350',
                'type' => 'error'
            ], 500);
        }
    }
}
