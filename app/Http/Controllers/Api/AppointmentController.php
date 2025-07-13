<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\AppointmentSlotResource;
use App\Models\{Doctor, Patient, Appointment};
use Illuminate\Http\Request;
use App\Models\DoctorSchedule;
use App\Models\SubSpecialization;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    use GeneralTrait;

// هلا هون عم جيب كلشي مواعيد عند كل طبيب مواعيد لاسبوع
    public function getWeeklyAppointments($doctorId,Request $request )
{   
    $startOfWeek = now()->startOfWeek();
    $endOfWeek = now()->endOfWeek();

    $appointments = Appointment::where('doctor_id', $doctorId)
        ->whereBetween('appointment_date', [$startOfWeek, $endOfWeek])
        ->with(['patient', 'subSpecialization'])
        ->orderBy('appointment_date', 'asc')
        ->get();

    return AppointmentResource::collection($appointments);
}
// بدي رجع المرضى حسب الحالة يعني كلشي كانسل مثلا
public function getAppointmentsByStatus($doctorId, $status)
{   
            // التحقق من أن الطبيب موجود
        $doctor = Doctor::find($doctorId);
        if (!$doctor) {
            return  $this->responseWithJson(['message' => 'الطبيب غير موجود'],
             404);
        }
        
  $allowedStatuses = ['cancelled', 'completed', 'upcoming', 'pending', 'confirmed'];
        if (!in_array($status, $allowedStatuses)) {
            return  $this->responseWithJson(['message' => 'حالة غير صالحة'],
             400);
        }

        // بناء الاستعلام
        $query = Appointment::where('doctor_id', $doctorId)
            ->where('status', $status)
            ->with(['patient.user', 'subSpecialization']);

        // إضافة شروط زمنية حسب الحالة
        if ($status === 'upcoming') {
            $query->where('appointment_date', '>', Carbon::now());
        } elseif ($status === 'completed') {
            $query->where('appointment_date', '<', Carbon::now());
        }

        $appointments = $query->orderBy('appointment_date', 'asc')->get();

        return AppointmentResource::collection($appointments);
    }



    // الحصول على الأوقات المتاحة
