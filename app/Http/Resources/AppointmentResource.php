<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
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
            'patient' => $this->patient->name,
            'date' => $this->appointment_date->format('Y-m-d H:i'),
            'status' => $this->status,
            'notes' => $this->notes,
            'type' => $this->subSpecialization->name,
            'duration' => $this->duration
        ];
    }
}