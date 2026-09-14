<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Venda e cliente precisam ser do mesmo comércio: use `recycle($user)` ao criar
 * (`Sale::factory()->recycle($user)`), senão cada relação cria o próprio usuário.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_id' => Customer::factory(),
            'total_amount' => fake()->randomFloat(2, 10, 500),
            'status' => 'pending',
        ];
    }
}
