@props(['rota', 'valor', 'rotulo', 'exemplo'])

{{-- Busca por nome (§F3). Campo grande e visível no topo: paginação sem busca
     transfere para o usuário o trabalho de lembrar em que página estava a coisa. --}}
<form action="{{ $rota }}" method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end">
    <div class="flex-1">
        <label for="busca" class="block text-lg font-bold text-ink mb-1">{{ $rotulo }}</label>
        <input type="search" name="busca" id="busca" value="{{ $valor }}" placeholder="{{ $exemplo }}"
               class="w-full rounded border px-4 py-3 text-lg">
    </div>

    <div class="flex gap-3">
        <button type="submit" data-rotulo-enviando="Buscando..."
                class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-lg bg-brand-700 px-6 py-3 text-lg font-bold text-white transition-colors hover:bg-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
            Buscar
        </button>

        @if ($valor !== '')
            <a href="{{ $rota }}"
               class="inline-flex min-h-11 items-center justify-center rounded-lg border-2 border-gray-400 px-6 py-3 text-lg font-bold text-gray-700 transition-colors hover:bg-gray-100">
                Limpar
            </a>
        @endif
    </div>
</form>
