{{--
    §T9 — a página de erro era a do Laravel, em inglês e com cara de sistema quebrado.
    Para este público, um 500 cru é motivo de parar de usar o sistema. Esta diz em
    português o que aconteceu e o que fazer, sem jargão e sem código de status gritando.
--}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name', 'System Bar') }}</title>
    <link rel="icon" href="{{ asset('assets/favicon.png') }}" type="image/png">
    <link rel="icon" href="{{ asset('assets/imgs/logo.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-surface-muted text-ink">
    <div class="flex min-h-screen items-center justify-center px-6 py-12">
        <div class="w-full max-w-xl rounded-card border border-edge bg-surface p-8 text-center shadow-sm">
            <h1 class="text-3xl font-extrabold text-brand-900">@yield('title')</h1>

            <p class="mt-4 text-xl leading-relaxed text-ink">@yield('message')</p>

            <a href="{{ url('/') }}"
               class="mt-8 inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-700 px-8 py-4 text-xl font-bold text-white transition-colors hover:bg-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                Voltar para o início
            </a>
        </div>
    </div>
</body>
</html>
