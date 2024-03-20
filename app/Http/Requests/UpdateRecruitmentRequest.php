<?php

namespace App\Http\Requests;

use App\Enums\RecruitmentFormStatus;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecruitmentRequest extends FormRequest
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
                Rule::unique('recruitments')->ignore($this->route('recruitment')),
            ],
            'description' => 'nullable|string|max:50000',
            'status' => ['required', new EnumValue(RecruitmentFormStatus::class)],
            'max_submission_per_user' => 'nullable|integer|min:1',
            'submission_cooldown_in_seconds' => 'nullable|integer|min:1',
            'is_allow_only_player_linked_users' => 'required|boolean',
            'is_allow_only_verified_users' => 'required|boolean',
            'is_allow_messages_from_users' => 'required|boolean',
            'min_role_weight_to_view_submission' => 'nullable|integer',
            'min_role_weight_to_vote_on_submission' => 'nullable|integer',
            'min_role_weight_to_act_on_submission' => 'nullable|integer',
            'is_notify_staff_on_submission' => 'required|boolean',
            'related_role_id' => 'nullable|exists:roles,id',

            'fields' => 'required|array',
            'fields.*.type' => 'required|string|in:'.implode(',', $inputTypes),
            'fields.*.label' => 'required|string|max:255',
            'fields.*.name' => 'required|string|max:255|distinct:ignore_case',
            'fields.*.placeholder' => 'sometimes|nullable|string|max:255',
            'fields.*.help' => 'sometimes|nullable|string|max:255',
            'fields.*.validation' => 'sometimes|nullable|string|max:255',
            'fields.*.options' => 'sometimes|nullable|string',
        ];
    }
}
