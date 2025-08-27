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
    $validated = $request->validate([
        'doctor_id' => 'required|exists:doctors,id',
        'sub_specialization_id' => 'required|exists:sub_specializations,id',
        'day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
    ]);

   try {
        $schedule = DoctorSchedule::create([
            'doctor_id' => $validated['doctor_id'],
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


    // تحديث حالة التوفر (is_available) لجدول الطبيب
    public function updateAvailability(Request $request, $scheduleId)
    {
        $validator = Validator::make($request->all(), [
            'is_available' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->responseWithJson(null, false, $validator->errors()->first(), 400);
        }

        $schedule = DoctorSchedule::find($scheduleId);

        if (!$schedule) {
            return $this->responseWithJson(null, false, 'جدول الطبيب غير موجود', 404);
        }

        $schedule->is_available = $request->input('is_available');
        $schedule->save();

            return $this->responseWithJson(new DoctorScheduleResource($schedule), true, 'تم تحديث التوفر بنجاح');

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

}
