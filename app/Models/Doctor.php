<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Specialization;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Doctor extends Model
{
    use HasFactory ,  HasApiTokens;
   
    protected $fillable = [
        'name',
        'image',
        'specialization_id',
        'email',
        'about_doctor',
        'practice',
    ];

    public function Specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    public function Patients(){
        return $this->belongsToMany(Patient::class, 'doctor_patient', 'doctor_id', 'patient_id');
    }
    public function appointments()
  {
    return $this->hasMany(Appointment::class);
  }
}
