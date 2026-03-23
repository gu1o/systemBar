<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuário de teste (id será UUID gerado pelo HasUuids)
        $user = User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'password' => '102030',
        ]);

        // Criar alguns clientes vinculados ao usuário
        Customer::create([
            'user_id' => $user->id,
            'name' => 'João Silva',
            'phone' => '11999999999',
        ]);
        Customer::create([
            'user_id' => $user->id,
            'name' => 'Maria Oliveira',
            'phone' => '11888888888',
        ]);
        Customer::create([
            'user_id' => $user->id,
            'name' => 'Pedro Santos',
            'phone' => '11777777777',
        ]);

        // Criar alguns produtos vinculados ao usuário
        Product::create([
            'user_id' => $user->id,
            'name' => 'Arroz 5kg',
            'cost_price' => 15.00,
            'sale_price' => 25.90,
            'stock_quantity' => 50,
        ]);

        Product::create([
            'user_id' => $user->id,
            'name' => 'Feijão Carioca 1kg',
            'cost_price' => 4.50,
            'sale_price' => 8.50,
            'stock_quantity' => 30,
        ]);

        Product::create([
            'user_id' => $user->id,
            'name' => 'Óleo de Soja 900ml',
            'cost_price' => 5.00,
            'sale_price' => 7.20,
            'stock_quantity' => 4,
        ]);

        Product::create([
            'user_id' => $user->id,
            'name' => 'Açúcar Refinado 1kg',
            'cost_price' => 3.20,
            'sale_price' => 5.40,
            'stock_quantity' => 20,
        ]);
    }
}
