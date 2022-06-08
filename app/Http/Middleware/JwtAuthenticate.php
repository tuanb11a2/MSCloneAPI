<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
class JwtAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
            
        } catch (\Exception $e) {
            if ($e instanceof TokenExpiredException) {
                $token = JWTAuth::parseToken()->refresh();
                return response()->json(['tokenStatus' => 'expired', 'token' => $token], 200);
            } elseif ($e instanceof TokenInvalidException) {
                return response()->json(['tokenStatus' => 'invalid'], 401);
            } else {
                return response()->json(['tokenStatus' => 'not found'], 401);
            }
        }

        return $next($request);
    }
}
