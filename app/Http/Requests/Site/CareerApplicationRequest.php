<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CareerApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => EnrollRequest::normalizePhone($this->input('phone'))]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:'.EnrollRequest::PHONE_PATTERN],
            'email' => ['nullable', 'email', 'max:190'],
            'position' => ['required', 'string', 'max:20'],
            'position_other' => ['nullable', 'required_if:position,other', 'string', 'max:120'],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')->where('is_active', true)],
            'message' => ['nullable', 'string', 'max:3000'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'website' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please tell us your name.',
            'phone.required' => 'Please add a mobile number.',
            'phone.regex' => 'Please enter an Egyptian mobile number with 11 digits, like 010 1234 5678.',
            'position.required' => 'Please choose the position you’re applying for.',
            'position_other.required_if' => 'Please tell us which role you’re interested in.',
            'cv.mimes' => 'Please upload your CV as a PDF or Word file.',
            'cv.max' => 'Your CV must be smaller than 5 MB.',
        ];
    }
}
