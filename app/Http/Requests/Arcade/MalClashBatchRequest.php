<?php

namespace App\Http\Requests\Arcade;

use App\Services\MalClashService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MalClashBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stat' => ['nullable', Rule::in(MalClashService::STATS)],
            'rounds' => ['nullable', 'integer', 'min:1', 'max:20'],
        ];
    }
}
