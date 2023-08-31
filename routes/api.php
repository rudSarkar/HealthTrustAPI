<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;

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
    
});

Route::middleware(['auth', 'patient'])->group(function () {
    Route::get('patient/me', [PatientController::class, 'me']);
    Route::get('patient/testme', [PatientController::class, 'testme']);
    Route::get('patient/logout', [PatientController::class, 'logout']);
});

/**
 * Patient register, login routes
 */
Route::post('patient/register', [PatientController::class, 'register']);
Route::post('patient/login', [PatientController::class, 'login']);