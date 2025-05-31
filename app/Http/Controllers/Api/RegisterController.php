<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient; 
use App\Http\Traits\GeneralTrait;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RegisterController extends Controller
{
    use GeneralTrait;

    public function register(Request $request)
    {  
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:patients,email',  
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|min:10|max:15|unique:patients,phone',
            'birth_date' => 'required|date|before_or_equal:today',
            'address' => ['required', 'string', 'regex:/^[^\d]*$/'],
            'gender' => 'required|in:ذكر,انثى',
            
        ],
        [
            'name.required' => 'يرجى إدخال اسم.',
            'birth_date.required' => 'ادخل تاريخ الميلاد بشكل صحيح',
            'password.required' => 'يرجى إدخال كلمة المرور.',
            'phone.required' => 'يرجى إدخال رقم الهاتف بشكل صحيح.',
            'phone.unique'=>'ادخل رقم هاتف متاح للخدمة',
            'email.email' => 'يرجى إدخال بريد إلكتروني صالح.',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقًا.',
            'password.confirmed' => 'كلمة المرور غير متطابقة.',
            'address.required' => 'يرجى ادخال العنوان',
            'address.regex' => 'العنوان يجب أن يحتوي على أحرف فقط ولا يحتوي على أرقام.',
            'gender.required' =>'يرجى اختيار الجنس',
            'gender.in' => 'القيمة المدخلة غير صحيحة',
        ]);

        if ($validator->fails()) 
        {
            $errors = $validator->errors()->toArray();
            $formattedErrors = [];

            foreach ($errors as $field => $messages) {
                $formattedErrors[$field] = $messages[0]; 
            }

            return $this->responseWithJson(null, false, $formattedErrors, 400);
        }

        $patient =Patient::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' =>$request->password,
            'phone' => $request->phone,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
            'gender'=>$request->gender,
        ]);

        return $this->responseWithJson('تم التسجيل بنجاح');
    }
}