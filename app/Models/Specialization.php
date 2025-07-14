<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
    ];


    public function subSpecializations()
{
    return $this->hasMany(\App\Models\SubSpecialization::class);
}

}
