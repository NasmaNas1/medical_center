<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubSpecialization;

class Specialization extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
    ];


    public function subSpecializations()
{
    return $this->hasMany(SubSpecialization::class);
}

}
