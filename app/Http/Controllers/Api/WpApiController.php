<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\EndSearchingDriver;
use App\Models\Driver;
use Carbon\Carbon;
use App\Jobs\QueueSearchingDriver;
use Illuminate\Support\Facades\DB;

class WpApiController extends Controller
{
    public function weightProduct($invoice)
        {
            // Tentukan tanggal hari ini
            $hariIni = Carbon::today();
            $dataDriver = [];

            // Query untuk mengambil driver yang statusnya = 1 dan tidak memiliki order dengan status < 2 hari ini
            $availableDrivers = Driver::where('status', '1')
                ->whereDate('time_on', $hariIni)
                ->whereNotExists(function ($query) {
                    // Subquery untuk memeriksa apakah driver sudah ada di tabel QueueDriver
                    $query->select(DB::raw(1))
                          ->from('queue_drivers')
                          ->whereColumn('queue_drivers.driver_id', 'drivers.id')
                          ->where('delete_queue', '>=', Carbon::now());
                })
                ->get();
            if($availableDrivers){
                QueueSearchingDriver::dispatch($invoice)->delay(now()->addSeconds(10));
                QueueSearchingDriver::dispatch($invoice)->delay(now()->addMinutes(4));
                QueueSearchingDriver::dispatch($invoice)->delay(now()->addMinutes(8));
                EndSearchingDriver::dispatch($invoice)->delay(now()->addMinutes(13));
                return response()->json([
                    'success'  => true,
                    'drivers' => $dataDriver,
                ], 200);    
            } else {
                EndSearchingDriver::dispatch($invoice)->delay(now()->addSeconds(10));
                return response()->json([
                    'success'  => false,
                    'message' => 'tidak menemukan driver'
                ], 200);
            }
        }
}
