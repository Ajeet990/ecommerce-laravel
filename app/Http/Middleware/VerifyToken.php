<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;


class VerifyToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Check if token exists and is valid
            $payload = JWTAuth::parseToken()->getPayload();

            // Extract user_id or other data from the token
            $userId = $payload->get('user_id');
            $request->attributes->add(['user_id' => $userId]);

        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token has expired'], 401);

        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token is invalid'], 401);

        } catch (JWTException $e) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        return $next($request);
    }
}
