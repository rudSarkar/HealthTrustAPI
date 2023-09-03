<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthForgotPasswordController;
use App\Http\Controllers\AuthResetPasswordController;

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
    Route::get('admin/all_appointments', [AdminController::class, 'get_all_appointments']);
});

Route::middleware(['auth', 'doctor'])->group(function () {
    Route::get('doctor/me', [DoctorController::class, 'me']);
    Route::get('doctor/profile', [DoctorController::class, 'profile']);
    Route::get('doctor/logout', [DoctorController::class, 'logout']);

    /**
     * Doctor appointment
     */
    Route::get('doctor/appointments', [DoctorController::class, 'get_all_appointments']);
    Route::get('doctor/appointment/confirm', [DoctorController::class, 'change_appointment_status_confirm']);
    Route::get('doctor/appointment/cancel', [DoctorController::class, 'change_appointment_status_cancel']);

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
    Route::get('patient/appointment/cancel', [PatientController::class, 'change_appointment_status_cancel']);


    Route::get('patient/search_doctor', [PatientController::class, 'search_doctor_by_location_specality']);
    Route::get('patient/search_ambulance', [PatientController::class, 'search_ambulance']);

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


/**
 * Doctor, Patient forget password and reset
 */
Route::post('password/forgot', [AuthForgotPasswordController::class, 'sendResetLinkEmail']);
Route::get('password/reseted', [AuthResetPasswordController::class, 'reset_complete']);