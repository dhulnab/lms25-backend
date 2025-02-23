<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminController extends Controller
{

    public function promoteToAdmin($id)
    {
        $user = User::find($id);

        // Check if the user exists
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Check if the user is already an admin
        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'User is already an admin'
            ], 400);
        }

        $user->role = 'admin';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User successfully promoted to admin',
            'user' => $user
        ], 200);
    }
}
