<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class ClientAuth
{
    public function handle($req, Closure $next, $role = 'client')
    {
        try {
            $token = JWTAuth::parseToken();
            $payload = $token->getPayload();

            // Get the expiration timestamp
            $expiration = $payload->get('exp');

            // Check if the token has expired
            if ($expiration < now()->timestamp) {
                return response()->json(['error' => 'Token has expired'], 401);
            }

            if ($payload->get('role') !== $role) {
                return response()->json(['error' => 'Insufficient role'], 403);
            }
        } catch (JWTException $e) {
            // Handle specific JWT errors
            if ($e instanceof TokenExpiredException) {
                return response()->json(['error' => 'Token has expired md'], 401);
            } elseif ($e instanceof TokenInvalidException) {
                return response()->json(['error' => 'Token is invalid md'], 401);
            } else {
                return response()->json(['error' => 'Authorization token not found md'], 401);
            }
        }

        return $next($req);
    }
}
