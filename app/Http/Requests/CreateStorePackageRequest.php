<?php

namespace App\Http\Requests;

use App\Enums\StorePackageCommandTrigger;
use App\Enums\StorePackageRequirementMode;
use App\Enums\StorePackageType;
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
        $this->merge(['slug' => $this->resolvedSlug()]);
    }

    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'slug' => array_merge($this->slugRules(), [
                Rule::unique('store_packages', 'slug'),
            ]),
        ]);
    }

    /**
     * The slug as it will be stored: whatever the admin typed, or the name if they left it blank.
     *
     * Normalised rather than merely validated, so what lands in the column is always something that
     * can appear in a URL. A name with no latin characters at all slugs to an empty string, which
     * would fail `required` and — the slug being derived rather than typed — leave the admin staring
     * at a form that will not submit and no field to fix. A random token is a worse URL than
     * `diamond-rank` and a better outcome than a dead button; they can edit it afterwards.
     */
    protected function resolvedSlug(): string
    {
        $submitted = Str::slug((string) $this->input('slug'));

        if ($submitted !== '') {
            return $submitted;
        }

        $fromName = Str::slug((string) $this->input('name'));

        return $fromName !== '' ? $fromName : 'package-'.Str::lower(Str::random(8));
    }

    /**
     * Shared with UpdateStorePackageRequest so the two cannot drift apart.
     *
     * @return array<int, mixed>
     */
    protected function slugRules(): array
    {
        return [
            'required', 'string', 'max:255',
            // Normalised in prepareForValidation, so this only catches what Str::slug could not
            // make usable at all.
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
        ];
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
            'type' => ['required', Rule::enum(StorePackageType::class)],

            // Minor units. The client never sends a formatted or decimal amount; the admin form
            // converts using the base currency exponent before submitting.
            'price' => 'required|integer|min:0',

            // Basis points, like coupons and sales: 2000 is 20% off. 10000 makes the package free.
            'discount_bp' => 'nullable|integer|min:0|max:10000',

            // With pay-what-you-want on, `price` becomes the minimum the buyer may enter.
            'is_pay_what_you_want' => 'required|boolean',
            'pay_what_you_want_max' => 'nullable|integer|min:0|gte:price',

            // Only meaningful for a package whose type issues a gift card.
            'gift_card_amount' => [
                'nullable', 'integer', 'min:1',
                Rule::requiredIf(fn () => $this->issuesGiftCard() && ! $this->boolean('is_gift_card_amount_same_as_price')),
            ],
            'is_gift_card_amount_same_as_price' => 'required|boolean',

            'sort_order' => 'nullable|integer|min:0',
            'is_visible' => 'required|boolean',
            'is_enabled' => 'required|boolean',
            'requires_login' => 'required|boolean',
            'is_featured' => 'required|boolean',
            'is_giftable' => 'required|boolean',

            'min_quantity' => 'required|integer|min:1|max:9999',
            'max_quantity' => 'nullable|integer|min:1|max:9999|gte:min_quantity',
            'player_purchase_limit' => 'nullable|integer|min:1',
            'player_purchase_limit_period_days' => 'nullable|integer|min:1',
            'global_purchase_limit' => 'nullable|integer|min:1',
            'global_purchase_limit_period_days' => 'nullable|integer|min:1',
            'expiry_duration_days' => 'nullable|integer|min:1',

            // Outside this window the package is neither listed nor purchasable.
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',

            // Packages the buyer must own first. required_packages_mode decides whether all of
            // them are needed or any single one.
            'required_packages' => 'nullable|array',
            'required_packages.*' => 'required|integer|distinct|exists:store_packages,id',
            'required_packages_mode' => ['required', Rule::enum(StorePackageRequirementMode::class)],

            // Inputs the buyer fills in for this package. Order matters, so it is a list rather
            // than a set: the position becomes the pivot's sort_order.
            'variables' => 'nullable|array',
            'variables.*' => 'required|integer|distinct|exists:store_variables,id',

            // Cells for the category's comparison table, keyed by field key. Narrowed to the
            // category's own fields in the controller, so only the shape is checked here.
            'comparison_values' => 'nullable|array',
            'comparison_values.*' => 'nullable|string|max:2000',

            'photo' => 'nullable|image|max:5120',

            // Optional per-currency price overrides, in that currency's minor units. Absent
            // currencies fall back to the converted-and-rounded base price.
            'prices' => 'nullable|array',
            'prices.*.currency_code' => 'required|string|size:3|exists:store_currencies,code',
            'prices.*.price' => 'required|integer|min:0',

            'commands' => 'nullable|array',
            'commands.*.id' => 'nullable|integer|exists:store_package_commands,id',
            'commands.*.trigger' => ['required', Rule::enum(StorePackageCommandTrigger::class)],
            'commands.*.command' => 'required|string|max:2000',
            'commands.*.is_player_online_required' => 'required|boolean',
            'commands.*.delay_seconds' => 'nullable|integer|min:0|max:2592000',
            // Empty means every server, which is what is_run_on_all_servers records.
            'commands.*.servers' => 'nullable|array',
            'commands.*.servers.*.id' => 'required|integer|exists:servers,id',
            'commands.*.is_repeat_per_quantity' => 'required|boolean',
            'commands.*.sort_order' => 'nullable|integer|min:0',

        ];
    }

    /**
     * Whether the submitted type sells a gift card, which is what makes the amount required.
     */
    protected function issuesGiftCard(): bool
    {
        return StorePackageType::tryFrom((string) $this->input('type'))?->issuesGiftCard() ?? false;
    }

    public function messages(): array
    {
        return [
            'gift_card_amount.required' => __('Enter the gift card amount, or tick "same as package price".'),
            'pay_what_you_want_max.gte' => __('The maximum cannot be below the minimum price.'),
            'available_until.after' => __('The removal date must be after the publish date.'),
            'slug.unique' => __('Another package already uses this URL. Pick a different one, or leave it blank to build it from the name.'),
            'slug.regex' => __('A URL slug may only use lowercase letters, numbers and hyphens.'),
        ];
    }
}
