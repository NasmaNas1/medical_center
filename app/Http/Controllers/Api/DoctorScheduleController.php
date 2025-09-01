<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\DoctorSchedule;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Http\Resources\DoctorScheduleResource;
use Illuminate\Support\Facades\Validator;

class DoctorScheduleController extends Controller
{
    use GeneralTrait;


    public function store(Request $request)
{   
    $doctor = $request->user(); 

    if (!$doctor instanceof Doctor) {
        return $this->responseWithJson(null, false, 'غير مصرح. هذا الإجراء للأطباء فقط.', 403);
    }
    $validated = $request->validate([
        
        'sub_specialization_id' => 'required|exists:sub_specializations,id',
        'day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
    ]);

   try {
        $schedule = DoctorSchedule::create([
            'doctor_id' =>$doctor->id,
            'sub_specialization_id' => $validated['sub_specialization_id'],
            'day' => $validated['day'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_available' => true,
        ]);

        $schedule->load('subSpecialization');

        return $this->responseWithJson(
            new DoctorScheduleResource($schedule),
            true,
            'تم إضافة الجدول بنجاح'
        );
    } catch (\Exception $e) {
        return $this->responseWithJson(null, false, 'حدث خطأ أثناء إضافة الجدول: ' . $e->getMessage(), 500);
    }
}


  


   // جلب الأوقات المتاحة لطبيب معيّن
public function getAvailableSchedules($doctorId)
{
    $doctor = Doctor::find($doctorId);
    if (!$doctor) {
        return $this->responseWithJson(null, false, 'الطبيب غير موجود', 404);
    }

    $schedules = DoctorSchedule::where('doctor_id', $doctorId)
        ->where('is_available', true)
        ->with('subSpecialization')
        ->orderByRaw("FIELD(day, 'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')")
        ->orderBy('start_time', 'asc')
        ->get();

    return $this->responseWithJson(
        DoctorScheduleResource::collection($schedules),
        true,
        'الأوقات المتاحة للطبيب'
    );
}
 

    public function getUnavailableDaysCount($doctorId)
{
    $doctor = Doctor::find($doctorId);
    if (!$doctor) {
        return $this->responseWithJson(null, false, 'الطبيب غير موجود', 404);
    }

    $count = DoctorSchedule::where('doctor_id', $doctorId)
        ->where('is_available', false)
        ->count();

    return $this->responseWithJson(['unavailable_days_count' => $count], true, 'عدد أيام العطلات');
}
public function updateSchedule(Request $request, $scheduleId)
{
    $validated = $request->validate([
        'day' => 'sometimes|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
        'start_time' => 'sometimes|date_format:H:i',
        'end_time' => 'sometimes|date_format:H:i|after:start_time',
        'is_available' => 'sometimes|boolean',
    ]);

    $schedule = DoctorSchedule::find($scheduleId);

    if (!$schedule) {
        return $this->responseWithJson(null, false, 'جدول الطبيب غير موجود', 404);
    }

    // تحقق من اليوم الحالي واليوم المطلوب تعديله
    $today = now()->format('l'); // اليوم الحالي
    $tomorrow = now()->addDay()->format('l'); // بكرا

    if (
        (isset($validated['day']) && ($validated['day'] === $today || $validated['day'] === $tomorrow)) ||
        ($schedule->day === $today || $schedule->day === $tomorrow)
    ) {
        return $this->responseWithJson(null, false, 'لا يمكنك تعديل جدول اليوم أو الغد', 400);
    }

    // تحديث الحقول
    if (isset($validated['day'])) {
        $schedule->day = $validated['day'];
    }
    if (isset($validated['start_time'])) {
        $schedule->start_time = $validated['start_time'];
    }
    if (isset($validated['end_time'])) {
        $schedule->end_time = $validated['end_time'];
    }
    if (isset($validated['is_available'])) {
        $schedule->is_available = $validated['is_available'];
    }

    $schedule->save();
    $schedule->load('subSpecialization');

    return $this->responseWithJson(
        new DoctorScheduleResource($schedule),
        true,
        'تم تعديل الجدول بنجاح'
    );
}


}
