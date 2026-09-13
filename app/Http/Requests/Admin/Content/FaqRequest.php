<?php

namespace App\Http\Requests\Admin\Content;

class FaqRequest extends ContentRequest
{
    public const CATEGORIES = [
        'admissions' => 'Admissions',
        'daily' => 'Daily life',
        'learning' => 'Learning',
        'safety' => 'Safety',
        'camps' => 'Camps',
    ];

    protected array $booleans = ['is_visible'];

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:40', 'alpha_dash'],
            'is_visible' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
