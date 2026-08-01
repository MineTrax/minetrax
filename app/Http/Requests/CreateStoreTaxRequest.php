<?php

namespace App\Http\Requests;

use App\Models\StoreTax;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateStoreTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', StoreTax::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            // Null is the fallback rule. Unique either way: two rules for one country, or two
            // fallbacks, would make the rate ambiguous — and an ambiguous tax rate is a liability.
            'country_id' => [
                'nullable',
                'integer',
                'exists:countries,id',
                Rule::unique('store_taxes', 'country_id')->ignore($this->route('storeTax')?->id),
            ],
            // Basis points. Capped at 100%: a rate above that is a typo, and an exclusive rule
            // would more than double what the buyer agreed to pay.
            'rate_bp' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_inclusive' => ['required', 'boolean'],
            'is_enabled' => ['required', 'boolean'],
        ];
    }

    /**
     * Rule::unique does not catch a second country-less rule, because SQL treats each NULL as
     * distinct. This does.
     */
    public function after(): array
    {
        return [
            function ($validator) {
                if ($this->input('country_id') !== null) {
                    return;
                }

                $exists = StoreTax::global()
                    ->when($this->route('storeTax'), fn ($q, $tax) => $q->whereKeyNot($tax->id))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('country_id', __('There is already a global rule. Edit that one, or pick a country.'));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'country_id.unique' => __('That country already has a tax rule.'),
        ];
    }
}
