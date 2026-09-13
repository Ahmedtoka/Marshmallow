<?php

namespace App\Http\Requests\Admin\Content;

class SeoPageRequest extends ContentRequest
{
    protected array $images = ['og_image'];

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ] + $this->imageRules();
    }
}
