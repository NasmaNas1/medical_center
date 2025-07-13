<?php

use Illuminate\Support\Facades\Route;
use App\Models\Patient;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/admin/login');
});



Route::get('/test-token', function () {
    $patient = Patient::first(); // أو حسب كيف عم تجيب المريض
    $token = $patient->createToken('auth-token', ['patient'])->plainTextToken;
    return response()->json(['token' => $token]);
});
