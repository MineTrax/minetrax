<?php

namespace App\Http\Requests;

use App\Enums\StoreDiscountType;
use App\Models\StoreSale;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateStoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', StoreSale::class);
    }

    public function rules(): array
    {
        return $this->baseRules();
    }

    /**
     * Shared with UpdateStoreSaleRequest so the two cannot drift apart.
     *
     * @return array<string, mixed>
     */
    public function baseRules(): array
    {
        return [
            // Shown to the customer as the badge on the package card, so it is a label rather than
            // an internal name.
            'name' => 'required|string|max:255',
            'discount_type' => ['required', Rule::enum(StoreDiscountType::class)],
            // Percent arrives as basis points (2000 = 20%) and cannot exceed 100%; fixed arrives as
            // minor units of the base currency and converts to whatever the buyer is paying in.
            'discount_value' => array_filter([
                'required', 'integer', 'min:1',
                $this->isPercent() ? 'max:10000' : null,
            ]),

            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'is_enabled' => 'required|boolean',

            // No scope rows at all means the sale runs store-wide.
            'packages' => 'nullable|array',
            'packages.*' => 'integer|exists:store_packages,id',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:store_categories,id',
        ];
    }

    protected function isPercent(): bool
    {
        return StoreDiscountType::tryFrom((string) $this->input('discount_type')) === StoreDiscountType::PERCENT;
    }

    public function messages(): array
    {
        return [
            'discount_value.max' => __('A percentage discount cannot exceed 100%.'),
            'ends_at.after' => __('The end date must be after the start date.'),
        ];
    }
}
