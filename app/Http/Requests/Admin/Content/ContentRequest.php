<?php

namespace App\Http\Requests\Admin\Content;

use Illuminate\Foundation\Http\FormRequest;

abstract class ContentRequest extends FormRequest
{
    /** Toggle fields; always stored as true/false. */
    protected array $booleans = [];

    /** Image upload fields; handled by HandlesUploads, never mass-assigned. */
    protected array $images = [];

    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public static function imageRule(): array
    {
        return ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'];
    }

    protected function imageRules(): array
    {
        $rules = [];
        foreach ($this->images as $field) {
            $rules[$field] = self::imageRule();
            $rules['remove_'.$field] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return ['sort_order' => 'order'];
    }

    public function messages(): array
    {
        return [
            '*.image' => 'Please choose an image file (JPG, PNG or WebP).',
        ];
    }

    /** Validated values ready to save: booleans cast, image inputs removed, order defaulted. */
    public function saveData(): array
    {
        $skip = [];
        foreach ($this->images as $field) {
            $skip[] = $field;
            $skip[] = 'remove_'.$field;
        }

        $data = collect($this->validated())->except($skip)->all();

        foreach ($this->booleans as $field) {
            $data[$field] = $this->boolean($field);
        }

        if (array_key_exists('sort_order', $data)) {
            $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        }

        return $data;
    }
}
