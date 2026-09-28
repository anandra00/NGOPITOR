<?php

namespace App\Http\Requests\CoffeeShop;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecommendationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mode' => ['nullable', 'string', 'in:wfc,hangout,budget,custom'],
            'city' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:30'],
            'rating_weight' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'wfc_weight' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'comfort_weight' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'price_weight' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ];
    }
}
