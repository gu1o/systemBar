<?php

namespace App\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Lista de clientes com busca que responde sem recarregar (mesmo formato de
 * Produtos e Vendas). Busca na URL: Voltar, favorito e F5 abrem a mesma lista.
 */
class Clientes extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $busca = '';

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

        $customers = auth()->user()->customers()
            ->withCount('sales')
            ->when($busca !== '', fn ($query) => $query->where('name', 'like', '%'.$busca.'%'))
            ->latest()
            ->paginate(10);

        return view('livewire.clientes', ['customers' => $customers, 'termo' => $busca]);
    }
}
