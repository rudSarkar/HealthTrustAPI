<?php

namespace App\Http\Controllers;

use App\Models\Admin;
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
}
