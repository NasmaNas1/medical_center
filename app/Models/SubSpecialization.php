<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use  App\Models\Specialization;
use  App\Models\Appointment;
class SubSpecialization extends Model
{
    use HasFactory ,  HasApiTokens;
    protected $fillable = [
         'name',
         'specialization_id',
         'duration'
    ];

    public function specialization()
  {
    return $this->belongsTo(Specialization::class);
  }

  public function appointments()
  {
    return $this->hasMany(Appointment::class);
  }
}
