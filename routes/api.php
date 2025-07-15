<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\SpecializationController;
use App\Http\Controllers\Api\SubSpecializationController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\OpinionController;
use App\Models\Doctor;
use App\Http\Controllers\Api\DoctorScheduleController;
use App\Http\Controllers\Api\ReportController;
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

// تسجيل الدخول والتسجيل
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

// التخصصات
Route::get('/specialization', [SpecializationController::class, 'index']);
Route::get('/sub-specializations/by-specialization/{id}', [SubSpecializationController::class, 'getBySpecialization']);

// الأطباء واستعراض الآراء
Route::get('/doctor/{id}', [DoctorController::class, 'index']);
Route::get('/doctors/by-specialization/{specialization_id}', [DoctorController::class, 'getBySpecialization']);
Route::get('/showOpinions', [OpinionController::class, 'show']);

// تقارير المرضى
Route::get('/patients/{patient_uuid}/reports', [ReportController::class, 'getPatientReports']);

Route::middleware(['auth:sanctum', 'patient'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/patient/{id}', [PatientController::class, 'index']);
    Route::post('/patient/update/{id}', [PatientController::class, 'update']);
    // تم
    Route::post('/appointments/book', [AppointmentController::class, 'bookAppointment']);
    Route::post('/opinions',[OpinionController::class,'opinion']);
});


Route::middleware(['auth:sanctum', 'doctor'])->group(function () {
    Route::post('/reports', [ReportController::class, 'store']);
    // jl
    Route::get('appointments/weekly/{doctorId}',[AppointmentController::class,'getWeeklyAppointments']);
    // 
    Route::get('/doctors/{id}/appointments/status/{status}', [AppointmentController::class, 'getAppointmentsByStatus']);
    Route::get('/doctors/{id}/appointments/patients-count', [AppointmentController::class, 'getPatientsCountByStatus']);
    // jl
    Route::get('/doctors/{id}/availableSlots/{subSpecializationId}', [AppointmentController::class, 'getAvailableSlots']);
    // 
    Route::post('/doctor-schedules', [DoctorScheduleController::class, 'store']);
    
    Route::put('/doctor-schedules/{scheduleId}/availability', [DoctorScheduleController::class, 'updateAvailability']);
    
});


