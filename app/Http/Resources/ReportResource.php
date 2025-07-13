<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'doctor_name' => $this->doctor->name ?? null,
            'doctor_id'=>$this->doctor_id,
            'patient_id'=>$this->patient_id,
            'content'=>$this->content,
        ];
    }
}
