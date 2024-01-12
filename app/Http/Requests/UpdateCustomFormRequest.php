<?php

namespace App\Http\Requests;

use App\Enums\CustomFormStatus;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $inputTypes = config('constants.custom_form_input_types');

        return [
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'alpha_dash',
                'max:255',
                Rule::unique('custom_forms')->ignore($this->route('customForm')),
            ],
            'description' => 'nullable|string|max:50000',
            'status' => ['required', new EnumValue(CustomFormStatus::class)],
            'can_create_submission' => 'required|string|in:anyone,auth,staff',
            'min_role_weight_to_view_submission' => 'nullable|integer',
            'max_submission_per_user' => 'nullable|integer|min:1',
            'is_notify_staff_on_submission' => 'required|boolean',
            'is_visible_in_listing' => 'required|boolean',
            'fields' => 'required|array',
            'fields.*.type' => 'required|string|in:'.implode(',', $inputTypes),
            'fields.*.label' => 'required|string|max:255',
            'fields.*.name' => 'required|string|alpha_dash|max:100|distinct:ignore_case',
            'fields.*.placeholder' => 'sometimes|nullable|string|max:255',
            'fields.*.help' => 'sometimes|nullable|string|max:255',
            'fields.*.validation' => 'sometimes|nullable|string|max:255',
            'fields.*.options' => 'sometimes|nullable|string',
        ];
    }
}
