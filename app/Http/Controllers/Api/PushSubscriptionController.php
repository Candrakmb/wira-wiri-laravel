<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\PushDemo;
use App\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Notification;

class PushSubscriptionController extends Controller
{
    public function store(Request $request){

        $this->validate($request, ['endpoint' => 'required']);

        $user = auth()->guard('api')->user();

        if (!$user) {
            return response()->json(['success' => false],401);
        }
        try{
            $user->updatePushSubscription(
                $request->post('endpoint'),
                $request->post('public_key'),
                $request->post('auth_token'),
                $request->post('encoding'),
            );

            return response()->json(['success' => true],200);
        } catch(Exception $e){
            return response()->json(['success' => false , 'message' => $e->getMessage()],500);
        }

    }

    public function destroy(Request $request){
        $user = auth()->guard('api')->user();

        if (!$user) {
            return response()->json(['success' => false],401);
        }

        $user->deletePushSubscription($request->post('endpoint'));

        return response()->json(['message' => 'Unsubscribed!']);
    }
}

