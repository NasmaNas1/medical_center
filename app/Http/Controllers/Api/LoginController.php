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
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;

class LoginController extends Controller
{
    use GeneralTrait;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'user_type' => 'required|in:patient,doctor' 
        ], [
            'email.required' => 'يرجى إدخال البريد الإلكتروني',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'password.required' => 'يرجى إدخال كلمة المرور',
            'user_type.required' => 'يرجى تحديد نوع المستخدم (pationt , doctor)',
            'user_type.in' => 'نوع المستخدم غير صحيح'
     
        ]);
        if($validator->fails()){
            return $this->responseWithJson(
            null,
            false,
            'بيانات الدخول غير صحيحة',
            401
        );
        }
      
if ($request->user_type === 'patient') {
        $user = Patient::where('email', $request->email)->first();
    } else {
        $user = Doctor::where('email', $request->email)->first();
    }

    // التحقق من صحة بيانات الدخول
    if (!$user || !Hash::check($request->password, $user->password)) {
        return $this->responseWithJson(
            null,
            false,
            'البريد الإلكتروني أو كلمة المرور غير صحيحة',
            401
        );
    }

    // إنشاء توكن
    $token = $user->createToken('auth-token',[$request->user_type])->plainTextToken;
            return $this->responseWithJson(
                [
                    'token' => $token,
                    'user_type' => $request->user_type,
                    'user' => $request->user_type === 'doctor' 
                    ? new DoctorResource($user) 
                    : new PatientResource($user),
    
                ],
                true,
                null,
                200
            );    }

    public function logout(Request $request)
{
    // التحقق من وجود مستخدم مصادق عليه
    if (!$request->user()) {
        return $this->responseWithJson(null, false, 'المستخدم غير مصادق عليه', 401);
    }
    
    try {
        // حذف token المصادقة الحالي
        $request->user()->currentAccessToken()->delete();
        
        return $this->responseWithJson(null, true, 'تم تسجيل الخروج بنجاح', 200);
    } catch (\Exception $e) {
        return $this->responseWithJson(null, false, 'حدث خطأ أثناء تسجيل الخروج: ' . $e->getMessage(), 500);
    }
}
}