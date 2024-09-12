<?php

namespace App\Http\Controllers\Api;

use App\Events\PosisiDriver;
use App\Events\StatusOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Menu;
use App\Models\MenuDetail;
use App\Models\Order;
use App\Models\OrderDestination;
use App\Models\OrderDetail;
use App\Models\OrderDetailEkstra;
use App\Models\Pelanggan;
use App\Services\Midtrans\CreateSnapTokenService;
use Carbon\Carbon;
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
            // $orderDetail = OrderDetail::with(['orderEkstra','orderEkstra.menuDetail','orderEkstra.menuDetail.kategoriPilihan','menu'])->where('order_id',$order->id)->get();
            $orderDetails = OrderDetail::with(['menu'])->where('order_id', $order->id)->get();

            $dataOrder = [];
            foreach ($orderDetails as $detail) {
                $dataOrderEkstra = [];

                $orderEkstra = OrderDetailEkstra::with(['menuDetail','menuDetail.kategoriPilihan'])
                    ->where('order_detail_id', $detail->id)
                    ->get();
                if($orderEkstra){
                    foreach ($orderEkstra as $ekstra) {
                        $dataOrderEkstra[] = [
                            'nama_kategori' => $ekstra->menuDetail->kategoriPilihan->nama,
                            'nama_pilihan' => $ekstra->menuDetail->nama_pilihan,
                            'price_ekstra' => $ekstra->menuDetail->harga,
                        ];
                    }
                }

                $dataOrder[] = [
                    'catatan' => $detail->catatan,
                    'kedai_id'=> $detail->menu->kedai_id,
                    'price' => $detail->price,
                    'qty' => $detail->qty,
                    'menu' => $detail->menu->nama,
                    'ekstra' => $dataOrderEkstra,
                ];
            }

            $orderDestination = OrderDestination::where('order_id',$order->id)->get();
            if ($orderDestination) {
                // Load only the non-empty relationships
                $orderDestination->load(['alamatPelanggan', 'kedai','kedai.user']);

                // Filter out empty relationships
                $filteredDestinasi = $orderDestination->toArray();
                $filteredDestinasi = array_filter($filteredDestinasi, function($value, $key) {
                    return !is_array($value) || count($value) > 0;
                }, ARRAY_FILTER_USE_BOTH);
            }
            return response()->json([
                'success' => true,
                'order' => $order,
                'pelanggan' => $pelanggan,
                'driver' => $driver,
                'order_detail'=> $dataOrder,
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
            'kedai_two' => 'required',
            'start_ongkir' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['title' => 'Error', 'icon' => 'error', 'text' => 'Validasi gagal. ' . $validator->errors()->first(), 'ButtonColor' => '#EF5350', 'type' => 'error'], 400);
        }

        $admin = 0;
        if ($request->pembayaran == 'midtrans') {
            $admin += 5000;
        }
        if ($request->kedai_two) {
            $admin += 1000;
        }

        $dataMenu = $request->only(['menu_id', 'catatan', 'qty', 'price', 'key', 'key_id']);
        $dataEkstra = $request->only(['menu_detail_id']);
        $subtotal = 0;

        foreach ($dataMenu['menu_id'] as $index => $menuId) {
            $menus = Menu::find($menuId);
            $subtotal += $menus->harga * $dataMenu['qty'][$index];

            foreach ($dataEkstra['menu_detail_id'] as $ekstraIndex => $menuDetailId) {
                if ($dataMenu['key_id'][$ekstraIndex] == $dataMenu['key'][$index]) {
                    $menuEkstra = MenuDetail::find($menuDetailId);
                    $subtotal += $menuEkstra->harga * $dataMenu['qty'][$index];
                }
            }
        }

        // Hitung ongkir
        $startOngkir = $request->start_ongkir;
        if ($subtotal <= 10000) {
            $ongkir = $startOngkir;
        } else {
            $stateRangeOngkir = (int)(ceil($subtotal / 10000)) * 10000;
            if ($subtotal > 90000) {
                $stateRangeOngkir /= 10000;
            } else {
                $stateRangeOngkir = str_replace('0', '', (string)$stateRangeOngkir);
            }
            $ongkir = ($stateRangeOngkir - 1) * 1000;
            $ongkir += $startOngkir;
        }

        // Menghitung total akhir
        $total = $subtotal + $ongkir + $admin;

        // dd($total , $subtotal , $ongkir , $admin);
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
                        'total_pay' => $total,
                        'status_pembayaran' => $status_pembayaran,
                        'metode_pembayaran' => $metode_pembayaran,
                        'subtotal' => $subtotal,
                        'ongkir' => $ongkir + $admin,
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
            $dataMenu = $request->only(['menu_id', 'catatan', 'qty', 'price', 'key', 'key_id']);
            $dataEkstra = $request->only(['menu_detail_id']);
            // dd($dataEkstra);
            foreach ($dataMenu['menu_id'] as $menuIndex => $menuId) {
                // Buat OrderDetail untuk setiap menu
                $orderDetail = OrderDetail::create([
                    'order_id' => $order->id,
                    'menu_id' => $menuId,
                    'catatan' => $dataMenu['catatan'][$menuIndex] ?? null, // Gunakan null jika tidak ada catatan
                    'qty' => $dataMenu['qty'][$menuIndex],
                    'price' => $dataMenu['price'][$menuIndex],
                ]);

                // Tambahkan OrderDetailEkstra yang sesuai
                foreach ($dataEkstra['menu_detail_id'] as $ekstraIndex => $menuDetailId) {
                    // Cek jika key_id cocok dengan key
                    if (isset($dataMenu['key_id'][$ekstraIndex]) && $dataMenu['key_id'][$ekstraIndex] == $dataMenu['key'][$menuIndex]) {
                        OrderDetailEkstra::create([
                            'order_detail_id' => $orderDetail->id,
                            'menu_detail_id' => $menuDetailId,
                        ]);
                    }
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

    public function updateStatusOrder(Request $request){
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required',
            'status_order' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false,'title' => 'Error', 'icon' => 'error', 'text' => 'Validasi gagal. ' . $validator->errors()->first(), 'ButtonColor' => '#EF5350', 'type' => 'error'], 400);
        }
        DB::beginTransaction();
        try {
            $order = Order::where('invoice_number',$request->invoice_number)->first();
            if($order){
                $order->status_order = $request->status_order;
                $order->save();
            }

            if($request->status_order == 6){
                $driver = Driver::where('id', $order->driver_id)->first();
                $driver->status = 1;
                $driver->time_on = Carbon::now();
                $driver->save();
            } else {
                $driver = null;
            }

            broadcast(new StatusOrder($order))->toOthers();
            DB::commit();
            return response()->json([
                'success' => true,
                'data_order' => $order,
                'driver' => $driver
            ], 200);
        } catch ( \Exception $e){
            DB::rollback();
            return response()->json([
                'success' => false,
                'text' => $e->getMessage(),
            ], 500);
        }
    }

    public function addDriverOrder(Request $request){
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false,'title' => 'Error', 'icon' => 'error', 'text' => 'Validasi gagal. ' . $validator->errors()->first(), 'ButtonColor' => '#EF5350', 'type' => 'error'], 400);
        }
        $order = Order::where('invoice_number',$request->invoice_number)->first();
        if($order){
            $userAuth = $userAuth = auth()->guard('api')->user();

            if($userAuth->getRoleNames()->contains('driver')){
                $driver = Driver::where('user_id',$userAuth->id)->first();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "tidak memiliki akses"
                ], 401);
            }
            DB::beginTransaction();
            try {
                $order->status_order = 1;
                $order->driver_id = $driver->id;
                $order->save();

                $driver->status = 2;
                $driver->save();

                $order->load(['driver','driver.user']);
                // dd($order);
                broadcast(new StatusOrder($order))->toOthers();
                DB::commit();
                return response()->json([
                    'success' => true,
                    'data_order' => $order,
                    'driver' => $driver
                ], 200);
            } catch(\Exception $e) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'text' => $e->getMessage(),
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'data order tidak ditemukan'
            ], 404);
        }
    }

    public function positionDriver(Request $request){
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
            'invoice_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false,'title' => 'Error', 'icon' => 'error', 'text' => 'Validasi gagal. ' . $validator->errors()->first(), 'ButtonColor' => '#EF5350', 'type' => 'error'], 200);
        }
        DB::beginTransaction();
        $order = Order::where('invoice_number',$request->invoice_number)->first();
        $userAuth = $userAuth = auth()->guard('api')->user();
        if($userAuth->getRoleNames()->contains('driver')){
            $driver = Driver::where('user_id',$userAuth->id)->first();
        } else {
            return response()->json([
                'success' => false,
                'message' => "tidak memiliki akses"
            ], 401);
        }

        try {
            $driver->latitude = $request->latitude;
            $driver->longitude = $request->longitude;
            $driver->save();
            $driver->load(['user']);
            if($order->status_order == 6){
                broadcast(new PosisiDriver($driver))->toOthers();
            }
            DB::commit();
            return response()->json([
                    'success' => true,
                    'driver' => $driver
            ], 200);
        } catch (\Exception $e){
            DB::rollback();
            return response()->json([
                'success' => false,
                'text' => $e->getMessage(),
            ], 500);
        }
    }
}
