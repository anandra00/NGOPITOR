<?php

namespace Tests\Feature;

use App\Models\CoffeeShop;
use Database\Seeders\CoffeeShopSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoffeeShopTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving coffee shop listing with pagination and standardized response.
     */
    public function test_can_list_coffee_shops_with_pagination(): void
    {
        CoffeeShop::factory()->count(5)->create();

        $response = $this->getJson(route('api.v1.coffee-shops.index'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Coffee shops retrieved successfully',
                'errors' => null,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'city',
                            'rating',
                            'facilities' => [
                                'has_wifi',
                                'has_power_outlets',
                                'is_work_friendly',
                            ],
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page',
                    ],
                ],
                'errors',
            ]);

        $this->assertCount(5, $response->json('data.items'));
    }

    /**
     * Test searching coffee shops by keyword in name or address.
     */
    public function test_can_search_coffee_shops_by_keyword(): void
    {
        CoffeeShop::factory()->create([
            'name' => 'Kopi Senja Senopati',
            'city' => 'Jakarta Selatan',
        ]);

        CoffeeShop::factory()->create([
            'name' => 'Warung Teh Manis',
            'city' => 'Bandung',
        ]);

        $response = $this->getJson(route('api.v1.coffee-shops.index', ['search' => 'Senja']));

        $response->assertStatus(200);
        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame('Kopi Senja Senopati', $items[0]['name']);
    }

    /**
     * Test filtering coffee shops by city.
     */
    public function test_can_filter_coffee_shops_by_city(): void
    {
        CoffeeShop::factory()->create([
            'name' => 'Kedai Braga Bandung',
            'city' => 'Bandung',
        ]);

        CoffeeShop::factory()->create([
            'name' => 'Kedai Menteng Jakarta',
            'city' => 'Jakarta Pusat',
        ]);

        $response = $this->getJson(route('api.v1.coffee-shops.index', ['city' => 'Bandung']));

        $response->assertStatus(200);
        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame('Bandung', $items[0]['city']);
    }

    /**
     * Test filtering coffee shops by WFC facilities.
     */
    public function test_can_filter_coffee_shops_by_wfc_facilities(): void
    {
        CoffeeShop::factory()->create([
            'name' => 'WFC Hub',
            'has_wifi' => true,
            'has_power_outlets' => true,
            'is_work_friendly' => true,
        ]);

        CoffeeShop::factory()->create([
            'name' => 'No Wifi Spot',
            'has_wifi' => false,
            'has_power_outlets' => false,
            'is_work_friendly' => false,
        ]);

        $response = $this->getJson(route('api.v1.coffee-shops.index', [
            'has_wifi' => 'true',
            'has_power_outlets' => 'true',
            'is_work_friendly' => 'true',
        ]));

        $response->assertStatus(200);
        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame('WFC Hub', $items[0]['name']);
    }

    /**
     * Test sorting coffee shops by minimum price.
     */
    public function test_can_sort_coffee_shops_by_price(): void
    {
        CoffeeShop::factory()->create(['name' => 'Mahal Coffee', 'price_min' => 45000]);
        CoffeeShop::factory()->create(['name' => 'Murah Coffee', 'price_min' => 15000]);

        $response = $this->getJson(route('api.v1.coffee-shops.index', [
            'sort_by' => 'price_min',
            'sort_order' => 'asc',
        ]));

        $response->assertStatus(200);
        $items = $response->json('data.items');
        $this->assertSame('Murah Coffee', $items[0]['name']);
    }

    /**
     * Test retrieving a specific coffee shop detail by slug.
     */
    public function test_can_get_coffee_shop_detail_by_slug(): void
    {
        $shop = CoffeeShop::factory()->workFriendly()->create([
            'name' => 'Kopi Titik Koma',
            'slug' => 'kopi-titik-koma',
            'city' => 'Jakarta Selatan',
        ]);

        $response = $this->getJson(route('api.v1.coffee-shops.show', ['coffeeShop' => $shop->slug]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Coffee shop detail retrieved successfully',
                'data' => [
                    'name' => 'Kopi Titik Koma',
                    'slug' => 'kopi-titik-koma',
                    'city' => 'Jakarta Selatan',
                    'facilities' => [
                        'has_wifi' => true,
                        'has_power_outlets' => true,
                        'is_work_friendly' => true,
                    ],
                ],
            ]);
    }

    /**
     * Test that non-existent coffee shop returns standardized 404 response.
     */
    public function test_returns_404_when_coffee_shop_not_found(): void
    {
        $response = $this->getJson('/api/v1/coffee-shops/kedai-tidak-ada');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found',
                'data' => null,
                'errors' => null,
            ]);
    }

    /**
     * Test database seeder populates real and faker coffee shops correctly.
     */
    public function test_seeder_populates_expected_coffee_shops(): void
    {
        $this->seed(CoffeeShopSeeder::class);

        $this->assertDatabaseHas('coffee_shops', [
            'slug' => 'titik-temu-coffee-senopati',
            'city' => 'Jakarta Selatan',
        ]);

        $this->assertDatabaseHas('coffee_shops', [
            'slug' => 'kopi-toko-djawa-braga',
            'city' => 'Bandung',
        ]);

        $this->assertDatabaseHas('coffee_shops', [
            'slug' => 'raindear-coffee-kitchen-bogor',
            'city' => 'Bogor',
        ]);

        $this->assertGreaterThanOrEqual(25, CoffeeShop::count());
    }
}
