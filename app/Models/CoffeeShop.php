<?php

namespace App\Models;

use Database\Factories\CoffeeShopFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoffeeShop extends Model
{
    /** @use HasFactory<CoffeeShopFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'address',
        'city',
        'latitude',
        'longitude',
        'phone',
        'instagram',
        'image_url',
        'price_min',
        'price_max',
        'price_range',
        'rating',
        'review_count',
        'opening_time',
        'closing_time',
        'has_wifi',
        'has_power_outlets',
        'is_ac',
        'is_outdoor',
        'is_smoking_area',
        'is_work_friendly',
        'has_prayer_room',
        'ambiance',
        'wifi_speed_mbps',
        'noise_level',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'float',
            'review_count' => 'integer',
            'price_min' => 'integer',
            'price_max' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'has_wifi' => 'boolean',
            'has_power_outlets' => 'boolean',
            'is_ac' => 'boolean',
            'is_outdoor' => 'boolean',
            'is_smoking_area' => 'boolean',
            'is_work_friendly' => 'boolean',
            'has_prayer_room' => 'boolean',
            'wifi_speed_mbps' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active coffee shops.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by city.
     */
    public function scopeInCity(Builder $query, string $city): Builder
    {
        return $query->where('city', 'ILIKE', "%{$city}%");
    }

    /**
     * Scope a query for work-friendly cafes.
     */
    public function scopeWorkFriendly(Builder $query): Builder
    {
        return $query->where('is_work_friendly', true)
            ->where('has_wifi', true)
            ->where('has_power_outlets', true);
    }
}
