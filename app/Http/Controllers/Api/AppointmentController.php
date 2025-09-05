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
use Illuminate\Support\Facades\Schema;

class AppointmentController extends Controller
{
    use GeneralTrait;
  
private function autoCloseExpiredAppointments(int $doctorId, int $graceMinutes = 0, string $missedStatus = 'no_show'): void
{
    $threshold = now()->subMinutes($graceMinutes);

    $q = Appointment::where('doctor_id', $doctorId)
        ->whereIn('status', ['pending', 'confirmed'])
        ->whereRaw('TIMESTAMPADD(MINUTE, duration, appointment_date) <= ?', [$threshold]);

    // طبّق الشرط بس إذا العمود موجود
    if (Schema::hasColumn('appointments', 'attended_at')) {
        $q->whereNull('attended_at');
    }

    $q->update([
        'status'     => $missedStatus,
        'updated_at' => now(),
    ]);
}
  public function getWeeklyAppointments($doctorId, Request $request)
{
    $this->autoCloseExpiredAppointments($doctorId, 0, 'no_show');
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
     $this->autoCloseExpiredAppointments($doctorId, 0, 'no_show');

    $doctor = Doctor::find($doctorId);
    if (!$doctor) {
        return $this->responseWithJson(null, false, 'الطبيب غير موجود', 404);
    }

    $allowedStatuses = ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'];
    if (!in_array($status, $allowedStatuses ,true)) {
        return $this->responseWithJson(null, false, 'حالة غير صالحة', 400);
    }

    $appointments = Appointment::where('doctor_id', $doctorId)
        ->where('appointments.status', $status)
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
    $this->autoCloseExpiredAppointments($doctorId, 0, 'no_show');
    
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
    // التحقق من الطبيب ونوع الحجز
    $doctor = Doctor::findOrFail($doctorId);
    $sub = SubSpecialization::findOrFail($subSpecializationId);
    $duration = (int) $sub->duration;

    // نطاق الأسبوع: 7 أيام بدءاً من اليوم
    $startDate = now()->startOfDay();
    $endDate   = now()->copy()->addDays(6)->endOfDay();
    $now       = now();

    // جداول الطبيب لهالنوع مجمعة حسب اليوم
    $schedulesByDay = DoctorSchedule::where('doctor_id', $doctorId)
        ->where('is_available', true)
        ->where('sub_specialization_id', $subSpecializationId)
        ->orderBy('start_time')
        ->get()
        ->groupBy('day'); // Sunday .. Saturday

    // المواعيد المحجوزة ضمن الأسبوع (لا ترجع الملغاة)
    $booked = Appointment::where('doctor_id', $doctorId)
        ->whereBetween('appointment_date', [$startDate, $endDate])
        ->where('status', '!=', 'cancelled')
        ->pluck('appointment_date')
        ->map(fn ($dt) => Carbon::parse($dt)->format('Y-m-d H:i:s'))
        ->all();
    $bookedSet = array_flip($booked);

    $days = [];
    for ($i = 0; $i < 7; $i++) {
        $date     = now()->copy()->addDays($i);
        $dateStr  = $date->format('d-m-Y');
        $weekday  = $date->format('l');

        $daySlots = [];

        foreach ($schedulesByDay->get($weekday, collect()) as $schedule) {
            $start = Carbon::parse("$dateStr {$schedule->start_time}");
            $end   = Carbon::parse("$dateStr {$schedule->end_time}");

            // تقسيم حسب المدة
            for ($current = $start->copy(); $current->copy()->addMinutes($duration)->lte($end); $current->addMinutes($duration)) {
                // استبعاد أي وقت ماضي نهائياً
                if ($current->lt($now)) {
                    continue;
                }

                $key = $current->format('d-m-Y H:i');

                // إذا محجوز نتجاوزه (ما نرجعه أبداً)
                if (isset($bookedSet[$key])) {
                    continue;
                }

                $daySlots[] = [
                    'time'           => $current->format('H:i'),
                    'formatted_time' => $current->format('h:i A'),
                    'timestamp'      => $key,
                    'is_available'   => true, // دائماً true لأننا استبعدنا المحجوز والماضي/
                ];
            }
        }

        // نحول الـ slots لريسورس
        $days[] = [
            'date'    => $dateStr,
            'weekday' => $weekday,
            'slots'   => AppointmentSlotResource::collection(collect($daySlots)),
        ];
    }

    // اختياري: احذف الأيام اللي ما فيها أي slot
    // $days = array_values(array_filter($days, fn ($d) => count($d['slots']) > 0));

    return $this->responseWithJson([
        'duration' => $duration,
        'range' => [
            'start' => $startDate->toDateString(),
            'end'   => $endDate->toDateString(),
        ],
        'days' => $days,
    ], true, 'الفترات المتاحة للأسبوع القادم');
}
    // حجز موعد
    public function bookAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'patient_id' => 'required|exists:patients,id',
            'sub_specialization_id' => 'required|exists:sub_specializations,id',
            'appointment_date' => 'required|date_format:d-m-Y H:i|after_or_equal:now',
            'notes' => 'nullable|string|max:500',
        ], [
            'appointment_date.after_or_equal' => 'يجب أن يكون الموعد في وقت لاحق',
        ]);

        if ($validator->fails()) {
            return $this->requiredField($validator->errors()->first());
        }

        $validated = $validator->validated();
        $appointmentDateTime = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $validated['appointment_date'])
                                   ->seconds(0);
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
    // نخلي حدود المقارنة دقيقة وحدة (نطنش الثواني)
    $slotStart = $dateTime->copy()->startOfMinute();
    $slotEnd   = $dateTime->copy()->endOfMinute();

    $exists = Appointment::where('doctor_id', $doctorId)
        ->whereBetween('appointment_date', [$slotStart, $slotEnd])
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


