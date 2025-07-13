<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Doctor;
use App\Models\Appointment;

class DoctorSchedule extends Model
{
   use HasFactory ,  HasApiTokens;
   
    protected $fillable = [
        'date',
        'doctor_id',
        'start_time',
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
}
