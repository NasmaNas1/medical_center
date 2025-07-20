<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash; // استدعاء مكتبة Hash
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Doctor;
use Illuminate\Notifications\Notifiable;
use App\Models\Appointment;



class Patient extends Authenticatable
{

    use HasFactory , HasApiTokens , Notifiable;
 
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'phone',
        'birth_date',
        'address',
        'gender',
        
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];
   
    protected $dates = [
        'birth_date',
        'created_at',
        'updated_at',
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($patient) {
    //         $patient->uuid = (string) Str::uuid(); // توليد UUID عند الإنشاء
    //     });
    // }

    public function Doctors(){
        return $this->belongsToMany(Doctor::class, 'doctor_patient', 'patient_id', 'doctor_id');
    }
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
