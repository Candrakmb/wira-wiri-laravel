<?php

namespace App\Http\Controllers\Api;

use App\Events\NotifyDriver;
use App\Events\NotifyKedai;
use App\Events\PosisiDriver;
use App\Events\StatusOrder;
use App\Helpers\Haversine;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Kedai;
use App\Models\Menu;
use App\Models\MenuDetail;
use App\Models\Order;
use App\Models\OrderDestination;
use App\Models\OrderDetail;
use App\Models\OrderDetailEkstra;
use App\Models\Pelanggan;
use App\Models\User;
use App\Notifications\sendNotifToKedai;
use App\Notifications\StatusOrderNotification;
use App\Services\Midtrans\CreateSnapTokenService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

    public function getOrderProgress()
    {
        try {
            $userAuth = auth()->guard('api')->user();
            $order = null;

            switch (true) {
                case $userAuth->getRoleNames()->contains('driver'):
                    $driver = Driver::where('user_id', $userAuth->id)->first();
                    if (!$driver) {
                        return response()->json(['success' => false, 'text' => 'Driver tidak ditemukan'], 404);
                    }
                    $order = Order::with([
                        'pelanggan', 'pelanggan.user', 'driver', 'driver.user',
                    ])
                    ->where('driver_id', $driver->id)
                    ->whereBetween('status_order', [1,6])
                    ->whereDate('created_at', Carbon::today())
                    ->first();
                    break;

                case $userAuth->getRoleNames()->contains('user'):
                    $pelanggan = Pelanggan::where('user_id', $userAuth->id)->first();
                    if (!$pelanggan) {
                        return response()->json(['success' => false, 'text' => 'Pelanggan tidak ditemukan'], 404);
                    }
                    $order = Order::with([
                        'pelanggan', 'pelanggan.user', 'driver', 'driver.user',
                    ])
                    ->where('pelanggan_id', $pelanggan->id)
                    ->whereBetween('status_order', [1,6])
                    ->whereDate('created_at', Carbon::today())
                    ->first();
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'text' => 'Tidak memiliki akses',
                    ], 401);
            }

            return response()->json([
                'success' => (bool) $order,
                'order' => $order,
                'text' => $order ? 'Order ditemukan' : 'Order tidak ditemukan',
            ], $order ? 200 : 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'text' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function calculateOngkir(Request $request)
    {
        $request->validate([
            'kedai' => 'required', // Format: [lng, lat]
            'destination' => 'required|array', // Format: [lng, lat]
        ]);

        $getDataKedai = Kedai::find($request->kedai);

        $kedai = [ $getDataKedai->longitude, $getDataKedai->latitude];        // Contoh: [106.827153, -6.175110] (Jakarta)
        $destination = $request->input('destination'); // Contoh: [107.619122, -6.917464] (Bandung)
        $apiKey = env('ORS_API_KEY'); // Simpan API Key di .env

        // Panggil OpenRouteService Directions API
        // $response = Http::withHeaders([
        //     'Authorization' => $apiKey,
        //     'Content-Type' => 'application/json',
        // ])->post('https://api.openrouteservice.org/v2/directions/driving-car', [
        //     'coordinates' => [$kedai, $destination],
        //     'instructions' => false,
        // ]);

        $distanceKm = Haversine::calculateDistance($getDataKedai->longitude, $getDataKedai->latitude, $destination[0], $destination[1]);

        // if ($response->successful()) {
            // $data = $response->json();

            // // Ambil rincian jarak (dalam meter) dan ubah ke kilometer
            // $distanceMeters = $data['routes'][0]['summary']['distance'] ?? null;
            // $distanceKm = $distanceMeters ? round($distanceMeters / 1000) : null;

            // Perhitungan ongkir
            $baseRate = 7000; // Ongkir dasar untuk 5 km pertama
            $additionalRate = 3000; // Tambahan ongkir per km di atas 5 km
            $ongkir = 0;

            if ($distanceKm !== null) {
                if ($distanceKm <= 4) {
                    $ongkir = $baseRate;
                } else {
                    $extraDistance = $distanceKm - 4; // Jarak di atas 5 km
                    $ongkir = $baseRate + ceil($extraDistance) * $additionalRate;
                }
            }

            return response()->json([
                'success' => true,
                'distance_km' => $distanceKm,
                'ongkir' => $ongkir,
            ], 200);
        // }

        return response()->json([
            'success' => false,
            'error' => 'Failed to calculate distance',
            'response' => $response->json(),
        ], $response->status());
    }



    public function data_order($invoice){
        $order = Order::where('invoice_number',$invoice)->first();

        if ($order) {
            $userAuth = auth()->guard('api')->user();
            if($userAuth->getRoleNames()->contains('driver') && $order->driver_id != null){
                $cekDriver = Driver::where('user_id',$userAuth->id)->first();
                if($cekDriver->id == $order->driver_id){
                    $akses = true;
                }else{
                    $akses = false;
                }
            } else if($userAuth->getRoleNames()->contains('driver') && $order->driver_id == null){
                $akses = false;
            } else if ($userAuth->getRoleNames()->contains('user')) {
                $cekPelanggan = Pelanggan::where('user_id',$userAuth->id)->first();
                if($cekPelanggan->id == $order->pelanggan_id){
                    $akses = true;
                }else{
                    $akses = false;
                }
            } else {
                $akses = false;
            }

            if(!$akses){
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda tidak memiliki akses',
                ], 200);
            }

            if($order->driver_id != null) {
                $driver =  Driver::with(['user'])
                ->where('id', $order->driver_id)
                ->first();
            }else {
                $driver= null;
            }
            $pelanggan = Pelanggan::with(['user'])
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
            return response()->json(['success' => false, 'text' => 'Validasi gagal. ' . $validator->errors()->first()], 400);
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
            if($order->metode_pembayaran == 0){
                $serchingDriver = new WpApiController();
                $serchingDriver->weightProduct($order->invoice_number);
            }
            return response()->json([
                'success' => true,
                'data_order' => $order
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'text' => $e->getMessage(),
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

            if($request->status_order == 7){
                $driver = Driver::where('id', $order->driver_id)->first();
                $driver->status = 1;
                $driver->time_on = Carbon::now();
                $driver->save();
            } else {
                $driver = null;
            }
            $pelanggan = Pelanggan::where('id', $order->pelanggan_id)->first();
            $user = User::where('id', $pelanggan->user_id)->first();

            broadcast(new StatusOrder($order))->toOthers();
            $user->notify(new StatusOrderNotification($order));
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

                $driver->status = 3;
                $driver->save();

                $order->load(['driver','driver.user']);
                // dd($order);
                broadcast(new StatusOrder($order))->toOthers();
                DB::commit();

                $orderDestination = OrderDestination::where('order_id', $order->id)
                                    ->whereNotNull('kedai_id')
                                    ->get();
                $orderDestination->load(['kedai', 'kedai.user']);
                foreach ($orderDestination as $item) {
                    $user = User::where('id', $item->kedai->user_id)->first();
                    broadcast(new NotifyKedai($item))->toOthers();
                    $user->notify(new sendNotifToKedai());
                }
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

    public function orderViewKedai()
    {
        $user = auth()->guard('api')->user();
        $kedai = Kedai::where('user_id', $user->id)->first();

        if (!$kedai) {
            return response()->json([
                'success' => false,
                'message' => 'Kedai not found',
            ], 404);
        }

        $orderIds = OrderDestination::where('kedai_id', $kedai->id)->pluck('order_id');
        $orders = Order::whereIn('id', $orderIds)
                        ->whereBetween('status_order', [1, 6])
                        ->whereDate('created_at', Carbon::today())
                        ->with(['driver', 'driver.user', 'pelanggan', 'pelanggan.user'])
                        ->get();

        // Ambil order_id dari $orders yang sudah difilter
        $filteredOrderIds = $orders->pluck('id');

        $menuIds = Menu::where('kedai_id', $kedai->id)->pluck('id');
        $orderDetails = OrderDetail::with(['menu'])
                                    ->whereIn('menu_id', $menuIds)
                                    ->whereIn('order_id', $filteredOrderIds) // Filter sesuai order_id dari $orders
                                    ->get();

        $selectedMenu = $orderDetails->map(function ($detail) {
            $extras = OrderDetailEkstra::with(['menuDetail.kategoriPilihan'])
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
                'order_id' => $detail->order_id,
                'menu_id' => $detail->menu->id,
                'catatan' => $detail->catatan,
                'kedai_id' => $detail->menu->kedai_id,
                'price' => $detail->price,
                'qty' => $detail->qty,
                'menu' => $detail->menu->nama,
                'ekstra' => $extras,
            ];
        });

        return response()->json([
            'success' => true,
            'order' => $orders,
            'selectedMenu' => $selectedMenu,
        ], 200);
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
        $userAuth = auth()->guard('api')->user();
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

    public function cancelOrder($invoice_number){
        $order = Order::where('invoice_number',$invoice_number)->first();
        if($order){
            DB::beginTransaction();
            try {
                $order->status_order = 8;
                $order->save();
                DB::commit();
                return response()->json([
                    'success' => true,
                    'massage' => 'order berhasil dibatalkan',
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
}
