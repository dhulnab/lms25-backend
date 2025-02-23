<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class EmailVerificationController extends Controller
{
    private $otp;
    public function __construct()
    {
        $this->otp = new Otp();
    }
    public function emailVerification(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|max:6',
        ]);

        $otpStatus = $this->otp->validate($request->get('email'), $request->get('otp'));
        if (!$otpStatus->status) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
            ], 422);
        }
        $user = User::where('email', $request->get('email'))->firstOrFail();
        $user->email_verified_at = now();
        $user->save();
        $token = JWTAuth::claims(['role' => 'client'])->fromUser($user);
        return response()->json([
            'success' => true,
            'token' => $token,
            'message' => 'Email verified successfully',
        ], 200);
    }
}
