<?php

namespace App\Http\Requests;

use App\Enums\StorePriceRounding;
use App\Models\StoreCurrency;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateStoreCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', StoreCurrency::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['code' => strtoupper((string) $this->input('code'))]);
    }

    public function rules(): array
    {
        return $this->baseRules() + [
            'code' => 'required|string|size:3|alpha|unique:store_currencies,code',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function baseRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:8',
            'symbol_position' => 'required|string|in:prefix,suffix',

            // ISO-4217 minor unit digits. Wrong here and every amount in this currency is
            // mis-scaled, so it is constrained to the range ISO actually uses.
            'exponent' => 'required|integer|min:0|max:4',

            'rate_to_base' => 'required|numeric|gt:0',
            'is_enabled' => 'required|boolean',
            'price_rounding' => ['required', Rule::enum(StorePriceRounding::class)],
            'country_codes' => 'nullable|array',
            'country_codes.*' => 'string|size:2',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }
}
