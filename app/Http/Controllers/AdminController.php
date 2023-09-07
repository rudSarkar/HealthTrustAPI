<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Admin;
use App\Models\User;
use App\Models\Ambulance;
use App\Models\DoctorAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


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

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    public function get_verified_doctor_profiles() {

        $doctor = User::with('doctor.location')
        ->where('role', 1)
        ->whereHas('doctor', function ($query) {
            $query->where('is_verified', 1);
        })
        ->get();

        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        return response()->json(['doctor' => $doctor], 200);
    }

    public function get_non_verified_doctor_profiles() {

        $doctor = User::with('doctor.location')
        ->where('role', 1)
        ->whereHas('doctor', function ($query) {
            $query->where('is_verified', 0);
        })
        ->get();

        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        return response()->json(['doctor' => $doctor], 200);
    }

    public function verify_doctor(Request $request) {
        $doctorId = $request->input('doctor_id');

        $verify_doctor = User::with('doctor.location')
                            ->where('role', 1)
                            ->find($doctorId);

        if(!$verify_doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }
        
        $doctor = $verify_doctor->doctor;
        $doctor->is_verified = 1;
        $doctor->save();
        
        return response()->json(['message' => 'Doctor Profile Verified!'], 200);
    }

    public function add_ambulance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'location_id' => 'required',
            'location_in_details' => 'required',
            'ambulance_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $addAmbulnace = new Ambulance();
        $addAmbulnace->name = $request->name;
        $addAmbulnace->location_id = $request->location_id;
        $addAmbulnace->location_in_details = $request->location_in_details;
        $addAmbulnace->gmap_link = $request->gmap_link;
        $addAmbulnace->ambulance_number = $request->ambulance_number;

        $addAmbulnace->save();

        return response()->json(['message' => 'Ambulance added'], 200);
    }

    public function all_patients() {
        $patients = User::where('role', 0)->get();
        return response()->json(['patients' => $patients], 200);
    }

    public function delete_single_patient(Request $request)
    {
        $userId = $request->input('patient_id');
        $user = User::where('role', 0)->find($userId);

        if (!$user) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Patient deleted sucessfully.'], 200);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'role' => auth()->user()->role,
            'name' => auth()->user()->name,
            'email' => auth()->user()->email
        ]);
    }
}
