<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class RefreshTokenMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            try {
                $refreshedToken = JWTAuth::refresh(JWTAuth::getToken());
                $request->headers->set('Authorization', 'Bearer ' . $refreshedToken);
            } catch (JWTException $e) {
                return response()->json(['error' => 'token_not_refreshable'], 401);
            }
        }

        return $next($request);
    }
}
