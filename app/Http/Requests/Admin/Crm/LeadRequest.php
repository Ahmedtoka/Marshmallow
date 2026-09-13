<?php

namespace App\Http\Requests\Admin\Crm;

use App\Models\Lead;
use App\Support\ClassFinder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadRequest extends FormRequest
{
    public const PHONE_REGEX = '/^01[0125]\d{8}$/';

    public function authorize(): bool
    {
        return true;
    }

    /** Accepts "+20 100 123 4567", "0020...", Arabic digits, spaces and dashes; stores 01XXXXXXXXX. */
    public static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', strtr($phone, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]));

        if (str_starts_with($digits, '0020')) {
            $digits = substr($digits, 3);
        } elseif (str_starts_with($digits, '20') && strlen($digits) === 12) {
            $digits = '0'.substr($digits, 2);
        } elseif (str_starts_with($digits, '1') && strlen($digits) === 10) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    protected function prepareForValidation(): void
    {
        // Legacy clients can post Windows-1252 text; repair it so MariaDB never rejects the row.
        foreach (['parent_name', 'child_name', 'heard_from', 'message', 'email'] as $field) {
            $value = $this->input($field);
            if (is_string($value) && ! mb_check_encoding($value, 'UTF-8')) {
                $this->merge([$field => mb_convert_encoding($value, 'UTF-8', 'Windows-1252')]);
            }
        }

        $this->merge([
            'phone' => self::normalizePhone($this->input('phone')),
            'whatsapp' => self::normalizePhone($this->input('whatsapp')),
        ]);
    }

    public function rules(): array
    {
        $creating = $this->isMethod('post');
        $channels = array_keys(Lead::CHANNELS);
        if ($creating) {
            $channels = array_values(array_diff($channels, ['website']));
        }

        return [
            'parent_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:'.self::PHONE_REGEX],
            'whatsapp' => ['nullable', 'regex:'.self::PHONE_REGEX],
            'email' => ['nullable', 'email', 'max:150'],
            'child_name' => ['nullable', 'string', 'max:120'],
            'child_dob' => ['nullable', 'date', 'before_or_equal:today', 'after:'.now()->subYears(12)->toDateString()],
            'academic_year' => ['nullable', Rule::in(array_unique(array_filter(array_merge(ClassFinder::academicYears(), [$this->route('lead')?->academic_year]))))],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'interest' => ['required', Rule::in(array_keys(Lead::INTERESTS))],
            'camp_id' => ['nullable', 'integer', 'exists:camps,id'],
            'preferred_tour_at' => ['nullable', 'date'],
            'heard_from' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:3000'],
            'channel' => ['required', Rule::in($channels)],
            'priority' => ['required', Rule::in(array_keys(Lead::PRIORITIES))],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'allow_duplicate' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter an Egyptian mobile number like 01012345678.',
            'whatsapp.regex' => 'Enter an Egyptian mobile number like 01012345678.',
            'child_dob.after' => 'That birthday looks too far back — please check the year.',
        ];
    }

    public function attributes(): array
    {
        return ['branch_id' => 'branch', 'child_dob' => "child's birthday", 'camp_id' => 'camp'];
    }

    /** Lead columns only. */
    public function leadData(): array
    {
        $data = collect($this->validated())->except(['assigned_to', 'allow_duplicate'])->all();
        if (($data['interest'] ?? null) !== 'camp') {
            $data['camp_id'] = null;
        }

        return $data;
    }
}
