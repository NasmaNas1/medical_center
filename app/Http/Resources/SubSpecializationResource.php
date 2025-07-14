<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubSpecializationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id'=>$this->id,
            'specialization' => $this->specialization->type,
            'name'=>$this->name,
            'duration'=>$this->duration,
               
        ];
    }
}
