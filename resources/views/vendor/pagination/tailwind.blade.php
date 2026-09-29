{{--
    Paginação (§A2). A view padrão do Laravel tem alvos pequenos e lista o número de
    todas as páginas. Aqui: dois botões de 44px e a posição dita por extenso —
    "Página 2 de 6" responde a pergunta que o usuário realmente tem.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegação entre páginas"
         class="flex flex-col gap-4 border-t-2 border-gray-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-lg font-semibold text-gray-700">
            Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}
        </p>

        <div class="flex flex-col gap-3 sm:flex-row">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true"
                      class="inline-flex min-h-11 items-center justify-center rounded-lg border-2 border-gray-300 px-6 py-3 text-base font-bold text-gray-400">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="inline-flex min-h-11 items-center justify-center rounded-lg border-2 border-brand-700 px-6 py-3 text-base font-bold text-brand-700 transition-colors hover:bg-brand-700 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="inline-flex min-h-11 items-center justify-center rounded-lg border-2 border-brand-700 px-6 py-3 text-base font-bold text-brand-700 transition-colors hover:bg-brand-700 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span aria-disabled="true"
                      class="inline-flex min-h-11 items-center justify-center rounded-lg border-2 border-gray-300 px-6 py-3 text-base font-bold text-gray-400">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>
    </nav>
@endif
