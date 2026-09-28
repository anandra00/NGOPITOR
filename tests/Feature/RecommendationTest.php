<?php

namespace Tests\Feature;

use App\Models\CoffeeShop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test recommendation generation in default WFC mode.
     */
    public function test_can_get_recommendations_with_default_wfc_mode(): void
    {
        // High WFC cafe
        $wfcCafe = CoffeeShop::factory()->workFriendly()->create([
            'name' => 'Fokus Space',
            'rating' => 4.90,
            'review_count' => 300,
            'wifi_speed_mbps' => 150,
            'noise_level' => 'quiet',
        ]);

        // Hangout/noisy cafe
        CoffeeShop::factory()->create([
            'name' => 'Party Lounge',
            'rating' => 4.10,
            'has_wifi' => false,
            'has_power_outlets' => false,
            'is_work_friendly' => false,
            'noise_level' => 'loud',
        ]);

        $response = $this->getJson(route('api.v1.coffee-shops.recommendations', ['mode' => 'wfc', 'limit' => 5]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Coffee shop recommendations generated successfully',
                'data' => [
                    'mode' => 'wfc',
                    'total_recommendations' => 2,
                ],
                'errors' => null,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'mode',
                    'city',
                    'total_recommendations',
                    'items' => [
                        '*' => [
                            'coffee_shop' => [
                                'id',
                                'name',
                                'slug',
                                'rating',
                                'facilities',
                            ],
                            'match_score',
                            'match_percentage',
                            'score_breakdown' => [
                                'rating_score',
                                'wfc_score',
                                'comfort_score',
                                'price_score',
                            ],
                            'highlights',
                        ],
                    ],
                ],
                'errors',
            ]);

        $items = $response->json('data.items');
        $this->assertSame('Fokus Space', $items[0]['coffee_shop']['name']);
        $this->assertGreaterThan($items[1]['match_score'], $items[0]['match_score']);
    }

    /**
     * Test recommendations in Hangout mode prioritize aesthetic and outdoor cafes.
     */
    public function test_can_get_recommendations_for_hangout_mode(): void
    {
        $hangoutCafe = CoffeeShop::factory()->outdoor()->create([
            'name' => 'Aesthetic Pines',
            'ambiance' => 'aesthetic',
            'rating' => 4.85,
            'is_outdoor' => true,
            'is_ac' => true,
        ]);

        CoffeeShop::factory()->create([
            'name' => 'Plain Office Cafe',
            'ambiance' => 'industrial',
            'is_outdoor' => false,
            'rating' => 4.0,
        ]);

        $response = $this->getJson(route('api.v1.coffee-shops.recommendations', ['mode' => 'hangout']));

        $response->assertStatus(200);
        $items = $response->json('data.items');
        $this->assertSame('Aesthetic Pines', $items[0]['coffee_shop']['name']);
    }

    /**
     * Test recommendations in Budget mode prioritize lowest prices.
     */
    public function test_can_get_recommendations_for_budget_mode(): void
    {
        $cheapCafe = CoffeeShop::factory()->create([
            'name' => 'Warkop Modern',
            'price_min' => 12000,
            'price_max' => 20000,
            'rating' => 4.60,
        ]);

        CoffeeShop::factory()->create([
            'name' => 'Sultan Coffee Lounge',
            'price_min' => 70000,
            'price_max' => 120000,
            'rating' => 4.60,
        ]);

        $response = $this->getJson(route('api.v1.coffee-shops.recommendations', ['mode' => 'budget']));

        $response->assertStatus(200);
        $items = $response->json('data.items');
        $this->assertSame('Warkop Modern', $items[0]['coffee_shop']['name']);
        $this->assertGreaterThan($items[1]['score_breakdown']['price_score'], $items[0]['score_breakdown']['price_score']);
    }

    /**
     * Test recommendations filtered by city.
     */
    public function test_can_filter_recommendations_by_city(): void
    {
        CoffeeShop::factory()->create([
            'name' => 'Braga Coffee Spot',
            'city' => 'Bandung',
        ]);

        CoffeeShop::factory()->create([
            'name' => 'Senopati Cafe',
            'city' => 'Jakarta Selatan',
        ]);

        $response = $this->getJson(route('api.v1.coffee-shops.recommendations', ['city' => 'Bandung']));

        $response->assertStatus(200);
        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame('Braga Coffee Spot', $items[0]['coffee_shop']['name']);
        $this->assertSame('Bandung', $items[0]['coffee_shop']['city']);
    }

    /**
     * Test custom weight parameters for flexible user scoring.
     */
    public function test_can_use_custom_weights_for_recommendations(): void
    {
        CoffeeShop::factory()->create(['name' => 'Cafe Test']);

        $response = $this->getJson(route('api.v1.coffee-shops.recommendations', [
            'mode' => 'custom',
            'rating_weight' => 0.7,
            'wfc_weight' => 0.1,
            'comfort_weight' => 0.1,
            'price_weight' => 0.1,
        ]));

        $response->assertStatus(200);
        $this->assertSame('custom', $response->json('data.mode'));
    }
}
