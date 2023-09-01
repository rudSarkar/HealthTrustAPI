<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\DoctorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth', 'admin'])->group(function () {
    
});

Route::middleware(['auth', 'doctor'])->group(function () {
    Route::get('doctor/me', [DoctorController::class, 'me']);
    Route::get('doctor/profile', [DoctorController::class, 'profile']);
});

Route::middleware(['auth', 'patient'])->group(function () {
    Route::get('patient/me', [PatientController::class, 'me']);
    Route::post('patient/add_my_information', [PatientController::class, 'add_my_information']);
    Route::get('patient/get_full_information', [PatientController::class, 'get_full_information']);

    /**
     * Patients activities
     */
    Route::get('patient/all_doctors', [PatientController::class, 'get_all_doctors']);
    Route::get('patient/get_doctor', [PatientController::class, 'get_doctor']);
    Route::post('patient/appointment', [PatientController::class, 'create_appointment']);
    Route::get('patient/appointments', [PatientController::class, 'all_appointments']);

    Route::get('patient/logout', [PatientController::class, 'logout']);
});

/**
 * Patient register, login routes
 */
Route::post('patient/register', [PatientController::class, 'register']);
Route::post('patient/login', [PatientController::class, 'login']);

/**
 * Doctor register, login routes
 */
Route::post('doctor/register', [DoctorController::class, 'register']);
Route::post('doctor/login', [DoctorController::class, 'login']);

/**
 * Other public APIs
 */
Route::get('public/location', [PublicController::class, 'get_location']);