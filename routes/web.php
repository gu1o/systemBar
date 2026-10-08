<?php

use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/dashboard', DashboardController::class)
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rotas de Produtos — sem show(): o controller não tem o método, e a rota
    // registrada respondia 500 em vez de 404 (§B9).
    Route::get('/products/arquivados', [ProductController::class, 'arquivados'])->name('products.arquivados');
    // Em lote — antes do resource: senão o DELETE cai em destroy() com {product} = "selecionados".
    Route::delete('/products/selecionados', [ProductController::class, 'arquivarSelecionados'])->name('products.arquivarSelecionados');
    Route::patch('/products/selecionados/restaurar', [ProductController::class, 'restaurarSelecionados'])->name('products.restaurarSelecionados');
    Route::patch('/products/{product}/restaurar', [ProductController::class, 'restaurar'])
    ->withTrashed()
    ->name('products.restaurar');
    Route::resource('products', ProductController::class)->except('show');

    // Rotas de Vendas (Registro de Compras) — venda não se edita nem se exclui;
    // o caminho previsto é cancelar com devolução ao estoque (§F4).
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('/sales/{sale}/status', [SaleController::class, 'updateStatus'])
    ->name('sales.updateStatus');

    // Cancelar devolve o estoque e mantém a venda no histórico, marcada (§F4).
    Route::patch('/sales/{sale}/cancelar', [SaleController::class, 'cancel'])
    ->name('sales.cancel');

    // Mesmo esquema de Produtos: rotas fixas antes do resource.
    Route::get('/customers/arquivados', [CustomersController::class, 'arquivados'])->name('customers.arquivados');
    Route::delete('/customers/selecionados', [CustomersController::class, 'arquivarSelecionados'])->name('customers.arquivarSelecionados');
    Route::patch('/customers/selecionados/restaurar', [CustomersController::class, 'restaurarSelecionados'])->name('customers.restaurarSelecionados');
    Route::patch('/customers/{customer}/restaurar', [CustomersController::class, 'restaurar'])
    ->withTrashed()
    ->name('customers.restaurar');
    Route::resource('customers', CustomersController::class)->except('show');

    // Filtro e totais ficam no componente: app/Livewire/Faturamento.php.
    Route::view('/faturamento', 'relatorio')->name('relatorio');
});

require __DIR__.'/auth.php';
