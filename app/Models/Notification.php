<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Notification extends Model
{
   
    use HasFactory ,  HasApiTokens;
    protected $fillable = [
       'type',
       'message',
       'sent_at',
       'read',
       'appointment_id'
    ];
}
