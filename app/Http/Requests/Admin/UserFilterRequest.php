<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filter.isActive' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', Rule::in(['name', '-name', 'email', '-email', 'createdAt', '-createdAt'])],
        ];
    }
}
