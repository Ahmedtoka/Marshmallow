<?php

namespace App\Http\Requests\Admin\Content;

use App\Models\Highlight;
use App\Support\Icons;
use Illuminate\Validation\Rule;

class HighlightRequest extends ContentRequest
{
    protected array $booleans = ['is_visible'];

    public function rules(): array
    {
        return [
            'group' => ['required', Rule::in(array_keys(Highlight::GROUPS))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['required', Rule::in(Icons::NAMES)],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_visible' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
