<?php

namespace App\Http\Requests\Admin\Content;

class TestimonialRequest extends ContentRequest
{
    protected array $booleans = ['is_visible', 'is_featured'];

    protected array $images = ['photo'];

    public function rules(): array
    {
        return [
            'parent_name' => ['required', 'string', 'max:255'],
            'relation' => ['nullable', 'string', 'max:255'],
            'quote' => ['required', 'string', 'max:3000'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'is_visible' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ] + $this->imageRules();
    }
}
