<?php

namespace App\Http\Requests;

use App\Enums\StoreCommandTarget;
use App\Enums\StorePackageCommandTrigger;
use App\Enums\StorePackageOptionType;
use App\Models\StorePackage;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreateStorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', StorePackage::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'slug' => 'required|string|max:255|unique:store_packages,slug',
        ]);
    }

    /**
     * Shared with UpdateStorePackageRequest so the two cannot drift apart.
     *
     * @return array<string, mixed>
     */
    public function baseRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'store_category_id' => 'nullable|integer|exists:store_categories,id',
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:20000',

            // Minor units. The client never sends a formatted or decimal amount; the admin form
            // converts using the base currency exponent before submitting.
            'price' => 'required|integer|min:0',

            'sort_order' => 'nullable|integer|min:0',
            'is_visible' => 'required|boolean',
            'is_enabled' => 'required|boolean',
            'requires_login' => 'required|boolean',

            'is_run_on_all_servers' => 'required|boolean',
            'is_player_online_required' => 'required|boolean',
            'is_command_repeated_per_quantity' => 'required|boolean',

            'min_quantity' => 'required|integer|min:1|max:9999',
            'max_quantity' => 'nullable|integer|min:1|max:9999|gte:min_quantity',
            'stock_limit' => 'nullable|integer|min:1',
            'player_purchase_limit' => 'nullable|integer|min:1',
            'purchase_limit_period_days' => 'nullable|integer|min:1',
            'expiry_duration_days' => 'nullable|integer|min:1',

            'photo' => 'nullable|image|max:5120',

            'servers' => 'nullable|array',
            'servers.*' => 'integer|exists:servers,id',

            // Optional per-currency price overrides, in that currency's minor units. Absent
            // currencies fall back to the converted-and-rounded base price.
            'prices' => 'nullable|array',
            'prices.*.currency_code' => 'required|string|size:3|exists:store_currencies,code',
            'prices.*.price' => 'required|integer|min:0',

            'commands' => 'nullable|array',
            'commands.*.id' => 'nullable|integer|exists:store_package_commands,id',
            'commands.*.trigger' => ['required', Rule::enum(StorePackageCommandTrigger::class)],
            'commands.*.command' => 'required|string|max:2000',
            'commands.*.is_player_online_required' => 'nullable|boolean',
            'commands.*.delay_seconds' => 'nullable|integer|min:0|max:2592000',
            'commands.*.target' => ['required', Rule::enum(StoreCommandTarget::class)],
            'commands.*.is_repeat_per_quantity' => 'nullable|boolean',
            'commands.*.sort_order' => 'nullable|integer|min:0',

            'options' => 'nullable|array',
            'options.*.id' => 'nullable|integer|exists:store_package_options,id',
            'options.*.name' => 'required|string|max:255',
            // Substituted into commands as {PLACEHOLDER}, so it must be a safe token.
            'options.*.placeholder' => 'required|string|max:64|regex:/^[A-Z][A-Z0-9_]*$/',
            'options.*.type' => ['required', Rule::enum(StorePackageOptionType::class)],
            'options.*.description' => 'nullable|string|max:1000',
            'options.*.is_required' => 'required|boolean',
            'options.*.sort_order' => 'nullable|integer|min:0',

            'options.*.choices' => 'required|array|min:1',
            'options.*.choices.*.id' => 'nullable|integer|exists:store_package_option_choices,id',
            'options.*.choices.*.name' => 'required|string|max:255',
            'options.*.choices.*.value' => 'required|string|max:255',
            'options.*.choices.*.price_delta' => 'required|integer',
            'options.*.choices.*.is_enabled' => 'required|boolean',
            'options.*.choices.*.sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'options.*.placeholder.regex' => __('Placeholder must be UPPER_SNAKE_CASE, e.g. TIER.'),
            'options.*.choices.required' => __('Each option needs at least one choice.'),
        ];
    }
}
