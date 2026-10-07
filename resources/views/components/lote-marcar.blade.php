@props(['id', 'nome'])

{{-- Célula de marcar a linha para o <x-lote>. No cartão do celular o texto
     "Selecionar" aparece ao lado; o label inteiro é alvo de toque. --}}
<td class="px-4 py-2 md:py-4 md:pr-0">
    <label class="inline-flex min-h-11 cursor-pointer items-center gap-3 text-base font-bold text-gray-700">
        <input type="checkbox" value="{{ $id }}" x-model="sel" aria-label="Selecionar {{ $nome }}"
               class="size-6 cursor-pointer rounded border-gray-400 text-brand-700 focus:ring-brand-700">
        <span class="md:sr-only" aria-hidden="true">Selecionar</span>
    </label>
</td>
