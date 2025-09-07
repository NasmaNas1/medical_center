<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopDoctorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'doctor_id'   => $this->id,
            'doctor_name' => $this->name,
            'specialization_id'   => $this->specialization->id ?? null,
            'specialization_name' => $this->specialization->type ?? null,
            'average_rating'      => round($this->ratings_avg_rating, 2),
        ];
    }
}
