<?php

namespace App\Http\Controllers\Api;

use App\Events\SendMessage;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Message;
use App\Models\Order;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatApiController extends Controller
{
    public function index(Request $request){
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'pesan'  => $validator->errors()
            ], 422);
        }
        $userAuth = Auth::guard('api')->user();
        $order = Order::where('invoice_number', $request->invoice_number)->first();
        $sender_id = $userAuth->id;
        if( $userAuth->getRoleNames()->contains('driver')){
            $pelanggan = Pelanggan::with('user')->where('id', $order->pelanggan_id)->first();
            $receiver_id = $pelanggan->user->id;
        } else {
            $driver = Driver::with('user')->where('id', $order->driver_id)->first();
            $receiver_id = $driver->user->id;

        }

        if($order){
            if($order->status_order != 7){
                $messages = Message::where(function($query) use ($sender_id, $receiver_id, $order) {
                    $query->where('sender_id', $sender_id)
                          ->where('receiver_id', $receiver_id)
                          ->where('order_id', $order->id);
                })->orWhere(function($query) use ($sender_id, $receiver_id, $order) {
                    $query->where('sender_id', $receiver_id)
                          ->where('receiver_id', $sender_id)
                          ->where('order_id', $order->id);
                })
                ->with('sender:id,name', 'receiver:id,name')
                ->get();

                return response()->json([
                    'success' => true,
                    'messages' => $messages
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'pesan'  => 'chat sudah berakhir'
                ], 200);
            }
        } else {
            return response()->json([
                'success' => false,
                'pesan'  => 'order tidak ditemukan'
            ], 404);
        }
    }

    public function sendMessage(Request $request){
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|string',
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'pesan'  => $validator->errors()
            ], 422);
        }

        $order = Order::where('invoice_number',$request->invoice_number)->first();
        if($order){
            if($order->order_status != 7){
                DB::beginTransaction();
                try{
                    $sender_id = Auth::guard('api')->user()->id;

                    $chatMessage = new Message();
                    $chatMessage->sender_id = $sender_id;
                    $chatMessage->receiver_id = $request->receiver_id;
                    $chatMessage->order_id = $order->id;
                    $chatMessage->content = $request->content;
                    $chatMessage->save();

                    $chatMessage->load(['sender:id,name', 'receiver:id,name']);

                    broadcast(new SendMessage($chatMessage))->toOthers();
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'messages' => $chatMessage
                    ], 200);
                } catch (\Exception $e) {
                    return response()
                        ->json([
                            'success' => false,
                            'message' => 'An error occurred: ' . $e->getMessage(),
                        ], 500);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'pesan'  => 'chat sudah berakhir'
                ], 200);
            }
        } else {
            return response()->json([
                'success' => false,
                'pesan'  => 'order tidak ditemukan'
            ], 404);
        }
    }
}
