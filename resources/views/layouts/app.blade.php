<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ ($titulo ?? null) ? $titulo.' · '.config('app.name', 'System Bar') : config('app.name', 'System Bar') }}</title>
        {{-- PNG primeiro como garantia (Safari antigo), SVG depois: quem entende SVG
             usa ele e fica nítido em qualquer densidade de tela. --}}
        <link rel="icon" href="{{ asset('assets/favicon.png') }}" type="image/png">
        <link rel="icon" href="{{ asset('assets/imgs/logo.svg') }}" type="image/svg+xml">


        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=noto-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-ink">
        <a href="#conteudo"
           class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-3 focus:rounded-lg focus:bg-white focus:px-5 focus:py-3 focus:text-lg focus:font-bold focus:text-brand-700">
            Pular para o conteúdo
        </a>

        <div class="min-h-screen bg-surface-muted">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-brand-900 shadow-lg">
                    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main id="conteudo">
                {{-- Resultado da última ação, em um lugar só: toda tela ganha o aviso,
                     inclusive as de erro, que antes não tinham canal nenhum (§A7). --}}
                @if (session('success') || session('error'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
                        @if (session('success'))
                            <x-alert type="success">{{ session('success') }}</x-alert>
                        @endif

                        @if (session('error'))
                            <x-alert type="error" class="mt-4">{{ session('error') }}</x-alert>
                        @endif
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>

        <script>
            // Um clique só. Sem isto, o botão de concluir a venda apertado duas vezes — o
            // que este público faz justamente porque a tela não respondeu ao primeiro
            // clique — registra duas vendas e baixa o estoque duas vezes (§A7).
            document.addEventListener('submit', (evento) => {
                const formulario = evento.target;

                if (formulario.dataset.enviando) {
                    evento.preventDefault();
                    return;
                }

                formulario.dataset.enviando = '1';
                formulario.setAttribute('aria-busy', 'true');

                formulario.querySelectorAll('button[type="submit"]').forEach((botao) => {
                    botao.dataset.rotuloOriginal = botao.textContent.trim();
                    botao.textContent = 'Salvando...';
                    botao.disabled = true;
                });
            });

            // Voltar pelo botão do navegador reexibe a página do cache com tudo travado.
            window.addEventListener('pageshow', () => {
                document.querySelectorAll('form[data-enviando]').forEach((formulario) => {
                    delete formulario.dataset.enviando;
                    formulario.removeAttribute('aria-busy');

                    formulario.querySelectorAll('button[type="submit"]').forEach((botao) => {
                        if (botao.dataset.rotuloOriginal) {
                            botao.textContent = botao.dataset.rotuloOriginal;
                        }
                        botao.disabled = false;
                    });
                });
            });
        </script>
    </body>
</html>
