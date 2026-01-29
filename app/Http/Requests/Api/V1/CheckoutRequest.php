<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
            'shipping_name' => 'required|string|max:255',
            'shipping_email' => 'required|email|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'nullable|string|max:100',
            'shipping_zip' => 'required|string|max:20',
            'shipping_country' => 'nullable|string|max:2',
            'payment_method' => 'required|in:stripe,cash_on_delivery',
            'customer_note' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get body parameters for API documentation
     */
    public function bodyParameters(): array
    {
        return [
            'shipping_name' => [
                'description' => 'Full name of the recipient',
                'example' => 'John Doe',
            ],
            'shipping_email' => [
                'description' => 'Email address for order confirmation',
                'example' => 'john@example.com',
            ],
            'shipping_phone' => [
                'description' => 'Contact phone number',
                'example' => '+8801712345678',
            ],
            'shipping_address' => [
                'description' => 'Complete shipping address',
                'example' => '123 Main Street, Apt 4B',
            ],
            'shipping_city' => [
                'description' => 'City name',
                'example' => 'Dhaka',
            ],
            'shipping_state' => [
                'description' => 'State or division (optional)',
                'example' => 'Dhaka Division',
            ],
            'shipping_zip' => [
                'description' => 'Postal/ZIP code',
                'example' => '1205',
            ],
            'shipping_country' => [
                'description' => 'Two-letter country code',
                'example' => 'BD',
            ],
            'payment_method' => [
                'description' => 'Payment method: stripe or cash_on_delivery',
                'example' => 'stripe',
            ],
            'customer_note' => [
                'description' => 'Optional delivery instructions or notes',
                'example' => 'Please call before delivery',
            ],
        ];
    }
}
