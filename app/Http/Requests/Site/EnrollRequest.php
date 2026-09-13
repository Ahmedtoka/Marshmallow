<?php

namespace App\Http\Requests\Site;

use App\Support\ClassFinder;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnrollRequest extends FormRequest
{
    public const PHONE_PATTERN = '/^01[0125]\d{8}$/';

    public const INTERESTS = [
        'enrollment' => 'Enrolling my child',
        'tour' => 'Visiting the nursery',
        'camp' => 'A holiday camp',
        'general' => 'A general question',
    ];

    public const TOUR_SLOTS = [
        '10:00' => '10:00 am',
        '11:00' => '11:00 am',
        '12:00' => '12:00 pm',
        '13:00' => '1:00 pm',
    ];

    public const HEARD_FROM = ['Facebook', 'Instagram', 'Google', 'Friend or family', 'Passing by', 'Other'];

    public function authorize(): bool
    {
        return true;
    }

    /** Accepts 010 1234 5678, +20 10 1234 5678, 20101234567 8 and Arabic digits; returns 01XXXXXXXXX. */
    public static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = strtr($phone, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
        $phone = preg_replace('/[\s\-\.\(\)\x{00A0}]/u', '', $phone);

        if (str_starts_with($phone, '+20')) {
            $phone = '0'.substr($phone, 3);
        } elseif (str_starts_with($phone, '0020')) {
            $phone = '0'.substr($phone, 4);
        } elseif (preg_match('/^201\d{9}$/', $phone)) {
            $phone = '0'.substr($phone, 2);
        }

        return $phone === '' ? null : $phone;
    }

    protected function prepareForValidation(): void
    {
        $same = $this->boolean('whatsapp_same');
        $phone = self::normalizePhone($this->input('phone'));

        $this->merge([
            'phone' => $phone,
            'whatsapp_same' => $same,
            // When WhatsApp is the same number it is copied from the phone in leadData(), so only the phone is validated.
            'whatsapp' => $same ? null : self::normalizePhone($this->input('whatsapp')),
            'interest' => $this->input('interest') ?: 'enrollment',
        ]);
    }

    public function rules(): array
    {
        return [
            'parent_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:'.self::PHONE_PATTERN],
            'whatsapp_same' => ['boolean'],
            'whatsapp' => ['nullable', 'string', 'regex:'.self::PHONE_PATTERN],
            'email' => ['nullable', 'email', 'max:190'],
            'child_name' => ['nullable', 'string', 'max:120'],
            'child_dob' => ['nullable', 'date_format:Y-m-d', 'before:today', 'after:'.now()->subYears(14)->toDateString()],
            'academic_year' => ['nullable', Rule::in(ClassFinder::academicYears())],
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')->where('is_active', true)],
            'interest' => ['required', Rule::in([...array_keys(self::INTERESTS), 'waitlist'])],
            'camp_id' => ['nullable', 'required_if:interest,camp', 'integer', Rule::exists('camps', 'id')->where('is_active', true)],
            'tour_date' => ['nullable', 'date_format:Y-m-d', 'after:today', function (string $attribute, mixed $value, Closure $fail) {
                if ($value && in_array(Carbon::parse($value)->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY], true)) {
                    $fail('We’re closed on Friday and Saturday. Please pick a day from Sunday to Thursday.');
                }
            }],
            'tour_time' => ['nullable', 'required_with:tour_date', Rule::in(array_keys(self::TOUR_SLOTS))],
            'heard_from' => ['nullable', Rule::in(self::HEARD_FROM)],
            'message' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_name.required' => 'Please tell us your name.',
            'phone.required' => 'Please add a mobile number so we can call you back.',
            'phone.regex' => 'Please enter an Egyptian mobile number with 11 digits, like 010 1234 5678.',
            'whatsapp.regex' => 'Please enter the WhatsApp number as an Egyptian mobile number, like 010 1234 5678.',
            'email.email' => 'That email address doesn’t look right.',
            'child_dob.before' => 'Your child’s birthday must be in the past.',
            'child_dob.after' => 'Please check your child’s birthday.',
            'child_dob.date_format' => 'Please pick your child’s birthday from the calendar.',
            'branch_id.required' => 'Please choose the branch you’d like to visit.',
            'branch_id.exists' => 'Please choose one of our branches.',
            'camp_id.required_if' => 'Please choose which camp you’re interested in.',
            'tour_date.after' => 'Please pick a visit day from tomorrow onwards.',
            'tour_time.required_with' => 'Please pick a time for your visit.',
        ];
    }

    public function attributes(): array
    {
        return [
            'parent_name' => 'your name',
            'child_dob' => 'birthday',
            'academic_year' => 'school year',
            'branch_id' => 'branch',
            'camp_id' => 'camp',
            'tour_date' => 'visit day',
            'tour_time' => 'visit time',
            'heard_from' => 'how you heard about us',
        ];
    }

    /** Only the columns a website lead may set. */
    public function leadData(): array
    {
        $data = $this->validated();

        $tourAt = ! empty($data['tour_date']) && ! empty($data['tour_time'])
            ? Carbon::parse($data['tour_date'].' '.$data['tour_time'])
            : null;

        return [
            'parent_name' => $data['parent_name'],
            'phone' => $data['phone'],
            'whatsapp' => ! empty($data['whatsapp_same']) ? $data['phone'] : ($data['whatsapp'] ?? null),
            'email' => $data['email'] ?? null,
            'child_name' => $data['child_name'] ?? null,
            'child_dob' => $data['child_dob'] ?? null,
            'academic_year' => $data['academic_year'] ?? null,
            'branch_id' => (int) $data['branch_id'],
            'camp_id' => $data['interest'] === 'camp' ? ($data['camp_id'] ?? null) : null,
            'interest' => $data['interest'],
            'preferred_tour_at' => $tourAt,
            'heard_from' => $data['heard_from'] ?? null,
            'message' => $data['message'] ?? null,
        ];
    }
}
