<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'id' => $this->id,
            'doctor_id' => $this->doctor_id,
            'sub_specialization_id'=>$this->subspecialization?? null,
            'duration' => $this->subspecialization->duration ?? null,'day' => $this->day,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'is_available' => $this->is_available,
          
        ];
    }
}
