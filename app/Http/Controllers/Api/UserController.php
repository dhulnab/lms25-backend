<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use App\Notifications\EmailVerificationNotification;
use Ichtrojan\Otp\Otp;

class UserController extends Controller
{
    public function register(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users,email',
            'password' => 'required|string|min:8'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $req->get('name'),
            'email' => $req->get('email'),
            'password' => Hash::make($req->get('password')),
        ]);

        $user->notify(new EmailVerificationNotification());
        return response()->json(compact('user'), 201);
    }

    public function login(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'email' => 'required|string|max:255,email',
            'password' => 'required|string|min:8'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $credentials = $req->only('email', 'password');

        try {
            $user = User::where('email', $req->get('email'))->first();
            if (!$user || !Hash::check($req->get('password'), $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password.',
                ], 401);
            }
            if (!$user->email_verified_at) {
                return response()->json([
                    'success' => false,
                    'verified' => false,
                    'message' => 'Email not verified.',
                ], 401);
            }
            $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);
            return response()->json([
                'success' => true,
                'verified' => true,
                'user' => $user,
                'token' => $token,
            ], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    public function getUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        return response()->json(compact('user'));
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid or missing token'], 400);
        }
    }



    public function refreshToken(Request $request)
    {
        try {
            // Attempt to refresh the token
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            // Return the new token
            return response()->json([
                'token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60 // Get TTL in seconds
            ]);
        } catch (JWTException $e) {
            // Handle all JWT exceptions (expired, invalid, or missing token)
            return response()->json(['error' => 'Token is invalid or missing'], 401);
        }
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }
}
