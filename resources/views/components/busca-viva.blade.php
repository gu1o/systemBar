@props(['rotulo', 'exemplo'])

{{-- Busca por nome dentro de um componente Livewire (precisa de $busca e buscar()).
     Filtra sozinha meio segundo depois de parar de digitar (não a cada letra: a
     lista pulando enquanto se digita confunde). Enter também busca. Mesmo campo
     da tela de Vendas, com o rótulo visível como nas outras listas. --}}
<form wire:submit="buscar($event.target.busca.value)" role="search" class="mb-6">
    <label for="busca" class="mb-1 block text-lg font-bold text-ink">{{ $rotulo }}</label>
    <div class="relative">
        <svg class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-ink-muted" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7.5"/><path d="m16.5 16.5 4 4"/></svg>
        <input type="search" name="busca" id="busca" wire:model.live.debounce.500ms="busca"
               placeholder="{{ $exemplo }}"
               class="min-h-12 w-full rounded-lg border-2 border-edge bg-surface py-2 pl-12 pr-4 text-lg placeholder:text-ink-muted focus:border-brand-700 focus:outline-none">
    </div>
</form>
