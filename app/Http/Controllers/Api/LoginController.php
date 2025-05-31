<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\GeneralTrait;
use App\Models\Patient;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PatientResource;

class LoginController extends Controller
{
    use GeneralTrait;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'يرجى إدخال البريد الإلكتروني',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'password.required' => 'يرجى إدخال كلمة المرور',
        ]);

        $patient = Patient::where('email', $request->email)->first();

        // التحقق من كلمة المرور
        if ($patient && Hash::check($request->password, $patient->password)) {
            $token = $patient->createToken('patient-token')->plainTextToken;
    
            return $this->responseWithJson(
                [
                    'patient' => new PatientResource($patient),
                    'token' => $token,
                ],
                true,
                null,
                200
            );
        }
        
    
        return $this->responseWithJson(
            null,
            false,
            'بيانات الدخول غير صحيحة',
            401
        );
    }

    public function logout(Request $request)
    {
        

        if (!$request->user()) {
            return $this->responseWithJson(null, false, 'المستخدم غير مصادق عليه', 401);
        }
    
        $request->user()->currentAccessToken()->delete();
        return $this->responseWithJson(null, true, 'تم تسجيل الخروج بنجاح', 200);

    }
}