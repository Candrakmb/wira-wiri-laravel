<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Driver;
use App\Models\Order;
use App\Models\OrderDestination;
use App\Models\QueueDriver;
use Carbon\Carbon;
use App\Helpers\Haversine;
use App\Helpers\weightedProduct;
use Illuminate\Support\Facades\DB;
use App\Events\NotifyDriver;
use App\Models\User;
use App\Notifications\SendOrderanDriver;
use Illuminate\Support\Facades\Log;

class QueueSearchingDriver implements ShouldQueue
{
    use Queueable;
    public $invoice;
    /**
     * Create a new job instance.
     */
    public function __construct($invoice)
    {
       $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Ambil data order berdasarkan nomor invoice
        $order = Order::where('invoice_number', $this->invoice)->first();

        if ($order && $order->driver_id === null && $order->status_order === 0) {
            // Tentukan tanggal hari ini
            $hariIni = Carbon::today();
            $dataDriver = [];
            $weights = [];
            $types = [];
            $selectedDriver = [];

            // Query untuk mengambil driver yang statusnya = 1 dan tidak memiliki order dengan status < 2 hari ini
            $availableDrivers = Driver::where('status', '1')
                ->whereDate('time_on', $hariIni)
                ->whereNotExists(function ($query) {
                    // Subquery untuk memeriksa apakah driver sudah ada di tabel QueueDriver
                    $query->select(DB::raw(1))
                          ->from('queue_drivers')
                          ->whereColumn('queue_drivers.driver_id', 'drivers.id')
                          ->where('delete_queue', '>=', Carbon::now()); // Kondisi tambahan untuk memastikan queue masih aktif
                })
                ->withCount(['orders' => function ($query) use ($hariIni) {
                    $query->where('status_order', 7)
                        ->whereDate('created_at', $hariIni);
                }])
                ->get();

            if ($availableDrivers->isNotEmpty()) {
                $orderDestination = OrderDestination::with(['kedai'])
                    ->where('order_id', $order->id)
                    ->where('tipe_destination', '1')
                    ->first();

                if (!$orderDestination || !$orderDestination->kedai) {
                    Log::error('Kedai tidak ditemukan untuk order ID: ' . $order->id);
                    return;
                }

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

                // Menentukan bobot dan tipe untuk metode weighted product
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

                // Menghitung skor driver
                $scores = WeightedProduct::weightedProducts($dataDriver, $weights, $types);

                // Ambil driver dengan skor tertinggi
                $selectedDriverData = Driver::with(['user'])
                    ->where('id', $scores[0]['driver_id'])
                    ->first();

                if ($selectedDriverData) {
                    $selectedDriver[] = [
                        'driver' => $selectedDriverData,
                        'order' => $order,
                    ];

                    DB::beginTransaction();
                    try {
                        $queueDriver = new QueueDriver();
                        $queueDriver->driver_id = $selectedDriverData->id;
                        $queueDriver->order_id = $order->id;
                        $queueDriver->end_queue = Carbon::now()->addMinutes(3);
                        $queueDriver->delete_queue = Carbon::now()->addMinutes(8);
                        $queueDriver->save();

                        $queueDriver->load(['driver', 'order']);
                        DeleteQueueDriver::dispatch($queueDriver->driver_id)->delay($queueDriver->delete_queue);
                        // Broadcast event untuk notifikasi driver
                        broadcast(new NotifyDriver($queueDriver))->toOthers();

                        $userDriver = Driver::where('id', $queueDriver->driver_id)->first();
                        $user = User::where('id', $userDriver->user_id)->first();
                        $user->notify(new SendOrderanDriver());
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollback();
                        Log::error('Error saat memproses order: ' . $e->getMessage());
                    }
                    QueueDriver::where('delete_queue', '<=', Carbon::now())->delete();
                } else {
                    Log::warning('Tidak ada driver yang ditemukan dengan ID driver: ' . $scores[0]['driver_id']);
                }
            } else {
                EndSearchingDriver::dispatch($this->invoice)->delay(now()->addSeconds(10));
                Log::info('Tidak ada driver tersedia untuk order ID: ' . $order->id);
            }
        } else {
            Log::info('Order ID: ' . $order->id . ' sudah memiliki driver.');
        }
    }
}
