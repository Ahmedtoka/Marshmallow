<?php

namespace App\Http\Requests\Admin\Content;

use App\Models\Camp;
use Illuminate\Validation\Rule;

class CampRequest extends ContentRequest
{
    protected array $booleans = ['is_active', 'is_featured'];

    protected array $images = ['cover_image'];

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'alpha_dash', 'max:255', Rule::unique('camps', 'slug')->ignore($this->route('camp')?->id)],
            'season' => ['required', Rule::in(array_keys(Camp::SEASONS))],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'age_from' => ['required', 'integer', 'min:0', 'max:18'],
            'age_to' => ['required', 'integer', 'min:0', 'max:18', 'gte:age_from'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'meals' => ['nullable', 'string', 'max:255'],
            'badge' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:20000'],
            'activities' => ['nullable', 'array', 'max:60'],
            'activities.*' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ] + $this->imageRules();
    }

    public function attributes(): array
    {
        return parent::attributes() + ['age_to' => 'oldest age', 'age_from' => 'youngest age', 'ends_on' => 'end date', 'starts_on' => 'start date'];
    }
}
