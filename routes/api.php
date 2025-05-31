<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\SpecializationController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\OpinionController;
use App\Models\Doctor;
use App\Models\Specialization;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware('auth:sanctum')->get('/patient', function (Request $request) {
//     return $request->user();
// });

Route::post('/login',[LoginController::class , 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/register', [RegisterController::class, 'register']);



Route::get('/patient/{id}', [PatientController::class, 'index']);
Route::post('/patient/update/{id}', [PatientController::class, 'update']);


Route::get('/doctor/{id}',[DoctorController::class , 'index']);
Route::get('/doctor/{specialization_id}', [DoctorController::class, 'getBySpecialization']);

Route::post('/appointment',[AppointmentController::class , 'bookAppointment']);
Route::get('/doctors/{id}/availableSlots',[AppointmentController::class ,'getAvailableSlots']);


Route::get('/specialization',[SpecializationController::class,'index']);


Route::get('/showOpinions',[OpinionController::class,'show']);
Route::post('/opinions',[OpinionController::class,'opinion']);