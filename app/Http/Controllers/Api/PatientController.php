<?php

namespace App\Http\Controllers\Api;

use App\Models\Patient;
use App\Http\Resources\PatientResource;
use App\Http\Resources\AppintmentPatientResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\GeneralTrait;
use App\Models\Appointment;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    use GeneralTrait;


    public function index($id)
    {
        try {
            $patient = Patient::findOrFail($id);
            return $this->responseWithJson(
                new PatientResource($patient), // استخدام new PatientResource()
                true
            );
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->responseWithJson(
                null,
                false,
                ['message' => 'المريض غير موجود'],
                404
            );
        } catch (\Exception $e) {
            return $this->responseWithJson(
                null,
                false,
                ['message' => 'حدث خطأ غير متوقع'],
                500
            );
        }
    }
    public function update(Request $request, $id)
    {
        try {
            // البحث عن المريض أو إرجاع خطأ 404
            $patient = Patient::findOrFail($id);

            // تحقق من البيانات المدخلة
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|unique:patients,phone,'.$id,
                'email' => 'sometimes|email|unique:patients,email,'.$id,
                'birth_date' => 'sometimes|date|before:today',
                'gender' => 'sometimes|in:انثى,ذكر,other',
                'address' => 'sometimes|string|max:500',
                

            ]);

            if ($validator->fails()) {
                return $this->responseWithJson(
                    null,
                    false,
                    ['errors' => $validator->errors()],
                    422
                );
            }

            // تحديث البيانات
            $patient->update($request->all());

            return $this->responseWithJson(
                new PatientResource($patient),
                true,
                ['message' => 'تم تحديث المريض بنجاح'],
                200
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->responseWithJson(
                null,
                false,
                ['message' => 'لم يتم العثور على المريض'],
                404
            );
        } catch (\Exception $e) {
            return $this->responseWithJson(
                null,
                false,
                ['message' => 'خطأ في التحديث: '.$e->getMessage()],
                500
            );
        }
    }

    public function appointments($id){
      try{  $patient=Patient::findOrFail($id);
        $appointments = $patient->appointments()->with(['doctor', 'subSpecialization'])->get();
         
         return   $this->responseWithJson(
             AppintmentPatientResource::collection($appointments ),  
            true
        );
    }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return $this->responseWithJson(
            null,
            false,
            ['message' => 'المريض غير موجود'],
            404
        );
    }
}
}