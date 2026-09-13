<?php

namespace App\Http\Requests\Admin\Content;

use App\Models\Activity;
use App\Support\Icons;
use Illuminate\Validation\Rule;

class ActivityRequest extends ContentRequest
{
    protected array $booleans = ['is_active'];

    protected array $images = ['cover_image'];

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'alpha_dash', 'max:255', Rule::unique('activities', 'slug')->ignore($this->route('activity')?->id)],
            'category' => ['required', Rule::in(array_keys(Activity::CATEGORIES))],
            'icon' => ['required', Rule::in(Icons::NAMES)],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:20000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ] + $this->imageRules();
    }
}
