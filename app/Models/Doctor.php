<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Specialization;
use App\Models\Patient;
use App\Models\Rating;
use App\Models\Appointment;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Doctor extends Authenticatable
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
    public function ratings()
   {
    return $this->hasManyThrough(
        Rating::class,
        Appointment::class,
        'doctor_id',        // FK in appointments table
        'appointment_id',    // FK in appointment_ratings table
        'id',               // Local key in doctors table
        'id'                // Local key in appointments table
    );

   }

  protected $hidden = [
    'password', 
    'remember_token',
];
 protected $casts = [
   'user_type' => 'string'
    ];
}
