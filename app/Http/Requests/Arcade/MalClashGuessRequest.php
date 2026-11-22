<?php

namespace App\Http\Requests\Arcade;

use App\Services\MalClashService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MalClashGuessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stat' => ['required', Rule::in(MalClashService::STATS)],
            'left_id' => ['required', 'integer', 'exists:anime,id'],
            'right_id' => ['required', 'integer', 'different:left_id', 'exists:anime,id'],
            'guess' => ['required', Rule::in(['higher', 'lower'])],
        ];
    }
}
