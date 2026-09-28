<?php

namespace Database\Factories;

use App\Models\CoffeeShop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CoffeeShop>
 */
class CoffeeShopFactory extends Factory
{
    protected $model = CoffeeShop::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prefixes = ['Kopi', 'Ruang', 'Titik', 'Kedai', 'Kala', 'Kausa', 'Sudut', 'Ombe', 'Daily'];
        $suffixes = ['Karsa', 'Kala', 'Temu', 'Seduh', 'Senja', 'Vibes', 'Roastery', 'Lab', 'Space', 'Corner'];
        $name = fake()->randomElement($prefixes).' '.fake()->randomElement($suffixes).' '.fake()->cityPrefix();

        $priceMin = fake()->randomElement([15000, 20000, 25000, 30000]);
        $priceMax = $priceMin + fake()->randomElement([20000, 30000, 45000, 60000]);

        $priceRange = '$';
        if ($priceMax > 60000) {
            $priceRange = '$$$';
        } elseif ($priceMax > 35000) {
            $priceRange = '$$';
        }

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 9999),
            'description' => fake()->paragraph(2),
            'address' => fake()->streetAddress().', '.fake()->streetName(),
            'city' => fake()->randomElement(['Jakarta Selatan', 'Jakarta Pusat', 'Bandung', 'Bogor', 'Yogyakarta', 'Surabaya']),
            'latitude' => fake()->latitude(-6.4, -6.1),
            'longitude' => fake()->longitude(106.6, 107.0),
            'phone' => fake()->phoneNumber(),
            'instagram' => '@'.Str::slug($name, ''),
            'image_url' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=800&q=80',
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'price_range' => $priceRange,
            'rating' => fake()->randomFloat(2, 3.80, 4.95),
            'review_count' => fake()->numberBetween(15, 650),
            'opening_time' => fake()->randomElement(['07:00:00', '08:00:00', '09:00:00']),
            'closing_time' => fake()->randomElement(['21:00:00', '22:00:00', '23:00:00']),
            'has_wifi' => fake()->boolean(85),
            'has_power_outlets' => fake()->boolean(80),
            'is_ac' => fake()->boolean(90),
            'is_outdoor' => fake()->boolean(65),
            'is_smoking_area' => fake()->boolean(55),
            'is_work_friendly' => fake()->boolean(75),
            'has_prayer_room' => fake()->boolean(60),
            'ambiance' => fake()->randomElement(['aesthetic', 'cozy', 'minimalist', 'industrial', 'nature']),
            'wifi_speed_mbps' => fake()->randomElement([25, 50, 75, 100, 150]),
            'noise_level' => fake()->randomElement(['quiet', 'moderate', 'loud']),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the cafe is highly work-friendly.
     */
    public function workFriendly(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_wifi' => true,
            'has_power_outlets' => true,
            'is_work_friendly' => true,
            'is_ac' => true,
            'noise_level' => 'quiet',
            'wifi_speed_mbps' => 100,
        ]);
    }

    /**
     * Indicate that the cafe is outdoor/nature focused.
     */
    public function outdoor(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_outdoor' => true,
            'ambiance' => 'nature',
        ]);
    }
}
