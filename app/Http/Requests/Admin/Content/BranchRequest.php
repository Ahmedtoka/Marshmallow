<?php

namespace App\Http\Requests\Admin\Content;

use Illuminate\Validation\Rule;

class BranchRequest extends ContentRequest
{
    protected array $booleans = ['is_active'];

    protected array $images = ['image'];

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'alpha_dash', 'max:255', Rule::unique('branches', 'slug')->ignore($this->route('branch')?->id)],
            'short_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'address_note' => ['nullable', 'string', 'max:255'],
            'map_url' => ['nullable', 'url', 'max:255'],
            'map_embed_url' => ['nullable', 'url', 'max:2000'],
            'working_hours' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ] + $this->imageRules();
    }
}
