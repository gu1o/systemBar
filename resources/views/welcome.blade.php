<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>System Bar - Gestão Simples</title>
        <link rel="icon" href="{{ asset('assets/favicon.png') }}" type="image/png">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-[#002366] flex items-center justify-center min-h-screen">
        <div class="py-12 px-8 max-w-4xl w-full text-center">
            <h1 class="text-6xl font-extrabold text-white mb-8 tracking-tight">
                SYSTEM BAR
            </h1>
            <p class="text-2xl text-gray-200 mb-12 leading-relaxed">
                A solução simples e eficiente para organizar o fluxo financeiro e o estoque do seu pequeno comércio.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-6">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-[#008080] hover:bg-[#00A0A0] text-white font-bold py-5 px-12 rounded-xl shadow-2xl transition-all text-2xl">
                            Acessar Painel
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="bg-[#0047AB] hover:bg-[#0056D2] text-white font-bold py-5 px-12 rounded-xl shadow-2xl transition-all text-2xl">
                            Entrar no Sistema
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-white hover:bg-gray-100 text-[#002366] font-bold py-5 px-12 rounded-xl shadow-2xl transition-all text-2xl">
                                Criar Conta
                            </a>
                        @endif
                    @endauth
                @endif
            </div>

            <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-8 text-white">
                <div class="p-6 border border-white/10 rounded-2xl bg-white/5">
                    <div class="text-4xl mb-4">📊</div>
                    <h3 class="text-xl font-bold mb-2">Fluxo Financeiro</h3>
                    <p class="text-gray-300">Controle suas vendas e pagamentos de forma simplificada.</p>
                </div>
                <div class="p-6 border border-white/10 rounded-2xl bg-white/5">
                    <div class="text-4xl mb-4">📦</div>
                    <h3 class="text-xl font-bold mb-2">Estoque Real</h3>
                    <p class="text-gray-300">Saiba exatamente o que tem na prateleira em tempo real.</p>
                </div>
                <div class="p-6 border border-white/10 rounded-2xl bg-white/5">
                    <div class="text-4xl mb-4">👴</div>
                    <h3 class="text-xl font-bold mb-2">Fácil de Usar</h3>
                    <p class="text-gray-300">Interface pensada para quem não tem intimidade com tecnologia.</p>
                </div>
            </div>
        </div>
    </body>
</html>
