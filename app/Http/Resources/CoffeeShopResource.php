<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoffeeShopResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'contact' => [
                'phone' => $this->phone,
                'instagram' => $this->instagram,
            ],
            'image_url' => $this->image_url,
            'pricing' => [
                'min' => $this->price_min,
                'max' => $this->price_max,
                'range' => $this->price_range,
            ],
            'rating' => $this->rating,
            'review_count' => $this->review_count,
            'operational_hours' => [
                'opening' => $this->opening_time,
                'closing' => $this->closing_time,
            ],
            'facilities' => [
                'has_wifi' => (bool) $this->has_wifi,
                'has_power_outlets' => (bool) $this->has_power_outlets,
                'is_ac' => (bool) $this->is_ac,
                'is_outdoor' => (bool) $this->is_outdoor,
                'is_smoking_area' => (bool) $this->is_smoking_area,
                'is_work_friendly' => (bool) $this->is_work_friendly,
                'has_prayer_room' => (bool) $this->has_prayer_room,
            ],
            'atmosphere' => [
                'ambiance' => $this->ambiance,
                'noise_level' => $this->noise_level,
                'wifi_speed_mbps' => $this->wifi_speed_mbps,
            ],
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
