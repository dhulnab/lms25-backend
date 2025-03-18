<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
    public function resetPassword(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8',
            'otp' => 'required|max:6'
        ]);

        $otpStatus = $this->otp->validate($request->get('email'), $request->get('otp'));
        if (!$otpStatus->status) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
            ], 422);
        }
        $user = User::where('email', $request->get('email'))->firstOrFail();
        $user->password = Hash::make($request->get('password'));
        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ], 200);
    }

    public function requestOtp(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Send OTP email
        $user->notify(new EmailVerificationNotification(
            "Update User Information",
            "This OTP is for updating your information. If this wasn't you, please ignore this email.",
        ));

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully to your email.'
        ]);
    }
    public function verifyOtpAndUpdateUser(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'old_email' => 'required|email|exists:users,email',
            'otp' => 'required|max:6'
        ]);

        $user = User::where('email', $validatedData['old_email'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Validate OTP
        $otpStatus = $this->otp->validate($request->get('email'), $request->get('otp'));

        if (!$otpStatus->status) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
            ], 422);
        }

        // Update user information
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user,
        ]);
    }
}
