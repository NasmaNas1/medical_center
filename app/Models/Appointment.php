<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Patient;
use App\Models\Doctor;
use Laravel\Sanctum\HasApiTokens;

class Appointment extends Model
{
    use HasFactory , HasApiTokens;
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'appointment_date',
        'status',
        'start_time',
        'end_time',
        
    ];
    public function doctor()
{
    return $this->belongsTo(Doctor::class);
}

public function patient()
{
    return $this->belongsTo(Patient::class, 'patient_id', 'uuid');
}
protected $casts = [
    'appointment_date' => 'datetime'
];

protected $dates = ['start_time', 'end_time'];

}
