<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ReportResource;
use App\Http\Traits\GeneralTrait;
use App\Models\Report;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    use GeneralTrait;

    /**
     * إنشاء تقرير طبي جديد
     */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|uuid|exists:patients,uuid',
            'content' => 'required|string|min:10'
        ]);

        if ($validator->fails()) {
            return $this->requiredField($validator->errors()->first());
        }

      $doctor = auth('doctor')->user();

    if (!$doctor) {
        return $this->responseWithJson(null, false, 'يجب تسجيل الدخول كطبيب', 401);
    }

        try {
            // إنشاء التقرير
            $report =Report::create([
                'patient_id' => $request->patient_id,
                'doctor_id' => $doctor->id,
                'content' => $request->content
            ]);

            // إرجاع التقرير الجديد كـ Resource
            return $this->responseWithJson(
                new ReportResource($report),
                true,
                'تم إضافة التقرير بنجاح',
                201
            );

        } catch (\Exception $e) {
            return $this->responseWithJson(
                null,
                false,
                'حدث خطأ أثناء حفظ التقرير: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * الحصول على جميع تقارير مريض معين
     */
    public function getPatientReports($patient_uuid)
    {
        // التحقق من وجود المريض
        if (!Patient::where('uuid', $patient_uuid)->exists()) {
            return $this->notFoundResponse('المريض غير موجود');
        }

        try {
            // الحصول على التقارير مع تحميل علاقة الطبيب
            $reports = Report::with('doctor')
                ->where('patient_id', $patient_uuid)
                ->orderBy('created_at', 'desc')
                ->get();

            // إرجاع التقارير كـ Resource collection
            return $this->responseWithJson(
                ReportResource::collection($reports),
                true,
                'تم استرجاع التقارير بنجاح',
                200
            );

        } catch (\Exception $e) {
            return $this->responseWithJson(
                null,
                false,
                'حدث خطأ أثناء استرجاع التقارير: ' . $e->getMessage(),
                500
            );
        }
    }
}