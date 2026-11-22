<?php

namespace App\Http\Requests\Arcade;

use Illuminate\Foundation\Http\FormRequest;

class RedFlagScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $size = (int) config('redflags.characters_per_scan');

        return [
            'character_ids' => ['required', 'array', "size:$size"],
            'character_ids.*' => ['integer', 'distinct', 'exists:characters,id'],
        ];
    }
}
