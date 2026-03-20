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
    <div class="flex flex-col gap-10 py-12 px-8 sm:px-14 md:py-0 md:px-8 lg:p-0 max-w-4xl w-full text-center">
        <h1 class="text-5xl self-stretch font-extrabold text-white sm:text-6xl sm:tracking-tight">
            SYSTEM BAR
        </h1>
        <p
            class="text-[18px] text-start sm:text-center sm:self-center sm:w-[500px] lg:text-2xl text-gray-200 leading-relaxed">
            A solução simples e eficiente para organizar o fluxo financeiro e o estoque do seu pequeno comércio.
        </p>

        <div class="flex flex-col flex-col md:flex-row justify-center gap-6">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="bg-[#008080] hover:bg-[#00A0A0] text-white font-bold py-5 px-12 rounded-xl shadow-2xl transition-all text-2xl">
                        Acessar Painel
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="bg-[#0047AB] hover:bg-[#0056D2] text-white font-bold py-5 px-12 rounded-xl shadow-2xl transition-all text-2xl">
                        Entrar no Sistema
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="bg-white hover:bg-gray-100 text-[#002366] font-bold py-5 px-12 rounded-xl shadow-2xl transition-all text-2xl sm:w-full md:w-[18.3rem] lg:w-[18.5rem]">
                            Criar Conta
                        </a>
                    @endif
                @endauth
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:px-12 lg:grid-cols-3 lg:p-0 gap-8 text-white">
            <div class="flex flex-col items-center gap-4 p-6 border border-white/10 rounded-2xl bg-white/5 md:py-3 md:px-4">
                <div class="text-4xl">📊</div>
                <div class="flex flex-col items-center gap-2">
                    <h3 class="text-xl font-bold">Fluxo Financeiro</h3>
                    <p class="text-gray-300">Controle suas vendas e pagamentos de forma simplificada.</p>
                </div>
            </div>
            <div class="flex flex-col items-center gap-4 p-6 border border-white/10 rounded-2xl bg-white/5 md:py-3 md:px-4">
                <div class="text-4xl">📦</div>
                <div class="flex flex-col items-center gap-2">
                    <h3 class="text-xl font-bold">Estoque Real</h3>
                    <p class="text-gray-300">Saiba exatamente o que tem na prateleira em tempo real.</p>
                </div>
            </div>
            <div
                class="flex flex-col items-center gap-4 p-6 border border-white/10 rounded-2xl bg-white/5 sm:col-span-2 sm:row-span-2 md:py-3 md:px-4 lg:row-span-1 lg:col-span-1">
                <div class="text-4xl">👴</div>
                <div class="flex flex-col items-center gap-2">
                    <h3 class="text-xl font-bold">Fácil de Usar</h3>
                    <p class="text-gray-300">Interface pensada para quem não tem intimidade com tecnologia.</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
