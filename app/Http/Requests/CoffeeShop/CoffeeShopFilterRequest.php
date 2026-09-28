<?php

namespace App\Http\Requests\CoffeeShop;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CoffeeShopFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        $booleans = [
            'has_wifi',
            'has_power_outlets',
            'is_ac',
            'is_outdoor',
            'is_smoking_area',
            'is_work_friendly',
            'has_prayer_room',
        ];

        $merged = [];
        foreach ($booleans as $field) {
            if ($this->has($field)) {
                $merged[$field] = filter_var($this->input($field), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }
        }

        if (! empty($merged)) {
            $this->merge($merged);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'price_range' => ['nullable', 'string', 'in:$,$$,$$$'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'gte:min_price'],
            'min_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'has_wifi' => ['nullable', 'boolean'],
            'has_power_outlets' => ['nullable', 'boolean'],
            'is_ac' => ['nullable', 'boolean'],
            'is_outdoor' => ['nullable', 'boolean'],
            'is_smoking_area' => ['nullable', 'boolean'],
            'is_work_friendly' => ['nullable', 'boolean'],
            'has_prayer_room' => ['nullable', 'boolean'],
            'ambiance' => ['nullable', 'string', 'in:aesthetic,cozy,minimalist,industrial,nature'],
            'noise_level' => ['nullable', 'string', 'in:quiet,moderate,loud'],
            'sort_by' => ['nullable', 'string', 'in:rating,review_count,price_min,price_max,name,wifi_speed_mbps'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
