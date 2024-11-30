<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use App\Models\QueueDriver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueApiController extends Controller
{
    public function index() {
        $userAuth = Auth::guard('api')->user();
        if (!$userAuth) {
            return response()->json([
                'success' => false,
                'message' => 'User is not authenticated',
            ], 401);
        }

        $driver = Driver::where('user_id', $userAuth->id)->first();
        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found',
            ], 404);
        }

        $queueDriver = QueueDriver::with(['order','order.pelanggan.user','driver'])->where('driver_id', $driver->id)->where('end_queue', '>', Carbon::now())->first();
        // $queueDriver = QueueDriver::with(['order','order.pelanggan.user','driver'])->where('driver_id', $driver->id)->first();
        // dd($queueDriver);
        if ($queueDriver) {
            return response()->json([
                'success' => true,
                'orderan' => $queueDriver,
            ], 200);
        } else {
            return response()->json([
                'success' => true,
                'orderan' => null,
            ], 200);
        }
    }
}
