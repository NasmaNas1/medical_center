<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\AppointmentSlotResource;
use App\Models\{Doctor, Patient, Appointment, DoctorSchedule, SubSpecialization};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class AppointmentController extends Controller
{
    use GeneralTrait;

  public function getWeeklyAppointments($doctorId, Request $request)
{
    // التحقق من وجود الطبيب
    $doctor = Doctor::find($doctorId);
    if (!$doctor) {
        return $this->responseWithJson(null, false, 'الطبيب غير موجود', 404);
    }

    $startOfWeek = now()->startOfWeek();
    $endOfWeek = now()->endOfWeek();

    $appointments = Appointment::where('doctor_id', $doctorId)
        ->whereBetween('appointment_date', [$startOfWeek, $endOfWeek])
        ->with(['patient', 'subSpecialization'])
        ->orderBy('appointment_date', 'asc')
        ->get();

    return AppointmentResource::collection($appointments);
}

public function getAppointmentsByStatus($doctorId, $status)
{
    $doctor = Doctor::find($doctorId);
    if (!$doctor) {
        return $this->responseWithJson(null, false, 'الطبيب غير موجود', 404);
    }

    $allowedStatuses = ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'];
    if (!in_array($status, $allowedStatuses)) {
        return $this->responseWithJson(null, false, 'حالة غير صالحة', 400);
    }

    $appointments = Appointment::where('doctor_id', $doctorId)
        ->where('status', $status)
        ->with(['patient', 'subSpecialization'])
        ->orderBy('appointment_date', 'asc')
        ->get();

    if ($appointments->isEmpty()) {
        return $this->responseWithJson([], true, 'لا يوجد مواعيد بهذه الحالة');
    }

    return $this->responseWithJson(AppointmentResource::collection($appointments), true, 'قائمة المواعيد حسب الحالة');
}

    // جلب المواعيد بحسب حالة
   public function getPatientsCountByStatus($doctorId)
{
    $doctor = Doctor::find($doctorId);
    if (!$doctor) {
        return $this->responseWithJson(null, false, 'الطبيب غير موجود', 404);
    }

    $statuses = ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'];

    $counts = [];

    foreach ($statuses as $status) {
        $counts[$status] = Appointment::where('doctor_id', $doctorId)
            ->where('status', $status)
            ->distinct() // تحسب عدد المرضى وليس عدد المواعيد
            ->count('patient_id');
    }

    return $this->responseWithJson($counts, true, 'عدد المرضى حسب الحالة');
}


    // المواعيد المتاحة
    public function getAvailableSlots(Request $request, $doctorId, $subSpecializationId)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = $request->input('date');

        // التحقق من عدم تجاوز 7 أيام
        if (Carbon::parse($date)->gt(now()->addDays(7)->endOfDay())) {
            return $this->responseWithJson(null, false, 'لا يمكن الحجز لأكثر من 7 أيام قادمة', 400);
        }

        $specialization = SubSpecialization::findOrFail($subSpecializationId);
        $duration = $specialization->duration;
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
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d H:i:s'))
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

                $slots[] = [
                    'time' => $current->format('H:i'),
                    'formatted_time' => $current->format('h:i A'),
                    'timestamp' => $slotKey,
                    'is_available' => !in_array($slotKey, $bookedAppointments) && !$current->lt($currentDateTime),
                ];

                $current->addMinutes($duration);
            }
        }

        return $this->responseWithJson([
            'data' => AppointmentSlotResource::collection($slots),
            'duration' => $duration,
            'date' => $date,
            'doctor_id' => $doctorId,
            'sub_specialization_id' => $subSpecializationId,
        ]);
    }

    // حجز موعد
    public function bookAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'patient_id' => 'required|exists:patients,id',
            'sub_specialization_id' => 'required|exists:sub_specializations,id',
            'appointment_date' => 'required|date_format:Y-m-d H:i|after_or_equal:now',
            'notes' => 'nullable|string|max:500',
        ], [
            'appointment_date.after_or_equal' => 'يجب أن يكون الموعد في وقت لاحق',
        ]);

        if ($validator->fails()) {
            return $this->requiredField($validator->errors()->first());
        }

        $validated = $validator->validated();
        $appointmentDateTime = Carbon::parse($validated['appointment_date']);

        // لهوس حد 7 أيام
        $maxBookingDate = now()->addDays(7)->endOfDay();
        if ($appointmentDateTime->gt($maxBookingDate)) {
            return $this->responseWithJson(null, false, 'لا يمكن الحجز لأكثر من 7 أيام قادمة', 400);
        }

        if ($appointmentDateTime->hour < 9 || $appointmentDateTime->hour >= 18) {
            return $this->responseWithJson(null, false, 'الموعد خارج ساعات العمل (9 صباحًا - 6 مساءً)', 400);
        }

        if (!$this->isSlotAvailable($validated['doctor_id'], $appointmentDateTime)) {
            return $this->responseWithJson(null, false, 'هذا الموعد غير متاح', 400);
        }

        $specialization = SubSpecialization::find($validated['sub_specialization_id']);
         // البحث عن السجل المناسب في جدول المواعيد
        $dayOfWeek = $appointmentDateTime->format('l');
        $time = $appointmentDateTime->format('H:i:s');

     $schedule = DoctorSchedule::where('doctor_id', $validated['doctor_id'])
       ->where('sub_specialization_id', $validated['sub_specialization_id'])
       ->where('day', $dayOfWeek)
       ->where('start_time', '<=', $time)
       ->where('end_time', '>', $time)
       ->where('is_available', true)
       ->first();
 
   if (!$schedule) {
       return $this->responseWithJson(null, false, 'لا يوجد جدول متاح لهذا الوقت', 400);
       }

        $appointment = Appointment::create([
            'doctor_id' => $validated['doctor_id'],
            'patient_id' => $validated['patient_id'],
            'appointment_date' => $appointmentDateTime,
            'notes' => $validated['notes'] ?? null,
            'duration' => $specialization->duration,
            'sub_specialization_id' => $validated['sub_specialization_id'],
            'schedule_id' => $schedule->id, 
            'status' => 'pending',
        ]);

        return $this->responseWithJson(new AppointmentResource($appointment), true, 'تم الحجز بنجاح', 201);
    }

    // التحقق من توفر الوقت
    private function isSlotAvailable($doctorId, Carbon $dateTime)
    {
        $exists = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $dateTime->format('Y-m-d H:i:00'))
            ->exists();

        $dayOfWeek = $dateTime->format('l');
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
