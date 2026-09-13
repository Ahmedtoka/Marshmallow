<?php

namespace App\Http\Requests\Admin\Content;

class SectionRequest extends ContentRequest
{
    protected array $booleans = ['is_visible'];

    protected array $images = ['image'];

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:2000'],
            'body' => ['nullable', 'string', 'max:20000'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'string', 'max:255', 'regex:/^(\/|https?:\/\/|#|tel:|mailto:)/i'],
            'is_visible' => ['nullable', 'boolean'],
        ] + $this->imageRules();
    }

    public function messages(): array
    {
        return parent::messages() + ['button_url.regex' => 'Use a page on this website like /enroll, or a full link starting with https://'];
    }
}
