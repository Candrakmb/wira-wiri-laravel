<?php

namespace App\Http\Controllers\Api;

use App\Events\NotifyDriver;
use App\Helpers\Haversine;
use App\Helpers\weightedProduct;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use App\Models\OrderDestination;
use Carbon\Carbon;

class WpApiController extends Controller
{
    public function weightProduct($invoice)
        {
            // Tentukan tanggal hari ini
            $hariIni = Carbon::today();
            $dataDriver = [];
            $weights = [];
            $types = [];

            // Query untuk mengambil driver yang statusnya = 1 dan tidak memiliki order dengan status < 2 hari ini
            $availableDrivers = Driver::where('status', '1')
                ->whereDoesntHave('orders', function ($query) use ($hariIni) {
                    $query->where('status_order', '<', 1)
                        ->whereDate('created_at', $hariIni);
                })
                ->withCount(['orders' => function ($query) use ($hariIni) {
                    $query->where('status_order', 1)
                        ->whereDate('created_at', $hariIni);
                }])
                ->get();
            if($availableDrivers){
                $order = Order::where('invoice_number', $invoice)->first();
                $orderDestination = OrderDestination::with(['kedai'])->where('order_id', $order->id)->where('tipe_destination', '1')->first();
                $latitudeResto = $orderDestination->kedai->latitude;
                $longitudeResto = $orderDestination->kedai->longitude;

                // Iterasi setiap driver dan hitung jarak ke restoran
                foreach ($availableDrivers as $driver) {
                    $driver->orders_count += 1;
                    $driver->distance_to_resto = Haversine::calculateDistance(
                        $driver->latitude,
                        $driver->longitude,
                        $latitudeResto,
                        $longitudeResto
                    );
                    // Tambahkan data driver ke dalam array $dataDriver
                    $dataDriver[] = [
                        'id' => $driver->id,
                        'distance' => $driver->distance_to_resto,
                        'order' => $driver->orders_count,
                        'time' => $driver->time_difference
                    ];
                }

                $weights = [
                    'distance' => 2,
                    'order' => 5,
                    'time' => 3
                ];

                $types = [
                    'distance' => 'cost',
                    'order' => 'cost',
                    'time' => 'benefit'
                ];

                $scores = weightedProduct::weightedProducts($dataDriver, $weights, $types);

                // broadcast(new NotifyDriver($dataDriver,$order))->toOthers();

                return response()->json([
                    'success'  => true,
                    'drivers' => $dataDriver,
                    'score' => $scores
                ], 200);
            } else {
                return response()->json([
                    'success'  => false,
                    'message' => 'tidak menemukan driver'
                ], 200);
            }
        }

    public function serachDriverSecond($id){
        
    }
}
