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
        return $query->whereRaw('LOWER(city) LIKE ?', ['%'.strtolower($city).'%']);
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

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $query->where(function (Builder $q) use ($search): void {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(address) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
            });
        }

        if (! empty($filters['city'])) {
            $city = strtolower($filters['city']);
            $query->whereRaw('LOWER(city) LIKE ?', ["%{$city}%"]);
        }

        if (! empty($filters['price_range'])) {
            $query->where('price_range', $filters['price_range']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price_min', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price_max', '<=', $filters['max_price']);
        }

        if (isset($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }

        $booleanFilters = [
            'has_wifi',
            'has_power_outlets',
            'is_ac',
            'is_outdoor',
            'is_smoking_area',
            'is_work_friendly',
            'has_prayer_room',
        ];

        foreach ($booleanFilters as $field) {
            if (isset($filters[$field])) {
                $query->where($field, filter_var($filters[$field], FILTER_VALIDATE_BOOLEAN));
            }
        }

        if (! empty($filters['ambiance'])) {
            $query->where('ambiance', $filters['ambiance']);
        }

        if (! empty($filters['noise_level'])) {
            $query->where('noise_level', $filters['noise_level']);
        }

        $allowedSorts = ['rating', 'review_count', 'price_min', 'price_max', 'name', 'wifi_speed_mbps'];
        $sortBy = in_array($filters['sort_by'] ?? null, $allowedSorts, true) ? $filters['sort_by'] : 'rating';
        $sortOrder = strtolower($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortOrder);
    }
}
