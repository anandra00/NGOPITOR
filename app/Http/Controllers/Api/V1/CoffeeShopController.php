<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoffeeShop\CoffeeShopFilterRequest;
use App\Http\Resources\CoffeeShopResource;
use App\Models\CoffeeShop;
use Illuminate\Http\JsonResponse;

class CoffeeShopController extends Controller
{
    /**
     * Display a listing of active coffee shops with optional filters and search.
     */
    public function index(CoffeeShopFilterRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);

        $coffeeShops = CoffeeShop::query()
            ->active()
            ->filter($filters)
            ->paginate($perPage);

        return $this->successResponse([
            'items' => CoffeeShopResource::collection($coffeeShops),
            'pagination' => [
                'current_page' => $coffeeShops->currentPage(),
                'per_page' => $coffeeShops->perPage(),
                'total' => $coffeeShops->total(),
                'last_page' => $coffeeShops->lastPage(),
            ],
            'applied_filters' => $filters,
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
