<?php

namespace App\Services;

use App\Models\CoffeeShop;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * Preset scoring weights for distinct use-cases.
     *
     * @var array<string, array{rating: float, wfc: float, comfort: float, price: float}>
     */
    protected array $presetWeights = [
        'wfc' => [
            'rating' => 0.15,
            'wfc' => 0.45,
            'comfort' => 0.25,
            'price' => 0.15,
        ],
        'hangout' => [
            'rating' => 0.30,
            'wfc' => 0.10,
            'comfort' => 0.45,
            'price' => 0.15,
        ],
        'budget' => [
            'rating' => 0.25,
            'wfc' => 0.15,
            'comfort' => 0.15,
            'price' => 0.45,
        ],
    ];

    /**
     * Compute recommendations for active coffee shops based on scoring algorithm.
     *
     * @param  array<string, mixed>  $params
     * @return Collection<int, array<string, mixed>>
     */
    public function getRecommendations(array $params = []): Collection
    {
        $mode = $params['mode'] ?? 'wfc';
        $city = $params['city'] ?? null;
        $limit = (int) ($params['limit'] ?? 5);

        // Determine weights
        $weights = $this->resolveWeights($mode, $params);

        // Fetch candidate cafes
        $query = CoffeeShop::query()->active();
        if (! empty($city)) {
            $query->inCity($city);
        }

        $cafes = $query->get();

        // Calculate score for each cafe
        $scored = $cafes->map(function (CoffeeShop $cafe) use ($weights): array {
            $ratingScore = $this->calculateRatingScore($cafe);
            $wfcScore = $this->calculateWfcScore($cafe);
            $comfortScore = $this->calculateComfortScore($cafe);
            $priceScore = $this->calculatePriceScore($cafe);

            $totalScore = round(
                ($ratingScore * $weights['rating']) +
                ($wfcScore * $weights['wfc']) +
                ($comfortScore * $weights['comfort']) +
                ($priceScore * $weights['price']),
                1
            );

            $highlights = $this->generateHighlights($cafe);

            return [
                'cafe' => $cafe,
                'match_score' => $totalScore,
                'match_percentage' => "{$totalScore}%",
                'score_breakdown' => [
                    'rating_score' => $ratingScore,
                    'wfc_score' => $wfcScore,
                    'comfort_score' => $comfortScore,
                    'price_score' => $priceScore,
                ],
                'highlights' => $highlights,
            ];
        });

        // Sort descending by total match_score and limit results
        return $scored->sortByDesc('match_score')->values()->take($limit);
    }

    /**
     * Resolve scoring weights based on mode or custom input.
     *
     * @param  array<string, mixed>  $params
     * @return array{rating: float, wfc: float, comfort: float, price: float}
     */
    protected function resolveWeights(string $mode, array $params): array
    {
        if ($mode === 'custom') {
            $rating = (float) ($params['rating_weight'] ?? 0.25);
            $wfc = (float) ($params['wfc_weight'] ?? 0.25);
            $comfort = (float) ($params['comfort_weight'] ?? 0.25);
            $price = (float) ($params['price_weight'] ?? 0.25);

            $sum = $rating + $wfc + $comfort + $price;
            if ($sum > 0) {
                return [
                    'rating' => $rating / $sum,
                    'wfc' => $wfc / $sum,
                    'comfort' => $comfort / $sum,
                    'price' => $price / $sum,
                ];
            }
        }

        return $this->presetWeights[$mode] ?? $this->presetWeights['wfc'];
    }

    /**
     * Sub-score for Rating & Popularity (0 - 100).
     */
    protected function calculateRatingScore(CoffeeShop $cafe): float
    {
        $base = ($cafe->rating / 5.0) * 90.0;
        $reviewBonus = min(($cafe->review_count / 500.0) * 10.0, 10.0);

        return round(min($base + $reviewBonus, 100.0), 1);
    }

    /**
     * Sub-score for WFC / Workspace suitability (0 - 100).
     */
    protected function calculateWfcScore(CoffeeShop $cafe): float
    {
        $score = 0.0;

        if ($cafe->has_wifi) {
            $score += 30.0;
            if ($cafe->wifi_speed_mbps) {
                $score += min(($cafe->wifi_speed_mbps / 100.0) * 20.0, 20.0);
            }
        }

        if ($cafe->has_power_outlets) {
            $score += 25.0;
        }

        if ($cafe->is_work_friendly) {
            $score += 15.0;
        }

        if ($cafe->noise_level === 'quiet') {
            $score += 10.0;
        } elseif ($cafe->noise_level === 'moderate') {
            $score += 5.0;
        }

        return round(min($score, 100.0), 1);
    }

    /**
     * Sub-score for Comfort & Atmosphere (0 - 100).
     */
    protected function calculateComfortScore(CoffeeShop $cafe): float
    {
        $score = 0.0;

        if ($cafe->is_ac) {
            $score += 25.0;
        }

        if ($cafe->is_outdoor) {
            $score += 20.0;
        }

        if ($cafe->has_prayer_room) {
            $score += 15.0;
        }

        if ($cafe->is_smoking_area) {
            $score += 10.0;
        }

        $ambiancePoints = [
            'aesthetic' => 30.0,
            'nature' => 30.0,
            'cozy' => 25.0,
            'minimalist' => 20.0,
            'industrial' => 20.0,
        ];
        $score += $ambiancePoints[$cafe->ambiance] ?? 15.0;

        return round(min($score, 100.0), 1);
    }

    /**
     * Sub-score for Affordability / Price efficiency (0 - 100).
     */
    protected function calculatePriceScore(CoffeeShop $cafe): float
    {
        $avgPrice = ($cafe->price_min + $cafe->price_max) / 2.0;

        if ($avgPrice <= 20000) {
            return 100.0;
        }
        if ($avgPrice >= 85000) {
            return 30.0;
        }

        $score = 100.0 - (($avgPrice - 20000) / 65000) * 70.0;

        return round(max(min($score, 100.0), 0.0), 1);
    }

    /**
     * Generate descriptive highlights for user recommendations.
     *
     * @return list<string>
     */
    protected function generateHighlights(CoffeeShop $cafe): array
    {
        $highlights = [];

        if ($cafe->rating >= 4.7) {
            $highlights[] = "Rating luar biasa ({$cafe->rating}/5 dari {$cafe->review_count}+ ulasan)";
        }

        if ($cafe->has_wifi && $cafe->wifi_speed_mbps && $cafe->wifi_speed_mbps >= 75) {
            $highlights[] = "Koneksi internet cepat {$cafe->wifi_speed_mbps} Mbps";
        }

        if ($cafe->has_power_outlets && $cafe->is_work_friendly) {
            $highlights[] = 'Colokan melimpah, sangat nyaman untuk WFC';
        }

        if ($cafe->noise_level === 'quiet') {
            $highlights[] = 'Suasana tenang & kondusif untuk fokus bekerja';
        }

        if ($cafe->ambiance === 'nature') {
            $highlights[] = 'Nuansa alam terbuka yang asri dan sejuk';
        } elseif ($cafe->ambiance === 'aesthetic') {
            $highlights[] = 'Desain interior estetik dan instagramable';
        }

        if ($cafe->price_range === '$') {
            $highlights[] = 'Harga sangat ramah di kantong';
        }

        if ($cafe->has_prayer_room) {
            $highlights[] = 'Tersedia fasilitas musholla';
        }

        return array_slice($highlights, 0, 4);
    }
}
