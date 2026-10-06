<?php

namespace App\Livewire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Lista de compras com filtros que respondem sem recarregar a página.
 * Os filtros continuam na URL (#[Url]): Voltar, favorito e F5 abrem a mesma lista.
 */
class Vendas extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $situacao = '';

    #[Url(except: '')]
    public string $busca = '';

    #[Url(except: '')]
    public string $periodo = '';

    #[Url(except: '')]
    public string $de = '';

    #[Url(except: '')]
    public string $ate = '';

    public function mount(): void
    {
        // O período fica lembrado na sessão: menu, "Voltar" dos detalhes e o redirect
        // depois de registrar venda chegam sem ?periodo e reabrem o último aplicado
        // (o personalizado principalmente, que dá trabalho refazer).
        if (! request()->hasAny(['periodo', 'de', 'ate'])) {
            foreach (session('vendas.periodo', []) as $campo => $valor) {
                $this->$campo = $valor;
            }
        }
    }

    // Filtro mudou: a página 3 da lista anterior não existe mais na nova.
    public function updated(): void
    {
        $this->resetPage();
    }

    // Personalizado começa no intervalo que já está na tela: abrir as datas não
    // troca a lista por baixo da pessoa.
    public function updatingPeriodo(string $novo): void
    {
        if ($novo === 'personalizado') {
            [, $this->de, $this->ate] = $this->resolverPeriodo();
        }
    }

    public function buscar(string $termo): void
    {
        $this->busca = trim($termo);
        $this->resetPage();
    }

    public function aplicarPeriodo(string $de, string $ate): void
    {
        [$this->periodo, $this->de, $this->ate] = ['personalizado', $de, $ate];
        $this->resetPage();
    }

    // Limpar volta para hoje explicitamente, para não reabrir o lembrado.
    public function limparPeriodo(): void
    {
        [$this->periodo, $this->de, $this->ate] = ['hoje', '', ''];
        $this->resetPage();
    }

    private function resolverPeriodo(): array
    {
        return Controller::periodo(new Request(array_filter([
            'periodo' => $this->periodo, 'de' => $this->de, 'ate' => $this->ate,
        ])));
    }

    public function render()
    {
        // Padrão hoje; sempre com as duas pontas, nunca a tabela inteira. Valor
        // inválido na URL vira o resolvido, para o select mostrar o que está valendo.
        [$periodo, $de, $ate] = $this->resolverPeriodo();
        $this->periodo = $periodo;
        [$this->de, $this->ate] = $periodo === 'personalizado' ? [$de, $ate] : ['', ''];

        session()->put('vendas.periodo',
            $periodo === 'personalizado' ? compact('periodo', 'de', 'ate') : compact('periodo'));

        $busca = trim($this->busca);

        $sales = auth()->user()->sales()
            ->with(['customer'])
            ->when(in_array($this->situacao, ['pending', 'paid', 'cancelled'], true),
                fn ($query) => $query->where('status', $this->situacao))
            ->when($busca !== '', fn ($query) => $query->whereHas('customer',
                fn ($query) => $query->where('name', 'like', '%'.$busca.'%')))
            ->whereDate('created_at', '>=', $de)
            ->whereDate('created_at', '<=', $ate)
            ->latest()
            ->paginate(10);

        // inicio/fim, não de/ate: as propriedades públicas de mesmo nome sobrescreveriam.
        return view('livewire.vendas', ['sales' => $sales, 'busca' => $busca, 'inicio' => $de, 'fim' => $ate]);
    }
}
