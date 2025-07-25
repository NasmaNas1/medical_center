<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppintmentPatientResource extends JsonResource
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
            'date' => $this->appointment_date->format('Y-m-d H:i'),
            'status' => $this->status,
            'sub_specialization' => new SubSpecializationResource($this->whenLoaded('subSpecialization')),
       ];
    }
}