public function markAttendance(Request $request, $appointmentId)
{
    $validator = Validator::make($request->all(), [
        'attended' => 'required|boolean', // true إذا حضر، false لو ما حضر
        'notes' => 'nullable|string|max:500',
    ]);

    if ($validator->fails()) {
        return $this->requiredField($validator->errors()->first());
    }

    $appointment = Appointment::find($appointmentId);
    if (!$appointment) {
        return $this->responseWithJson(null, false, 'الموعد غير موجود', 404);
    }

    if (!in_array($appointment->status, ['pending','confirmed'])) {
        return $this->responseWithJson(null, false, 'لا يمكن تعديل هذه الحالة', 409);
    }

    $attended = $request->boolean('attended');

    // إذا حابّة تمنعي no_show قبل انتهاء الموعد + فترة سماح
    $duration = (int)($appointment->duration ?? optional($appointment->subSpecialization)->duration ?? 0);
    $end = \Carbon\Carbon::parse($appointment->appointment_date)->addMinutes($duration);
    $grace = (int) env('APPOINTMENT_GRACE_MIN', 10);

    if ($attended === false && now()->lt($end->copy()->addMinutes($grace))) {
        return $this->responseWithJson(null, false, "لا يمكنك اعتبار الموعد غياباً قبل مرور فترة السماح.", 422);
    }

    // 🔴 الشرط الجديد: منع completed إذا الموعد لسا ما صار
    if ($attended === true && now()->lt(\Carbon\Carbon::parse($appointment->appointment_date))) {
        return $this->responseWithJson(null, false, "لا يمكنك اعتبار الموعد مكتمل قبل موعده.", 422);
    }

    $appointment->status = $attended ? 'completed' : 'no_show';

    if ($request->filled('notes')) {
        $appointment->notes = $request->input('notes');
    }

    $appointment->save();

    return $this->responseWithJson(new AppointmentResource($appointment), true, 'تم تحديث حالة الموعد');
}

    }
