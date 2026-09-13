<?php

namespace App\Http\Requests\Admin\Content;

use App\Models\Classroom;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ClassroomRequest extends ContentRequest
{
    public const ICONS = [
        'cupcake' => 'Cupcake',
        'popcorn' => 'Popcorn',
        'candy' => 'Candy',
        'icecream' => 'Ice cream',
        'lollipop' => 'Lollipop',
        'cottoncandy' => 'Cotton candy',
    ];

    protected array $booleans = ['is_active'];

    protected array $images = ['cover_image'];

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'alpha_dash', 'max:255', Rule::unique('classrooms', 'slug')->ignore($this->route('classroom')?->id)],
            'tagline' => ['nullable', 'string', 'max:255'],
            'min_months' => ['required', 'integer', 'min:0', 'max:216'],
            'max_months' => ['nullable', 'integer', 'min:1', 'max:216'],
            'age_label' => ['nullable', 'string', 'max:255'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['required', Rule::in(array_keys(self::ICONS))],
            'summary' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:20000'],
            'goals' => ['nullable', 'array', 'max:30'],
            'goals.*' => ['nullable', 'string', 'max:255'],
            'daily_routine' => ['nullable', 'array', 'max:40'],
            'daily_routine.*.time' => ['nullable', 'string', 'max:20'],
            'daily_routine.*.label' => ['nullable', 'string', 'max:255'],
            'teacher_ratio' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ] + $this->imageRules();
    }

    public function attributes(): array
    {
        return parent::attributes() + ['min_months' => 'starting age', 'max_months' => 'upper age', 'color' => 'color'];
    }

    public function messages(): array
    {
        return parent::messages() + ['color.regex' => 'Choose a color or enter a hex code like #E8177F.'];
    }

    /** Active classes must not share ages: the class finder places each child in exactly one class. */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->hasAny(['min_months', 'max_months'])) {
                return;
            }

            $min = (int) $this->input('min_months');
            $max = $this->filled('max_months') ? (int) $this->input('max_months') : null;

            if ($max !== null && $min >= $max) {
                $validator->errors()->add('max_months', 'The upper age must be greater than the starting age.');

                return;
            }

            if (! $this->boolean('is_active')) {
                return;
            }

            $ignore = $this->route('classroom')?->id;
            $overlap = Classroom::query()
                ->where('is_active', true)
                ->when($ignore, fn ($q) => $q->whereKeyNot($ignore))
                ->orderBy('min_months')
                ->get()
                ->first(fn (Classroom $other) => $min < ($other->max_months ?? PHP_INT_MAX) && $other->min_months < ($max ?? PHP_INT_MAX));

            if ($overlap) {
                $to = $overlap->max_months === null ? 'school age' : $overlap->max_months.' months';
                $validator->errors()->add('min_months', "This age range overlaps {$overlap->name} ({$overlap->min_months} months – {$to}). Active classes can’t share ages because the class finder uses them to place each child.");
            }
        }];
    }
}
