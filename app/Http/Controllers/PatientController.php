<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use App\Models\MyInformation;
use App\Models\DoctorAppointment;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        //
    }

    /**
     * Custom Login
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = JWTAuth::fromUser($user);
            
            //return response()->json(['token' => $token]);

            return $this->respondWithToken($token);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
    /**
     * Custom register
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|unique:users|max:255',
            'password' => 'required|min:6',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = 0;
        $user->save();
        
        return response()->json(['message' => 'Patient registered successfully'], 201);

    }
    /**
     * Find me
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Add my_information
     */
    public function add_my_information(Request $request) {
        if(auth()->user()) {
            $validator = Validator::make($request->all(), [
                'dob' => 'required',
                'location_id' => 'required'
            ]);
            
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 422);
            }

            $myInformation = new MyInformation();
            $myInformation->dob = $request->dob;
            $myInformation->location_id = $request->location_id;
            $myInformation->user_id = auth()->user()->id;
            $myInformation->save();

            return response()->json(['message' => 'Information added successfully'], 200);
        }
    }

    /**
     * Get full information
     */
    public function get_full_information() {
        $usersWithInformation = MyInformation::with(['user', 'location'])
        ->where('user_id', auth()->user()->id)->first();

        return response()->json(['users' => $usersWithInformation], 200);
    }


    /**
     * Get all doctor if patient logged in
     */
    public function get_all_doctors() {
        $usersWithInformation = User::with(['doctor.location'])->where('role', 1)->orderBy('id', 'DESC')->get();
    
        return response()->json(['doctor' => $usersWithInformation], 200);
    }

    /**
     * Read doctor by one
     */
    public function get_doctor(Request $request)
    {
        $doctorId = $request->input('doctor_id');

        $doctor = User::with('doctor.location')
        ->where('role', 1)
        ->where('id', $doctorId)
        ->first();

        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        return response()->json(['doctor' => $doctor], 200);
        
    }
    
    /**
     * Create appointment
     */
    public function create_appointment(Request $request) {

        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $new_appointment = new DoctorAppointment();
        $new_appointment->user_id = auth()->user()->id;
        
        $doctor = DoctorAppointment::where('doctor_id', $request->doctor_id)->count();

        if($doctor == 10) {
            return response()->json(['error' => 'can not take more appointment'], 200);
        }

        $doctor = User::with('doctor.location')
        ->where('role', 1)
        ->where('id', $request->doctor_id)
        ->first();

        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        $new_appointment->doctor_id = $request->doctor_id;

        $new_appointment->booking_date = now();
        $new_appointment->appointment_date = $request->appointment_date;

        $new_appointment->status = 'pending';

        $new_appointment->save();
        return response()->json(['message' => 'Your appointment has been made!'], 200);
    }

    /**
     * All patient appointment
     */
    public function all_appointments() {
        $all_appointments = DoctorAppointment::with(['doctor', 'doctor.user', 'user'])->where('user_id', auth()->user()->id)->get();
        return response()->json(['appointments' => $all_appointments], 200);
    }

    public function change_appointment_status_cancel(Request $request) {
        $appointmentId = $request->input('appointment_id');

        $appointment = DoctorAppointment::find($appointmentId);

        if(!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }
        
        if ($appointment->update(['status' => 'cancel']));

        return response()->json(['message' => 'Appointment Canceled'], 200);
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
