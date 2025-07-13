<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Notification;
use App\Models\SubSpecialization;
use App\Models\DoctorSchedule;
use Laravel\Sanctum\HasApiTokens;

class Appointment extends Model
{
    use HasFactory , HasApiTokens;
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'appointment_date',
        'status',
        'sub_specialization_id',
        'duration',
        'cancellation_reason',
        'schedule_id',
        
    ];
    public function doctor()
{
    return $this->belongsTo(Doctor::class);
}

public function patient()
{
    return $this->belongsTo(Patient::class, 'patient_id', 'uuid');
}

public function service()
{
    return $this->belongsTo(SubSpecialization::class, 'sub_specialization_id');
}

public function schedule()
{
    return $this->belongsTo(DoctorSchedule::class);
}

public function notifications()
{
    return $this->hasMany(Notification::class);
}
protected $casts = [
    'appointment_date' => 'datetime'
];

protected $dates = ['start_time', 'end_time'];

}
