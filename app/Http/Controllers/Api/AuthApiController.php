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
            $cookieToken = Cookie::forget('token');
            $cookieRefreshToken = Cookie::forget('refresh_token');
            
            return response()->json([
                'success' => true,
                'message' => 'Logout Berhasil!',
            ])->withCookie($cookieToken)->withCookie($cookieRefreshToken);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout gagal!',
            ], 500);
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
    
            if (!$token = auth()->guard('api')->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau Password Anda salah'
                ], 401);
            }
    
            $user = auth()->guard('api')->user();
            
            // Generate refresh token
            $refreshToken = JWTAuth::fromUser($user);
    
            // Set tokens in HttpOnly cookies
            $cookieToken = cookie('token', $token, 60, null, null, false, true);
            $cookieRefreshToken = cookie('refresh_token', $refreshToken, 60 * 24 * 30, null, null, false, true); 

    
            return response()->json([
                'success' => true,
                'user' => $user,
                'token' => $token,
                'refresh_token' => $refreshToken
            ], 200)->withCookie($cookieToken)->withCookie($cookieRefreshToken);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengkodekan token: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    public function refresh(Request $request)
    {
        try {
            $refreshToken = $request->cookie('refresh_token');

            // Use refresh token to generate new token
            $newToken = JWTAuth::refresh($refreshToken);

            // Set new token in HttpOnly cookie
            $cookieToken = cookie('token', $newToken, 60, null, null, false, true); // 1 hour, HttpOnly

            return response()->json([
                'success' => true,
                'token' => $newToken
            ], 200)->withCookie($cookieToken);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token telah kedaluwarsa: ' . $e->getMessage()
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token tidak valid: ' . $e->getMessage()
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak dapat diperbarui: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getToken(Request $request){
        $token = $request->cookie('token');
        $refreshToken = $request->cookie('refresh_token');

        return response()->json([
            'success' => true,
            'token' => $token,
            'refresh_token' => $refreshToken
        ], 200);
    }
}
