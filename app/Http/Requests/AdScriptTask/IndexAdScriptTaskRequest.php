<?php

declare(strict_types=1);

namespace App\Http\Requests\AdScriptTask;

use Illuminate\Foundation\Http\FormRequest;

class IndexAdScriptTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:pending,completed,failed'],
            'search' => ['nullable', 'string', 'max:500'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}


