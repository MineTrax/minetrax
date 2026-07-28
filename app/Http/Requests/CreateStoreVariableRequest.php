<?php

namespace App\Http\Requests;

use App\Enums\StoreVariableType;
use App\Models\StoreVariable;
use App\Services\StoreVariableService;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreateStoreVariableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', StoreVariable::class);
    }

    protected function prepareForValidation(): void
    {
        // The identifier becomes a command placeholder, so it is normalised rather than merely
        // validated: whatever an admin types, what lands in the column is a lowercase snake_case
        // token they can read back off {VARIABLE_...}.
        $this->merge([
            'identifier' => Str::slug((string) $this->input('identifier'), '_'),
        ]);
    }

    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'identifier' => array_merge($this->identifierRules(), [
                Rule::unique('store_variables', 'identifier'),
            ]),
        ]);
    }

    /**
     * Shared with UpdateStoreVariableRequest so the two cannot drift apart.
     *
     * @return array<string, mixed>
     */
    public function baseRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:20000',
            'type' => ['required', Rule::enum(StoreVariableType::class)],

            // A select or radio without choices would render an empty input the buyer cannot use.
            'options' => [
                'nullable', 'string', 'max:2000',
                Rule::requiredIf(fn () => $this->typeHasOptions()),
            ],

            'placeholder' => 'nullable|string|max:255',
            'is_required' => 'required|boolean',
            // Free text only, and capped: the value is substituted into a command.
            'max_length' => 'nullable|integer|min:1|max:255',
            'is_enabled' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function identifierRules(): array
    {
        return [
            'required', 'string', 'max:64',
            // Normalised in prepareForValidation, so this only catches input that slug() could not
            // make into a usable token at all — an identifier of punctuation, say.
            'regex:/^[a-z][a-z0-9_]*$/',
            Rule::notIn(app(StoreVariableService::class)->reservedIdentifiers()),
        ];
    }

    protected function typeHasOptions(): bool
    {
        return StoreVariableType::tryFrom((string) $this->input('type'))?->hasOptions() ?? false;
    }

    public function messages(): array
    {
        return [
            'identifier.regex' => __('The identifier must start with a letter and use only lowercase letters, numbers and underscores.'),
            'identifier.not_in' => __('That identifier is reserved by a built-in command placeholder.'),
            'options.required' => __('List the choices, separated by commas.'),
        ];
    }
}
