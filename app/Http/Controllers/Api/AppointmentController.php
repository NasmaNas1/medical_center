<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Http\Resources\AppointmentResource;
use App\Models\{Doctor, Patient, Appointment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    use GeneralTrait;

    const WORKING_HOURS = ['start' => 9, 'end' => 18];
    const SLOT_DURATION = 30; // دقائق

    // الحصول على الأوقات المتاحة
public function getAvailableSlots(Request $request, $doctorId)
{
    $validator = Validator::make($request->all(), [
        'date' => 'required|date|after_or_equal:today'
    ], [
        'date.after_or_equal' => 'لا يمكن الحجز في تواريخ ماضية'
    ]);

    if ($validator->fails()) {
        return $this->requiredField($validator->errors()->first());
    }

    try {
        $date = Carbon::parse($request->date);
        $doctor = Doctor::findOrFail($doctorId);

        // توليد الأوقات المتاحة
        $slots = $this->generateTimeSlots($date);

        // استبعاد الأوقات المحجوزة
        $availableSlots = $this->filterAvailableSlots($doctorId, $date, $slots);

        return $this->responseWithJson([
            'doctor' => $doctor->name,
            'date' => $date->format('Y-m-d'),
            'available_slots' => $availableSlots
        ], true);

    }
     catch (\Exception $e) {
        return $this->responseWithJson(null, false, $e->getMessage(), 500);
    }
}

    // إنشاء حجز جديد
    public function bookAppointment(Request $request)
{
    $validator = Validator::make($request->all(), [
        'doctor_id' => 'required|exists:doctors,id',
        'patient_id' => 'required|exists:patients,uuid',
        'appointment_date' => 'required|date_format:Y-m-d H:i|after_or_equal:now',
        'notes' => 'nullable|string|max:500'
    ], [
        'appointment_date.after_or_equal' => 'يجب أن يكون الموعد في وقت لاحق'
    ]);

    if ($validator->fails()) {
        return $this->requiredField($validator->errors()->first());
    }

    try {
        $appointmentDateTime = Carbon::parse($request->appointment_date);

        // التحقق من ساعات العمل
        if (!$this->isWithinWorkingHours($appointmentDateTime)) {
            return $this->responseWithJson(
                null, 
                false, 
                ['appointment_date' => 'الموعد خارج ساعات العمل (9 صباحًا - 6 مساءً)'], 
                400
            );
        }

        // التحقق من توفر الموعد
        if (!$this->isSlotAvailable($request->doctor_id, $appointmentDateTime)) {
            return $this->responseWithJson(
                null, 
                false, 
                ['appointment_date' => 'هذا الموعد غير متاح'], 
                400
            );
        }

        // إنشاء الحجز
        $appointment = Appointment::create([
            'doctor_id' => $request->doctor_id,
            'patient_id' => $request->patient_id,
            'appointment_date' => $appointmentDateTime,
            'notes' => $request->notes,
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

    // إدارة حالة الحجز
    public function updateStatus(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'status' => 'required|in:pending,confirmed,cancelled,completed'
    ]);

    if ($validator->fails()) {
        return $this->requiredField($validator->errors()->first());
    }

    try {
        $appointment = Appointment::findOrFail($id);
        
        if ($appointment->status === 'completed') {
            return $this->responseWithJson(
                null, 
                false, 
                'لا يمكن تعديل الحجز المكتمل', 
                403
            );
        }

        $appointment->update(['status' => $request->status]);

        return $this->responseWithJson(
            new AppointmentResource($appointment), 
            true, 
            'تم تحديث الحالة بنجاح'
        );

    }  catch (\Exception $e) {
        return $this->responseWithJson(null, false, $e->getMessage(), 500);
    }
}

    // توليد الأوقات المتاحة
    private function generateTimeSlots(Carbon $date)
    {
        $start = $date->copy()
            ->setTime(self::WORKING_HOURS['start'], 0)
            ->timezone(config('app.timezone'));

        $end = $date->copy()
            ->setTime(self::WORKING_HOURS['end'], 0);

        $slots = [];
        while ($start < $end) {
            $slots[] = $start->format('H:i');
            $start->addMinutes(self::SLOT_DURATION);
        }
        
        return $slots;
    }

    // تصفية الأوقات المحجوزة
    private function filterAvailableSlots($doctorId, Carbon $date, array $slots)
    {
        $booked = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('appointment_date')
            ->map(fn ($dt) => Carbon::parse($dt)->format('H:i'))
            ->toArray();

        return array_values(array_diff($slots, $booked));
    }

    // إنشاء الحجز مع التحقق الشامل
    private function createAppointment(array $data)
    {
        $appointmentDate = Carbon::parse($data['appointment_date'])
            ->timezone(config('app.timezone'));

        $this->validateAppointmentTime($appointmentDate);

        return Appointment::create([
            'doctor_id' => $data['doctor_id'],
            'patient_id' => $data['patient_id'],
            'appointment_date' => $appointmentDate,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending'
        ]);
    }

    // التحقق من وقت الحجز
    private function validateAppointmentTime(Carbon $datetime)
    {
        if ($datetime->isPast()) {
            throw new \Exception('لا يمكن الحجز في وقت ماضي');
        }

        $start = $datetime->copy()->setTime(self::WORKING_HOURS['start'], 0);
        $end = $datetime->copy()->setTime(self::WORKING_HOURS['end'], 0);

        if (!$datetime->between($start, $end)) {
            throw new \Exception('الوقت خارج ساعات العمل');
        }

        if (Appointment::where('appointment_date', $datetime)->exists()) {
            throw new \Exception('هذا الموعد محجوز مسبقاً');
        }
    }

    // صلاحيات تغيير الحالة
    private function authorizeStatusUpdate(Appointment $appointment)
    {
        // يمكن إضافة منطق الصلاحيات هنا
        if ($appointment->status === 'completed') {
            throw new \Exception('لا يمكن تعديل الحجز المكتمل');
        }
    }

    // تحقق من بيانات الطلب
    private function validateAppointmentRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'patient_id' => 'required|exists:patients,uuid',
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:now',
                function ($attribute, $value, $fail) {
                    $date = Carbon::parse($value);
                    if ($date->isWeekend()) {
                        $fail('لا يمكن الحجز في العطل الأسبوعية');
                    }
                }
            ],
            'notes' => 'nullable|string|max:500'
        ], [
            'appointment_date.after_or_equal' => 'يجب أن يكون الموعد في وقت لاحق'
        ]);

    }
      private function isWithinWorkingHours(Carbon $datetime)
    {
    $start = $datetime->copy()->setTime(9, 0);
    $end = $datetime->copy()->setTime(18, 0);
    
    return $datetime->between($start, $end);
   }
   
   private function isSlotAvailable($doctorId, Carbon $datetime)
   {
    return !Appointment::where('doctor_id', $doctorId)
        ->where('appointment_date', $datetime)
        ->whereIn('status', ['pending', 'confirmed'])
        ->exists();
  }
}