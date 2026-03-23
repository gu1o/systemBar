<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $hash = User::hashEmail($value);
                    if (User::where('email_hash', $hash)->where('id', '!=', $this->user()->id)->exists()) {
                        $fail(__('validation.unique', ['attribute' => __('validation.attributes.email')]));
                    }
                },
            ],
        ];
    }
}
