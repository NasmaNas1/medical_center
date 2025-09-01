<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Doctor;
use App\Models\Appointment;

class DoctorSchedule extends Model
{
   use HasFactory ;
   
    protected $fillable = [
        'day',
        'doctor_id',
        'start_time',
        'sub_specialization_id',
        'end_time',
        'is_available',
    ];
  
    public function doctor()
{
    return $this->belongsTo(Doctor::class);
}

public function appointments()
{
    return $this->hasMany(Appointment::class);
}

public function subSpecialization()
    {
        return $this->belongsTo(SubSpecialization::class, 'sub_specialization_id');
    }

protected $casts = [
       
        'day' => 'string'
    ];
}
