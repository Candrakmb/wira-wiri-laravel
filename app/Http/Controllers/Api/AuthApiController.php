<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthApiController extends Controller
{
    public function logout(Request $request)
    {
        try {
            
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Logout Berhasil!',
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Logout gagal!',
                ],
                500,
            );
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $credentials = $request->only('email', 'password');

            if (!($token = auth()->guard('api')->attempt($credentials))) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Email atau Password Anda salah',
                    ],
                    401,
                );
            }

            $user = auth()->guard('api')->user();

            return response()->json(
                [
                    'success' => true,
                    'user' => $user,
                    'role' => $user->getRoleNames(),
                    'token' => $token,
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal mengkodekan token: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function refresh()
    {
        $token = JWTAuth::getToken();
        $newToken = JWTAuth::refresh($token);

        return response()->json(['token' => $newToken ,'tokenreal' => $token]);
    }
}
