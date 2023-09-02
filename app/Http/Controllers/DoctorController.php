<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DoctorController extends Controller
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
    public function show(Doctor $doctor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Doctor $doctor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Doctor $doctor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        //
    }

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

        return response()->json(['message' => 'Successfully logged out']);
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
