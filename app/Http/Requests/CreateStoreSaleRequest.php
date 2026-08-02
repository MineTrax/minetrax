<?php

namespace App\Http\Requests;

use App\Enums\StoreDiscountType;
use App\Enums\StorePackageCommandTrigger;
use App\Enums\StoreSaleScope;
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

            // In base-currency minor units, so one threshold holds however the buyer is paying.
            // min:1 rather than min:0 because a zero threshold is indistinguishable from no
            // threshold and would hang a pointless "spend $0.00 to unlock" note on every card.
            'min_basket_amount' => 'nullable|integer|min:1',

            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'is_enabled' => 'required|boolean',

            // The scope is declared rather than inferred from which picker was filled in, so that
            // emptying a picker cannot quietly promote a targeted sale to a store-wide one. That
            // is also why the matching list is required: choosing "packages" and naming none is a
            // half-finished form, not a request to discount the catalogue.
            'scope_type' => ['required', Rule::enum(StoreSaleScope::class)],
            'packages' => 'required_if:scope_type,packages|array',
            'packages.*' => 'integer|exists:store_packages,id',
            'categories' => 'required_if:scope_type,categories|array',
            'categories.*' => 'integer|exists:store_categories,id',

            // The extra a sale hands out on top of its discount: "10% off, and 100 bonus coins".
            'commands' => 'nullable|array',
            'commands.*.id' => 'nullable|integer|exists:store_package_commands,id',
            'commands.*.trigger' => ['required', Rule::enum(StorePackageCommandTrigger::class)],
            'commands.*.command' => 'required|string|max:2000',
            'commands.*.is_player_online_required' => 'required|boolean',
            'commands.*.delay_seconds' => 'nullable|integer|min:0|max:2592000',
            // Empty means every server, which is what is_run_on_all_servers records.
            'commands.*.servers' => 'nullable|array',
            'commands.*.servers.*.id' => 'required|integer|exists:servers,id',
            // Empty means every package the sale discounted, which is what is_run_on_all_packages
            // records.
            'commands.*.packages' => 'nullable|array',
            'commands.*.packages.*.id' => 'required|integer|exists:store_packages,id',
            'commands.*.is_repeat_per_quantity' => 'required|boolean',
            'commands.*.sort_order' => 'nullable|integer|min:0',
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
            'packages.required_if' => __('Pick at least one package, or set the sale to run store-wide.'),
            'categories.required_if' => __('Pick at least one category, or set the sale to run store-wide.'),
        ];
    }
}
