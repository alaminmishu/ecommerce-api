<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-'.strtoupper(Str::random(10)),
            'guest_email' => fake()->email(),
            'subtotal' => 1000,
            'tax' => 0,
            'shipping' => 0,
            'discount' => 0,
            'total' => 1000,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'shipping_name' => fake()->name(),
            'shipping_email' => fake()->email(),
            'shipping_phone' => '+8801712345678',
            'shipping_address' => fake()->address(),
            'shipping_city' => 'Dhaka',
            'shipping_zip' => '1205',
            'shipping_country' => 'BD',
        ];
    }
}
