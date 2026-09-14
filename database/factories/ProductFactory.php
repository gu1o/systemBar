<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'cost_price' => fake()->randomFloat(2, 1, 50),
            'sale_price' => fake()->randomFloat(2, 2, 100),
            'stock_quantity' => 10,
            'stock_alert' => 5,
        ];
    }
}
