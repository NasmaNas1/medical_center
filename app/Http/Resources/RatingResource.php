<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
      return [
            'id'             => $this->id,
            'appointment_id'  => $this->appointment_id,
            'rating'         => $this->rating,
            'comment'        => $this->comment,
      ];
    }
}
