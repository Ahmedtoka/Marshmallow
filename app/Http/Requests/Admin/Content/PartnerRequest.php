<?php

namespace App\Http\Requests\Admin\Content;

use App\Models\Partner;
use Illuminate\Validation\Rule;

class PartnerRequest extends ContentRequest
{
    protected array $booleans = ['is_visible'];

    protected array $images = ['logo'];

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Partner::TYPES))],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_visible' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ] + $this->imageRules();
    }
}
