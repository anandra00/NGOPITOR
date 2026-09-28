<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CoffeeShopResource;
use App\Models\CoffeeShop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoffeeShopController extends Controller
{
    /**
     * Display a listing of active coffee shops.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 15), 50);

        $coffeeShops = CoffeeShop::query()
            ->active()
            ->orderByDesc('rating')
            ->paginate($perPage);

        return $this->successResponse([
            'items' => CoffeeShopResource::collection($coffeeShops),
            'pagination' => [
                'current_page' => $coffeeShops->currentPage(),
                'per_page' => $coffeeShops->perPage(),
                'total' => $coffeeShops->total(),
                'last_page' => $coffeeShops->lastPage(),
            ],
        ], 'Coffee shops retrieved successfully');
    }

    /**
     * Display the specified coffee shop by slug.
     */
    public function show(CoffeeShop $coffeeShop): JsonResponse
    {
        return $this->successResponse(
            new CoffeeShopResource($coffeeShop),
            'Coffee shop detail retrieved successfully'
        );
    }
}
