<?php

namespace App\Http\Resources;

use App\Models\Opinion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpinionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
          'Opinion'=> $this->opinion ,
        ];
    }
}
