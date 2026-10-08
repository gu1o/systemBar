<?php

namespace App\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Clientes arquivados, com busca sem recarregar (mesmo formato de ProdutosArquivados).
 * Restaurar continua por formulário normal.
 */
class ClientesArquivados extends Component
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

        $customers = auth()->user()->customers()->onlyTrashed()
            ->when($busca !== '', fn ($query) => $query->where('name', 'like', '%'.$busca.'%'))
            ->latest('deleted_at')
            ->paginate(10);

        return view('livewire.clientes-arquivados', ['customers' => $customers, 'termo' => $busca]);
    }
}
