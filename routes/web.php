<?php

use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RelatorioController;
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
    Route::resource('products', ProductController::class)->except('show');

    // Rotas de Vendas (Registro de Compras) — venda não se edita nem se exclui;
    // o caminho previsto é cancelar com devolução ao estoque (§F4).
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('/sales/{sale}/status', [SaleController::class, 'updateStatus'])
    ->name('sales.updateStatus');

    // Cancelar devolve o estoque e mantém a venda no histórico, marcada (§F4).
    Route::patch('/sales/{sale}/cancelar', [SaleController::class, 'cancel'])
    ->name('sales.cancel');

    Route::resource('customers', CustomersController::class)->except('show');

    Route::get('/faturamento', RelatorioController::class)->name('relatorio');
});

require __DIR__.'/auth.php';
