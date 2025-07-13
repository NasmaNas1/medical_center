<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentSlotResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'time' => $this->resource['time'],
            'formatted_time' => $this->resource['formatted_time'],
            'timestamp' => $this->resource['timestamp'],
            'is_available' => $this->resource['is_available'] ?? true,
        ];
    }
}