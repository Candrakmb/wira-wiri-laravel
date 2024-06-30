<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Cookie;

class CheckJWTFromCookie
{
    public function handle($request, Closure $next)
    {
        if ($request->cookie('token')) {
            $token = $request->cookie('token');
            try {
                JWTAuth::setToken($token);
                JWTAuth::authenticate();
            } catch (JWTException $e) {
                if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                    try {
                        if ($request->cookie('refresh_token')) {
                            $refreshToken = $request->cookie('refresh_token');
                            $newToken = JWTAuth::refresh($refreshToken);
                            $request->headers->set('Authorization', 'Bearer ' . $newToken);
                            $cookie = cookie('token', $newToken, 60, null, null, false, true); // 1 hour, HttpOnly

                            return $next($request)->withCookie($cookie);
                        } else {
                            return response()->json(['error' => 'Refresh token tidak ditemukan'], 401);
                        }
                    } catch (JWTException $e) {
                        return response()->json(['error' => 'Token tidak dapat diperbarui'], 401);
                    }
                } else {
                    return response()->json(['error' => 'Token tidak valid'], 401);
                }
            }
        } else {
            return response()->json(['error' => 'Token tidak ditemukan'], 401);
        }

        return $next($request);
    }
}
