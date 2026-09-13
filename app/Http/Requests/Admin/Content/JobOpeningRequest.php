<?php

namespace App\Http\Requests\Admin\Content;

use Illuminate\Validation\Rule;

class JobOpeningRequest extends ContentRequest
{
    public const TYPES = ['Full time' => 'Full time', 'Part time' => 'Part time', 'Internship' => 'Internship', 'Temporary' => 'Temporary'];

    protected array $booleans = ['is_active'];

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'alpha_dash', 'max:255', Rule::unique('job_openings', 'slug')->ignore($this->route('job')?->id)],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'type' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'requirements' => ['nullable', 'array', 'max:40'],
            'requirements.*' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
