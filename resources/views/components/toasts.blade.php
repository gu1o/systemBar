{{--
    Retorno de toda ação do sistema, em um lugar só (substitui o x-alert no topo do
    conteúdo e o "Alterações salvas." solto em cada form).

    Fontes:
      - flash da sessão: session('success'), session('error') e session('status')
        (login, senha, perfil) — os controllers não mudam;
      - evento do navegador `toast`: Livewire `$this->dispatch('toast', type: 'success', message: '...')`
        ou Alpine `$dispatch('toast', { type: 'error', message: '...' })`.

    Sucesso some sozinho em 6s (pausa com o mouse em cima). Erro fica até fechar:
    quem não leu a tempo perderia o único aviso de que a ação não aconteceu.
--}}
@php
    $status = session('status');

    $iniciais = array_values(array_filter([
        session('success') ? ['type' => 'success', 'message' => session('success')] : null,
        session('error') ? ['type' => 'error', 'message' => session('error')] : null,
        $status ? ['type' => 'success', 'message' => match ($status) {
            'profile-updated', 'password-updated' => 'Alterações salvas.',
            default => $status,
        }] : null,
    ]));
@endphp

<div x-data="{
        toasts: [],
        // x-transition só anima com x-show: o toast entra no array invisível e é
        // mostrado no frame seguinte (anima a entrada); ao fechar, esconde primeiro
        // e só sai do array depois da animação de saída (300ms).
        add(toast) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, type: toast.type ?? 'success', message: toast.message, visivel: false });
            requestAnimationFrame(() => {
                const t = this.toasts.find(t => t.id === id);
                if (t) t.visivel = true;
            });
            if ((toast.type ?? 'success') !== 'error') this.agendar(id);
        },
        agendar(id) {
            const t = this.toasts.find(t => t.id === id);
            if (t) t.timer = setTimeout(() => this.remove(id), 6000);
        },
        pausar(id) { clearTimeout(this.toasts.find(t => t.id === id)?.timer); },
        remove(id) {
            const t = this.toasts.find(t => t.id === id);
            if (!t || !t.visivel) return;
            clearTimeout(t.timer);
            t.visivel = false;
            setTimeout(() => this.toasts = this.toasts.filter(t => t.id !== id), 300);
        },
     }"
     {{-- JSON num data-* escapado pelo {{ }}: aspas viram &quot; e não fecham o atributo. --}}
     data-iniciais="{{ json_encode($iniciais, JSON_UNESCAPED_UNICODE) }}"
     x-init="JSON.parse($el.dataset.iniciais).forEach(t => add(t))"
     x-on:toast.window="add(Array.isArray($event.detail) ? $event.detail[0] : $event.detail)"
     class="pointer-events-none fixed inset-x-0 top-0 z-[60] flex flex-col items-center gap-3 p-4 sm:inset-x-auto sm:right-0 sm:items-end sm:p-6"
     aria-live="polite">
    <template x-for="toast in toasts" :key="toast.id">
        {{-- Entra deslizando de cima (celular) ou da direita (desktop) com leve
             "pulo"; sai deslizando para a direita e encolhendo. Quem pediu menos
             movimento no sistema operacional vê só o fade (motion-reduce). --}}
        <div x-show="toast.visivel"
             x-transition:enter="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] motion-reduce:transition-opacity"
             x-transition:enter-start="opacity-0 -translate-y-4 sm:translate-y-0 sm:translate-x-full motion-reduce:translate-x-0 motion-reduce:translate-y-0"
             x-transition:enter-end="opacity-100 translate-y-0 translate-x-0"
             x-transition:leave="transition-all duration-300 ease-in motion-reduce:transition-opacity"
             x-transition:leave-start="opacity-100 translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 translate-x-full scale-95 motion-reduce:translate-x-0 motion-reduce:scale-100"
             x-on:mouseenter="pausar(toast.id)"
             x-on:mouseleave="toast.type !== 'error' && agendar(toast.id)"
             :role="toast.type === 'error' ? 'alert' : 'status'"
             :class="toast.type === 'error' ? 'border-red-700 text-red-900' : 'border-green-600 text-green-900'"
             class="pointer-events-auto flex w-full max-w-md items-start gap-3 rounded-lg border-l-8 bg-white p-4 shadow-xl ring-1 ring-black/5">
            <div class="flex-1">
                <p class="text-lg font-bold" x-text="toast.type === 'error' ? 'Algo deu errado' : 'Deu certo!'"></p>
                <p class="text-base text-ink" x-text="toast.message"></p>
            </div>

            <button type="button" x-on:click="remove(toast.id)"
                    class="-m-1 inline-flex min-h-11 min-w-11 cursor-pointer items-center justify-center rounded-lg text-2xl leading-none text-ink-muted hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-brand-700"
                    aria-label="Fechar aviso">&times;</button>
        </div>
    </template>
</div>
