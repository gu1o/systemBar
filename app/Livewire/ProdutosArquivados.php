<?php

namespace App\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Produtos arquivados, com busca sem recarregar (mesmo formato de Produtos/Vendas).
 * Restaurar continua por formulário normal.
 */
class ProdutosArquivados extends Component
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

        $products = auth()->user()->products()->onlyTrashed()
            ->when($busca !== '', fn ($query) => $query->where('name', 'like', '%'.$busca.'%'))
            ->latest('deleted_at')
            ->paginate(10);

        return view('livewire.produtos-arquivados', ['products' => $products, 'termo' => $busca]);
    }
}
