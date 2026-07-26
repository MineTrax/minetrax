<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Validation\Rule;

class UpdateStoreCurrencyRequest extends CreateStoreCurrencyRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('storeCurrency'));
    }

    public function rules(): array
    {
        $currency = $this->route('storeCurrency');

        return $this->baseRules() + [
            'code' => [
                'required', 'string', 'size:3', 'alpha',
                Rule::unique('store_currencies', 'code')->ignore($currency->id),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $currency = $this->route('storeCurrency');

            // The base currency anchors every stored base_total. Letting it be disabled or
            // re-denominated would silently invalidate historical revenue figures.
            if ($currency->is_base) {
                if (! $this->boolean('is_enabled')) {
                    $validator->errors()->add('is_enabled', __('The base currency cannot be disabled.'));
                }

                if ((float) $this->input('rate_to_base') !== 1.0) {
                    $validator->errors()->add('rate_to_base', __('The base currency rate is always 1.'));
                }
            }
        });
    }
}
