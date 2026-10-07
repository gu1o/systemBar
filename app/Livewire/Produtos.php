<?php

namespace App\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Lista de produtos com busca e filtro de estoque que respondem sem recarregar
 * (mesmo formato de Vendas). Filtros na URL: Voltar, favorito e F5 abrem a mesma lista.
 * Arquivar continua por formulário normal (x-row-actions / x-lote), como o
 * "Marcar como pago" de Vendas.
 */
class Produtos extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $busca = '';

    #[Url(except: '')]
    public string $status = '';

    // Filtro mudou: a página 3 da lista anterior não existe mais na nova.
    public function updated(): void
    {
        $this->resetPage();
    }

    public function buscar(string $termo): void
    {
        $this->busca = trim($termo);
        $this->resetPage();
    }

    public function render()
    {
        $busca = trim($this->busca);
        // Mesmo critério do selo da lista e do aviso do painel: estoque <= ponto de alerta.
        // Valor desconhecido na URL vira "sem filtro", para a aba ativa mostrar o que vale.
        $this->status = in_array($this->status, ['baixo', 'normal'], true) ? $this->status : '';

        $products = auth()->user()->products()
            ->when($busca !== '', fn ($query) => $query->where('name', 'like', '%'.$busca.'%'))
            ->when($this->status === 'baixo', fn ($query) => $query->whereColumn('stock_quantity', '<=', 'stock_alert'))
            ->when($this->status === 'normal', fn ($query) => $query->whereColumn('stock_quantity', '>', 'stock_alert'))
            ->latest()
            ->paginate(10);

        return view('livewire.produtos', ['products' => $products, 'termo' => $busca]);
    }
}
