@props(['editar', 'excluir', 'nome', 'tipo', 'aviso'])

@php
    // Um modal por linha: o nome precisa ser único na página.
    $modal = 'arquivar-'.$tipo.'-'.md5($excluir);
@endphp

{{-- Ações de uma linha da lista (§V4). A confirmação deixou de ser o confirm()
     nativo — diálogo minúsculo, sem estilo, com "OK/Cancelar" — e passou a usar o
     x-modal que já existia no projeto: foco preso, ESC, e botões que dizem o que
     fazem ("Arquivar" / "Manter", não "OK") (§A6). --}}
<div class="flex flex-wrap items-center justify-end gap-3">
    <a href="{{ $editar }}"
       class="inline-flex min-h-11 items-center justify-center rounded-lg border-2 border-brand-700 px-5 py-3 text-base font-bold text-brand-700 transition-colors hover:bg-brand-700 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
        Editar
    </a>

    <button type="button" x-data=""
            x-on:click="$dispatch('open-modal', '{{ $modal }}')"
            class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-lg border-2 border-red-700 px-5 py-3 text-base font-bold text-red-700 transition-colors hover:bg-red-700 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700">
        Arquivar
    </button>
</div>

<x-modal :name="$modal" maxWidth="lg" focusable>
    <div class="p-6 text-left">
        <h2 class="text-2xl font-bold text-gray-900">
            Arquivar o {{ $tipo }} {{ $nome }}?
        </h2>

        <p class="mt-3 text-lg text-gray-700">{{ $aviso }}</p>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
            <button type="button" x-on:click="$dispatch('close')"
                    class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-lg border-2 border-gray-400 px-6 py-3 text-lg font-bold text-gray-700 transition-colors hover:bg-gray-100">
                Manter
            </button>

            <form action="{{ $excluir }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-lg bg-red-700 px-6 py-3 text-lg font-bold text-white transition-colors hover:bg-red-800">
                    Arquivar
                </button>
            </form>
        </div>
    </div>
</x-modal>
