<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Doctor;
use App\Models\Patient;

class Report extends Model
{
    protected $keyType = 'string'; // لأن UUID هو نص
    public $incrementing = false; // لأنه ليس رقماً متسلسلاً

    use HasFactory ,HasApiTokens;
    protected $fillable =[
        'content',
        'doctor_id',
        'patient_id'

    ];
  public function patient()
    {
        return $this->belongsTo(Patient::class, 'uuid', 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
