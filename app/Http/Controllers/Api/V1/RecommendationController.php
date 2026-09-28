<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoffeeShop\RecommendationRequest;
use App\Http\Resources\CoffeeShopResource;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;

class RecommendationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected RecommendationService $recommendationService
    ) {}

    /**
     * Generate scored coffee shop recommendations based on user preference criteria.
     */
    public function __invoke(RecommendationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $recommendations = $this->recommendationService->getRecommendations($validated);

        $formatted = $recommendations->map(function (array $item): array {
            return [
                'coffee_shop' => new CoffeeShopResource($item['cafe']),
                'match_score' => $item['match_score'],
                'match_percentage' => $item['match_percentage'],
                'score_breakdown' => $item['score_breakdown'],
                'highlights' => $item['highlights'],
            ];
        });

        return $this->successResponse([
            'mode' => $validated['mode'] ?? 'wfc',
            'city' => $validated['city'] ?? 'All Cities',
            'total_recommendations' => $formatted->count(),
            'items' => $formatted,
        ], 'Coffee shop recommendations generated successfully');
    }
}
