<?php

declare(strict_types=1);

namespace App\Http\Requests\AdScriptTask;

use Illuminate\Foundation\Http\FormRequest;

class AdScriptTaskResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_id' => ['required', 'integer', 'exists:ad_script_tasks,id'],
            'response' => ['required']
        ];
    }
}


