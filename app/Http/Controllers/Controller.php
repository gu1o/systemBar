<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

abstract class Controller
{
    /**
     * Períodos do filtro de Vendas e Faturamento, na ordem do select; o primeiro é o padrão.
     * Sem "todas as datas" de propósito: toda consulta tem um intervalo fechado.
     */
    public const PERIODOS = [
        'hoje' => 'Hoje',
        '7dias' => 'Últimos 7 dias',
        'mes' => 'Este mês',
        'mes-passado' => 'Mês passado',
        'personalizado' => 'Personalizado',
    ];

    /**
     * Data de filtro vinda da URL (Y-m-d). Inválida vira null em vez de erro: é
     * filtro, não formulário. O ida-e-volta pelo formato recusa 2026-02-31, que o
     * strtotime() aceitaria.
     */
    public static function data(?string $valor): ?string
    {
        $data = \DateTime::createFromFormat('!Y-m-d', (string) $valor);

        return $data && $data->format('Y-m-d') === $valor ? $valor : null;
    }

    /**
     * Período do filtro: [nome, de, ate], datas em Y-m-d, sempre as duas.
     *
     * Vem por nome (?periodo=mes) e as datas são calculadas aqui, na hora: um link
     * salvo de "este mês" continua sendo este mês no mês que vem. "personalizado"
     * usa ?de=&ate=; link antigo só com de/ate também cai nele. Nome desconhecido
     * vira o padrão (hoje).
     *
     * @return array{0: string, 1: string, 2: string}
     */
    public static function periodo(Request $request): array
    {
        $periodo = $request->query('periodo');
        if (! isset(self::PERIODOS[$periodo])) {
            $periodo = $request->hasAny(['de', 'ate']) ? 'personalizado' : array_key_first(self::PERIODOS);
        }

        $hoje = today();
        [$de, $ate] = array_map(fn ($dia) => Carbon::parse($dia)->toDateString(), match ($periodo) {
            'hoje' => [$hoje, $hoje],
            '7dias' => [$hoje->copy()->subDays(6), $hoje],
            'mes' => [$hoje->copy()->startOfMonth(), $hoje],
            'mes-passado' => [$hoje->copy()->subMonthNoOverflow()->startOfMonth(), $hoje->copy()->subMonthNoOverflow()->endOfMonth()],
            // Data que faltar (ou inválida) vira hoje: nunca uma consulta sem limite.
            'personalizado' => [self::data($request->query('de')) ?? $hoje, self::data($request->query('ate')) ?? $hoje],
        });

        // "De 30 até 1" é engano de digitação, não pedido de período vazio.
        return [$periodo, min($de, $ate), max($de, $ate)];
    }
}
