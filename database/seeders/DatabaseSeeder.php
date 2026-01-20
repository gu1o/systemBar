<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuário de teste
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);

        // Criar alguns clientes
        Customer::create(['name' => 'João Silva', 'phone' => '11999999999']);
        Customer::create(['name' => 'Maria Oliveira', 'phone' => '11888888888']);
        Customer::create(['name' => 'Pedro Santos', 'phone' => '11777777777']);

        // Criar alguns produtos
        Product::create([
            'name' => 'Arroz 5kg',
            'cost_price' => 15.00,
            'sale_price' => 25.90,
            'stock_quantity' => 50,
        ]);

        Product::create([
            'name' => 'Feijão Carioca 1kg',
            'cost_price' => 4.50,
            'sale_price' => 8.50,
            'stock_quantity' => 30,
        ]);

        Product::create([
            'name' => 'Óleo de Soja 900ml',
            'cost_price' => 5.00,
            'sale_price' => 7.20,
            'stock_quantity' => 4, // Baixo estoque para teste
        ]);

        Product::create([
            'name' => 'Açúcar Refinado 1kg',
            'cost_price' => 3.20,
            'sale_price' => 5.40,
            'stock_quantity' => 20,
        ]);
    }
}
