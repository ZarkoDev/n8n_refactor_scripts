<?php

declare(strict_types=1);

namespace App\Http\Requests\AdScriptTask;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdScriptTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference_script' => ['required', 'string', 'min:1'],
            'outcome_description' => ['required', 'string', 'min:1'],
        ];
    }
}


