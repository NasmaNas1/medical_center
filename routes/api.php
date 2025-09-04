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
use App\Http\Controllers\Api\RatingController;
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


Route::middleware(['auth:sanctum', 'patient'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/patient/{id}', [PatientController::class, 'index']);
    Route::post('/patient/update/{id}', [PatientController::class, 'update']);
    Route::get('/patients/{id}/appointments', [PatientController::class, 'appointments']);
    Route::post('/appointments/book', [AppointmentController::class, 'bookAppointment']);
    Route::post('/opinions',[OpinionController::class,'opinion']);
    Route::get('/doctors/{doctorId}/subSpecializations/{subSpecializationId}/availableSlots', [AppointmentController::class, 'getAvailableSlots']);
    Route::post('/appointments/{appointment}/rate', [RatingController::class, 'rateAppointment']);
    Route::get('/patients/{patient_id}/reports', [ReportController::class, 'getPatientReports']);
    

});
 Route::get('/doctors/{doctor}/average-rating', [RatingController::class, 'getDoctorAverageRating']);

Route::middleware(['auth:sanctum', 'doctor'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('appointments/weekly/{doctorId}',[AppointmentController::class,'getWeeklyAppointments']);
    Route::get('/doctors/{id}/appointments/status/{status}', [AppointmentController::class, 'getAppointmentsByStatus']);
    Route::get('/doctors/{id}/appointments/patients-count', [AppointmentController::class, 'getPatientsCountByStatus']);
    Route::post('appointments/{id}/attendance', [AppointmentController::class, 'markAttendance']);
    Route::post('/doctor-schedules', [DoctorScheduleController::class, 'store']);
    Route::post('/doctor-schedules/{scheduleId}/update', [DoctorScheduleController::class, 'updateSchedule']);
    Route::get('/doctors/{doctorId}/schedules/available', [DoctorScheduleController::class, 'getAvailableSchedules']);
});

