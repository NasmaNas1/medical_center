<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Specialization;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Doctor extends Model
{
    use HasFactory ,  HasApiTokens , Notifiable;
   
    protected $fillable = [
        'name',
        'image',
        'specialization_id',
        'email',
        'about_doctor',
        'practice',
        'password',
    ];

    public function Specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

       public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function Patients(){
        return $this->belongsToMany(Patient::class, 'doctor_patient', 'doctor_id', 'patient_id');
    }
    public function appointments()
  {
    return $this->hasMany(Appointment::class);
  }
  public function schedules()
  {
    return $this->hasMany(DoctorSchedule::class);
  }


  public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
  protected $hidden = [
    'password', 
    'remember_token',
];
 protected $casts = [
   'user_type' => 'string'
    ];
}
