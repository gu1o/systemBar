<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * §T4 — mesmas regras em `store()` e `update()`, uma cópia cada.
 */
class CustomerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ];
    }
}
