<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class FirebaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user) {
            $user->fcm_token = $request->fcm_token;
            $user->save();
            return response()->json(["message" => "Token stored successfully"], 200);
        }
        return response()->json(["error" => "User not found"], 404);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Display the specified resource.
     */
    //show all notifications related to user
    public function show()
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user) {
            $notifications = Notification::where('notifiable_type', 'App\Models\User')->where('notifiable_id', $user->id)->get();
            return response()->json(["success" => true, "data" => $notifications], 200);
        }
        return response()->json(["error" => "User not found"], 404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
