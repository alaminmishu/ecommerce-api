<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get body parameters for API documentation
     */
    public function bodyParameters(): array
    {
        return [
            'product_variant_id' => [
                'description' => 'The ID of the product variant to add to cart',
                'example' => 1,
            ],
            'quantity' => [
                'description' => 'Quantity of items to add',
                'example' => 2,
            ],
        ];
    }

}
