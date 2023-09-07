<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Doctor;
use App\Models\DoctorAppointment;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DoctorController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            $doctor = Doctor::where('user_id', $user->id)->first();

            if ($doctor->is_verified) {
                $token = JWTAuth::fromUser($user);

                return $this->respondWithToken($token);
            } else {
                return response()->json(['error' => 'Wait for admin approval'], 401);
            }
        }

        return response()->json(['error' => 'Email and Password wrong!'], 401);
    }
    /**
     * Custom register
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'last_education' => 'required|string|max:255',
            'degrees' => 'required|string|max:255',
            'about' => 'required|string',
            'work_experience' => 'required|string',
            'image' => '',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        if ($request->has('doctor_image') && !empty($request->doctor_image)) {
            $imageData = $request->doctor_image;
        
            list($imageType, $base64Image) = explode(';', $imageData);
            list(, $base64Image) = explode(',', $base64Image);
        
            $image_base64 = base64_decode($base64Image);
        
            if ($image_base64 === false) {
                return response()->json(['error' => 'Failed to decode base64 image.'], 400);
            }
        
            $filename = time() . '.' . $imageType;
            $filename = str_replace('data:image/', '', $filename);
        
            $storageDirectory = 'public/doctor-images/';
        
            $storagePath = $storageDirectory.$filename;
        
            Storage::put($storagePath, $image_base64);

            $storagePath = 'storage/doctor-images/'.$filename;
        } else {
            $storagePath = 'doctor-images/default.jpeg';
        }
            
        

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = 1;
        $user->save();
        
        $doctorInformation = new Doctor();
        $doctorInformation->specialty = $request->specialty;
        $doctorInformation->location_id = $request->location_id;
        $doctorInformation->user_id = $user->id;
        $doctorInformation->last_education = $request->last_education;
        $doctorInformation->degrees = $request->degrees;
        $doctorInformation->price = $request->price;
        $doctorInformation->about = $request->about;
        $doctorInformation->work_experience = $request->work_experience;
        $doctorInformation->doctor_image = $storagePath;
        $doctorInformation->is_verified = false;

        $doctorInformation->save();

        return response()->json(['message' => 'Doctor registered successfully'], 201);

        
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function profile()
    {
        $usersWithInformation = User::with(['doctor.location'])->find(auth()->user()->id);

        return response()->json(['users' => $usersWithInformation], 200);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }


    /**
     * get all appointment
     */
    public function get_all_appointments() {
        $user_id = auth()->user()->id;
    
        $all_appointments = DoctorAppointment::with('user', 'doctor')->where('doctor_id', $user_id)->get();
        return response()->json(['appointments' => $all_appointments], 200);
    }

    public function change_appointment_status_confirm(Request $request) {
        $appointmentId = $request->input('appointment_id');

        $appointment = DoctorAppointment::find($appointmentId);

        if(!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }
        
        $appointment->update(['status' => 'confirm']);

        return response()->json(['message' => 'Appointment confirmed'], 200);
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
            'expires_in' => auth()->factory()->getTTL() * 3600,
            'role' => auth()->user()->role,
            'name' => auth()->user()->name,
            'email' => auth()->user()->email
        ]);
    }
}
