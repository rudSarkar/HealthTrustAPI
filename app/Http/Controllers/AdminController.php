<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\DoctorAppointment;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * View all appointments
     */
    public function get_all_appointments() {
        $all_appointments = DoctorAppointment::with(['doctor', 'doctor.user', 'user'])->get();
        return response()->json(['appointments' => $all_appointments], 200);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = JWTAuth::fromUser($user);
            

            return $this->respondWithToken($token);
        }

        return response()->json(['error' => 'Email and Password wrong!'], 401);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
