<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Data de filtro vinda da URL (Y-m-d). Inválida vira null em vez de erro: é
     * filtro, não formulário. O ida-e-volta pelo formato recusa 2026-02-31, que o
     * strtotime() aceitaria.
     */
    protected function data(?string $valor): ?string
    {
        $data = \DateTime::createFromFormat('!Y-m-d', (string) $valor);

        return $data && $data->format('Y-m-d') === $valor ? $valor : null;
    }
}