public function getAvailableSlots(Request $request, $doctorId, $subSpecializationId)
{
    $request->validate([
        'date' => 'required|date_format:Y-m-d'
    ]);

    $specialization = SubSpecialization::findOrFail($subSpecializationId);
    $duration = $specialization->duration;

    $date = $request->input('date');
    $dayOfWeek = Carbon::parse($date)->format('l');

    $schedules = DoctorSchedule::where('doctor_id', $doctorId)
        ->where('is_available', true)
        ->where('day', $dayOfWeek)
        ->where('sub_specialization_id', $subSpecializationId)
        ->get();

    $bookedAppointments = Appointment::where('doctor_id', $doctorId)
        ->whereDate('appointment_date', $date)
        ->where('status', '!=', 'cancelled')
        ->pluck('appointment_date')
        ->map(function ($date) {
            return Carbon::parse($date)->format('Y-m-d H:i:s');
        })
        ->toArray();

    $slots = [];
    $currentDateTime = now();

    foreach ($schedules as $schedule) {
        $start = Carbon::parse("$date {$schedule->start_time}");
        $end = Carbon::parse("$date {$schedule->end_time}");

        $current = $start->copy();
        
        while ($current->copy()->addMinutes($duration) <= $end) {
            $slotEnd = $current->copy()->addMinutes($duration);
            $slotKey = $current->format('Y-m-d H:i:s');
            
            // التحقق من عدم وجود حجز في هذا الوقت
            $isBooked = in_array($slotKey, $bookedAppointments);
            
            // التحقق من أن الموعد لم يمر بعد
            $isPast = $current->lt($currentDateTime);
            
            // التحقق من أن الموعد ضمن ساعات العمل
            $isWithinHours = ($current->hour >= 9 && $slotEnd->hour < 18);

            $slots[] = [
                'time' => $current->format('H:i'),
                'formatted_time' => $current->format('h:i A'),
                'timestamp' => $slotKey,
                'is_available' => !$isBooked && !$isPast && $isWithinHours
            ];

            $current->addMinutes($duration);
        }
    }

    // ترتيب الفتحات الزمنية
    usort($slots, function ($a, $b) {
        return strtotime($a['timestamp']) - strtotime($b['timestamp']);
    });

    return $this->responseWithJson([
        'data' => AppointmentSlotResource::collection($slots),
        'duration' => $duration,
        'date' => $date,
        'doctor_id' => $doctorId,
        'sub_specialization_id' => $subSpecializationId
    ]);
}

   public function bookAppointment(Request $request)
{
    $validator = Validator::make($request->all(), [
        'doctor_id' => 'required|exists:doctors,id',
        'patient_id' => 'required|exists:patients,uuid',
        'sub_specialization_id' => 'required|exists:sub_specializations,id',
        'appointment_date' => 'required|date_format:Y-m-d H:i|after_or_equal:now',
        'notes' => 'nullable|string|max:500'
    ], [
        'appointment_date.after_or_equal' => 'يجب أن يكون الموعد في وقت لاحق'
    ]);

    if ($validator->fails()) {
        return $this->requiredField($validator->errors()->first());
    }

    try {
        $validated = $validator->validated();
        $appointmentDateTime = Carbon::parse($validated['appointment_date']);

        // التحقق من ساعات العمل (9AM - 6PM)
        $hour = $appointmentDateTime->hour;
        if ($hour < 9 || $hour >= 18) {
            return $this->responseWithJson(
                null, 
                false, 
                ['appointment_date' => 'الموعد خارج ساعات العمل (9 صباحًا - 6 مساءً)'], 
                400
            );
        }

        // التحقق من توفر الموعد (دالة يجب تعريفها)
        if (!$this->isSlotAvailable($validated['doctor_id'], $appointmentDateTime)) {
            return $this->responseWithJson(
                null, 
                false, 
                ['appointment_date' => 'هذا الموعد غير متاح'], 
                400
            );
        }

        $specialization = SubSpecialization::find($validated['sub_specialization_id']);

        // إنشاء الحجز
        $appointment = Appointment::create([
            'doctor_id' => $validated['doctor_id'],
            'patient_id' => $validated['patient_id'],
            'appointment_date' => $appointmentDateTime,
            'notes' => $validated['notes'] ?? null,
            'duration' => $specialization->duration,
            'sub_specialization_id' => $validated['sub_specialization_id'],
            'status' => 'pending'
        ]);

        return $this->responseWithJson(
            new AppointmentResource($appointment), 
            true, 
            'تم الحجز بنجاح', 
            201
        );

    } catch (\Exception $e) {
        return $this->responseWithJson(null, false, $e->getMessage(), 500);
    }
}

// دالة التحقق من توفر الموعد (يجب إضافتها في نفس الكلاس)
private function isSlotAvailable($doctorId, Carbon $dateTime)
{
    // التحقق من عدم وجود موعد في نفس الوقت مع نفس الطبيب
    $exists = Appointment::where('doctor_id', $doctorId)
        ->where('appointment_date', $dateTime->format('Y-m-d H:i:00'))
        ->exists();

    // التحقق من أن الموعد ضمن الجدول الزمني للطبيب
    $dayOfWeek = $dateTime->format('l'); // Sunday, Monday, etc.
    $time = $dateTime->format('H:i:s');
    
    $scheduleExists = DoctorSchedule::where('doctor_id', $doctorId)
        ->where('day', $dayOfWeek)
        ->where('start_time', '<=', $time)
        ->where('end_time', '>=', $time)
        ->where('is_available', true)
        ->exists();

    return !$exists && $scheduleExists;
}
   
}