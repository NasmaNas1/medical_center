<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
           'name' => $this -> name,
           'image'=> asset('storage/' . $this->image),
           'specialization_id' => $this->specialization->type,
           'email' => $this -> email,
           'practice'=>$this->practice,
           'about_doctor'=> $this -> about_doctor,
        ];
    }
}
