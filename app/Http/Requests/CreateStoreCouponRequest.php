<?php

namespace App\Http\Requests;

use App\Enums\StoreDiscountType;
use App\Models\StoreCoupon;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateStoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', StoreCoupon::class);
    }

    protected function prepareForValidation(): void
    {
        // Codes are compared uppercase when a buyer types one in, so that is what gets stored
        // whatever case the admin used. Spaces go too: a code with one in it cannot be typed back.
        $this->merge([
            'code' => strtoupper(preg_replace('/\s+/', '', (string) $this->input('code'))),
        ]);
    }

    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'code' => array_merge($this->codeRules(), [
                Rule::unique('store_coupons', 'code'),
            ]),
        ]);
    }

    /**
     * Shared with UpdateStoreCouponRequest so the two cannot drift apart.
     *
     * @return array<string, mixed>
     */
    public function baseRules(): array
    {
        return [
            'description' => 'nullable|string|max:255',

            'discount_type' => ['required', Rule::enum(StoreDiscountType::class)],
            // Percent arrives as basis points (2000 = 20%) and cannot exceed 100%; fixed arrives as
            // minor units of currency_code, where a cap would be meaningless.
            'discount_value' => array_filter([
                'required', 'integer', 'min:1',
                $this->isPercent() ? 'max:10000' : null,
            ]),
            // Fixed only. Null means the amount is in the base currency and converts at quote time.
            'currency_code' => [
                'nullable', 'string', 'size:3', 'exists:store_currencies,code',
                Rule::excludeIf($this->isPercent()),
            ],

            // In base-currency minor units, so one threshold holds however the buyer is paying.
            'min_basket_amount' => 'nullable|integer|min:0',
            'max_uses_total' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',

            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_enabled' => 'required|boolean',

            // No scope rows at all means the coupon applies store-wide.
            'packages' => 'nullable|array',
            'packages.*' => 'integer|exists:store_packages,id',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:store_categories,id',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function codeRules(): array
    {
        return [
            'required', 'string', 'max:64',
            // The buyer types this, so it stays to characters that survive a keyboard and a URL.
            'regex:/^[A-Z0-9_-]+$/',
        ];
    }

    protected function isPercent(): bool
    {
        return StoreDiscountType::tryFrom((string) $this->input('discount_type')) === StoreDiscountType::PERCENT;
    }

    public function messages(): array
    {
        return [
            'code.regex' => __('A coupon code may only use letters, numbers, hyphens and underscores.'),
            'discount_value.max' => __('A percentage discount cannot exceed 100%.'),
            'expires_at.after' => __('The end date must be after the start date.'),
        ];
    }
}
