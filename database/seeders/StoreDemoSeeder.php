<?php

namespace Database\Seeders;

use App\Enums\StoreCategoryDisplayType;
use App\Enums\StoreCommandTrigger;
use App\Enums\StorePackageRequirementMode;
use App\Enums\StorePackageType;
use App\Enums\StoreVariableType;
use App\Models\StoreCategory;
use App\Models\StorePackage;
use App\Models\StoreVariable;
use App\Services\StoreCurrencyService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Throwaway demo data for exercising the store by hand.
 *
 * Not registered in DatabaseSeeder — run it deliberately:
 *
 *     php artisan db:seed --class=StoreDemoSeeder
 *
 * Re-runnable: it force-deletes only the slugs and identifiers listed in OWN_SLUGS and
 * OWN_VARIABLES below, so editing a command here and re-running picks up the change without
 * piling up duplicates. It touches nothing it did not create.
 *
 * Every command runs on all servers, and works with no servers configured at all — dispatch simply
 * finds nothing to send to.
 *
 * Delete this file once manual testing is done.
 */
class StoreDemoSeeder extends Seeder
{
    /**
     * Category slugs this seeder owns. Packages are removed with their category.
     */
    private const OWN_CATEGORY_SLUGS = ['ranks', 'coins', 'lists', 'stacked'];

    /**
     * Package slugs this seeder owns, including any left behind by an earlier run whose category
     * has since been renamed.
     */
    private const OWN_PACKAGE_SLUGS = [
        'vip', 'vip-plus', 'mvp',
        // Str::slug drops the thousands comma rather than turning it into a separator.
        '1000-coins', '5000-coins', '25000-coins',
        'custom-name-tag', 'gift-card-100', 'fly-perk-30-days', 'name-your-price-donation', 'pet-dragon',
        'crate-key', 'diamond-bundle', 'xp-bottle-pack',
    ];

    private const OWN_VARIABLES = ['name_tag_color', 'name_tag_value'];

    private StoreCurrencyService $currencies;

    public function run(): void
    {
        $this->currencies = app(StoreCurrencyService::class);

        $this->command?->info('Base currency: '.$this->currencies->base()->code);

        // One transaction, so a mistake while editing this file leaves nothing half-seeded behind.
        DB::transaction(function () {
            $this->clearPreviousRun();

            $variables = $this->seedVariables();

            $this->seedRanks();
            $this->seedCoins();
            $this->seedLists($variables);
            $this->seedStacked();

            $this->linkRequirements();
        });

        $this->command?->info('Seeded '.StorePackage::whereIn('slug', self::OWN_PACKAGE_SLUGS)->count().' demo packages across 4 categories.');
    }

    /**
     * A decimal amount in the store's base currency, as the integer minor units everything is
     * stored in.
     *
     * Asked of StoreCurrencyService rather than scaled here, so this seeder is still correct on a
     * store whose base currency has no minor unit (JPY) or three digits of it (KWD).
     */
    private function money(string $amount): int
    {
        return $this->currencies->toMinor($amount, $this->currencies->base());
    }

    /**
     * Remove what an earlier run of this seeder created, and nothing else.
     */
    private function clearPreviousRun(): void
    {
        $packages = StorePackage::withTrashed()
            ->where(function ($query) {
                $query->whereIn('slug', self::OWN_PACKAGE_SLUGS)
                    ->orWhereHas('category', fn ($q) => $q->whereIn('slug', self::OWN_CATEGORY_SLUGS));
            })
            ->get();

        foreach ($packages as $package) {
            // Force, not soft: a soft-deleted row would keep the slug and the next run would collide.
            // Order items and grants point at packages with nullOnDelete, so any test orders survive
            // as a record.
            $package->forceDelete();
        }

        StoreCategory::whereIn('slug', self::OWN_CATEGORY_SLUGS)->delete();
        StoreVariable::whereIn('identifier', self::OWN_VARIABLES)->delete();
    }

    /**
     * @return array<string, StoreVariable>
     */
    private function seedVariables(): array
    {
        $color = StoreVariable::create([
            'name' => 'Name Tag Color',
            'identifier' => 'name_tag_color',
            'description' => '<p>The colour your name tag is rendered in.</p>',
            'type' => StoreVariableType::SELECT,
            // The label is the value, so these are the exact words the command receives.
            'options' => 'red,green,aqua,yellow,light_purple,gold',
            'is_required' => true,
            'is_enabled' => true,
            'sort_order' => 0,
        ]);

        $value = StoreVariable::create([
            'name' => 'Name Tag Value',
            'identifier' => 'name_tag_value',
            'description' => '<p>Up to 16 characters. Letters, numbers and spaces.</p>',
            'type' => StoreVariableType::TEXT,
            'placeholder' => 'TheChosenOne',
            'is_required' => true,
            'max_length' => 16,
            'is_enabled' => true,
            'sort_order' => 1,
        ]);

        return ['color' => $color, 'value' => $value];
    }

    // --- Ranks: grid, and cumulative so upgrades only cost the difference ----------------------

    private function seedRanks(): void
    {
        $category = $this->category('Ranks', StoreCategoryDisplayType::GRID, [
            'description' => 'Permanent ranks. Upgrading only costs the difference.',
            'is_cumulative' => true,
            'sort_order' => 0,
        ]);

        $this->package($category, 'VIP', [
            'short_description' => 'Coloured chat, /hat and a home slot.',
            'description' => '<p>The starter rank. Keeps your name blue and gives you somewhere to put your hat.</p>',
            'price' => $this->money('9.99'),
            'is_featured' => true,
            'sort_order' => 0,
        ], $this->rankCommands('vip'));

        $this->package($category, 'VIP+', [
            // Str::slug drops the plus, which would collide with VIP.
            'slug' => 'vip-plus',
            'short_description' => 'Everything in VIP, plus /fly in the lobby and 3 homes.',
            'description' => '<p>For people who liked VIP enough to come back.</p>',
            'price' => $this->money('19.99'),
            'sort_order' => 1,
        ], $this->rankCommands('vipplus'));

        $this->package($category, 'MVP', [
            'short_description' => 'The lot. Requires VIP+.',
            'description' => '<p>Top of the tree. You have to hold VIP+ before this one is available.</p>',
            'price' => $this->money('34.99'),
            'is_giftable' => true,
            'sort_order' => 2,
        ], $this->rankCommands('mvp'));
    }

    /**
     * Grant on purchase, take it back on expiry, refund or chargeback. The revocation triggers are
     * what makes an admin refund observable in game.
     *
     * @return array<int, array<string, mixed>>
     */
    private function rankCommands(string $group): array
    {
        return [
            [
                'trigger' => StoreCommandTrigger::PURCHASE,
                'command' => 'lp user {PLAYER_USERNAME} parent add '.$group,
            ],
            [
                'trigger' => StoreCommandTrigger::PURCHASE,
                'command' => 'broadcast &e{PLAYER_USERNAME} &7just bought &6{PACKAGE_NAME}&7!',
                'delay_seconds' => 5,
            ],
            [
                'trigger' => StoreCommandTrigger::EXPIRY,
                'command' => 'lp user {PLAYER_USERNAME} parent remove '.$group,
            ],
            [
                'trigger' => StoreCommandTrigger::REFUND,
                'command' => 'lp user {PLAYER_USERNAME} parent remove '.$group,
            ],
            [
                'trigger' => StoreCommandTrigger::CHARGEBACK,
                'command' => 'lp user {PLAYER_USERNAME} parent remove '.$group,
            ],
        ];
    }

    // --- Coins: the comparison table ------------------------------------------------------------

    private function seedCoins(): void
    {
        $category = $this->category('Coins', StoreCategoryDisplayType::COMPARISON, [
            'description' => 'In-game currency. Bigger bundles carry a bonus.',
            'comparison_fields' => [
                ['key' => 'field_1', 'name' => 'Coins', 'description' => 'no of coins', 'type' => 'text'],
                ['key' => 'field_2', 'name' => 'Bonus', 'description' => 'no of bonus coins', 'type' => 'text'],
                ['key' => 'field_3', 'name' => 'Instant', 'description' => 'delivered on purchase?', 'type' => 'check'],
            ],
            'sort_order' => 1,
        ]);

        $bundles = [
            ['name' => '1,000 Coins', 'price' => '4.99', 'coins' => 1000, 'bonus' => 0, 'discount_bp' => 0, 'featured' => false],
            ['name' => '5,000 Coins', 'price' => '19.99', 'coins' => 5000, 'bonus' => 500, 'discount_bp' => 1000, 'featured' => false],
            ['name' => '25,000 Coins', 'price' => '79.99', 'coins' => 25000, 'bonus' => 5000, 'discount_bp' => 0, 'featured' => true],
        ];

        foreach ($bundles as $index => $bundle) {
            $total = $bundle['coins'] + $bundle['bonus'];

            $this->package($category, $bundle['name'], [
                'short_description' => number_format($total).' coins in total.',
                'price' => $this->money($bundle['price']),
                'discount_bp' => $bundle['discount_bp'],
                'is_featured' => $bundle['featured'],
                'sort_order' => $index,
                'comparison_values' => [
                    'field_1' => number_format($bundle['coins']),
                    'field_2' => $bundle['bonus'] > 0 ? '<strong>+'.number_format($bundle['bonus']).'</strong>' : '—',
                    'field_3' => '1',
                ],
            ], [
                [
                    'trigger' => StoreCommandTrigger::PURCHASE,
                    'command' => 'eco give {PLAYER_USERNAME} '.$total,
                ],
            ]);
        }
    }

    // --- Lists: the listing layout, and a spread of package features ---------------------------

    /**
     * @param  array<string, StoreVariable>  $variables
     */
    private function seedLists(array $variables): void
    {
        $category = $this->category('Lists', StoreCategoryDisplayType::LISTING, [
            'description' => 'A mixed bag, laid out as a list.',
            'sort_order' => 2,
        ]);

        // Variables: the buyer fills these in before it can go in the cart.
        $nameTag = $this->package($category, 'Custom Name Tag', [
            'short_description' => 'Pick your own colour and text above your head.',
            'description' => '<p>Choose a colour and up to 16 characters. Both are asked for before checkout.</p>',
            'price' => $this->money('7.49'),
            'sort_order' => 0,
        ], [
            [
                'trigger' => StoreCommandTrigger::PURCHASE,
                'command' => 'nte set {PLAYER_USERNAME} nametag {VARIABLE_NAME_TAG_COLOR} {VARIABLE_NAME_TAG_VALUE}',
            ],
        ]);

        $nameTag->variables()->attach([
            $variables['color']->id => ['sort_order' => 0],
            $variables['value']->id => ['sort_order' => 1],
        ]);

        // Sells store credit rather than an in-game delivery. Buying it mints a code the buyer can
        // redeem on a later order.
        $this->package($category, 'Gift Card ($100)', [
            'slug' => 'gift-card-100',
            'short_description' => 'Store credit for someone else. Or yourself.',
            'description' => '<p>Issues a redeemable code worth 100 once the payment clears.</p>',
            'type' => StorePackageType::GIFTCARD,
            'price' => $this->money('100'),
            'gift_card_amount' => $this->money('100'),
            'is_giftable' => true,
            'sort_order' => 1,
        ]);

        // Expires, and waits for the player to be online — the DEFERRED path.
        $this->package($category, 'Fly Perk (30 Days)', [
            'slug' => 'fly-perk-30-days',
            'short_description' => 'Creative flight everywhere, for a month.',
            'price' => $this->money('5.99'),
            'expiry_duration_days' => 30,
            'sort_order' => 2,
        ], [
            [
                'trigger' => StoreCommandTrigger::PURCHASE,
                'command' => 'lp user {PLAYER_USERNAME} permission set essentials.fly true',
                // Queues and runs the moment they next join, rather than failing while offline.
                'is_player_online_required' => true,
            ],
            [
                'trigger' => StoreCommandTrigger::EXPIRY,
                'command' => 'lp user {PLAYER_USERNAME} permission unset essentials.fly',
            ],
        ]);

        // Pay what you want, with a floor and a ceiling. Quantity is fixed at one.
        $this->package($category, 'Name Your Price Donation', [
            'short_description' => 'Whatever you think it is worth.',
            'description' => '<p>No perks, just our thanks. Minimum 1, maximum 500.</p>',
            'price' => $this->money('1'),
            'is_pay_what_you_want' => true,
            'pay_what_you_want_max' => $this->money('500'),
            'sort_order' => 3,
        ], [
            [
                'trigger' => StoreCommandTrigger::PURCHASE,
                'command' => 'broadcast &7Thanks for the support, &e{PLAYER_USERNAME}&7!',
            ],
        ]);

        // Members only, and one per person ever.
        $this->package($category, 'Pet Dragon', [
            'short_description' => 'One per account, ever. Members only.',
            'price' => $this->money('12.99'),
            'requires_login' => true,
            'player_purchase_limit' => 1,
            'sort_order' => 4,
        ], [
            [
                'trigger' => StoreCommandTrigger::PURCHASE,
                'command' => 'pet give {PLAYER_USERNAME} ender_dragon',
            ],
        ]);
    }

    // --- Stacked: bulk items bought by the unit ------------------------------------------------

    private function seedStacked(): void
    {
        $category = $this->category('Stacked', StoreCategoryDisplayType::STACKED, [
            'description' => 'Bulk items. Pick a quantity and go.',
            'sort_order' => 3,
        ]);

        // Repeat-per-quantity: buying 10 runs the command 10 times, which is what a crate key needs.
        $this->package($category, 'Crate Key', [
            'short_description' => 'One vote crate key. Buy as many as you like.',
            'price' => $this->money('1.49'),
            'min_quantity' => 1,
            'max_quantity' => 64,
            'is_featured' => true,
            'sort_order' => 0,
        ], [
            [
                'trigger' => StoreCommandTrigger::PURCHASE,
                'command' => 'crates key give {PLAYER_USERNAME} vote 1',
                'is_repeat_per_quantity' => true,
            ],
        ]);

        $this->package($category, 'Diamond Bundle', [
            'short_description' => '16 diamonds per bundle.',
            'price' => $this->money('0.99'),
            'min_quantity' => 1,
            'max_quantity' => 64,
            'sort_order' => 1,
        ], [
            [
                'trigger' => StoreCommandTrigger::PURCHASE,
                'command' => 'give {PLAYER_USERNAME} diamond 16',
                'is_repeat_per_quantity' => true,
            ],
        ]);

        // The other half of the quantity story: one command, with {QUANTITY} substituted. Also
        // carries a per-player limit that resets, so the rolling window is testable.
        $this->package($category, 'XP Bottle Pack', [
            'short_description' => 'Minimum 5 packs. Ten per player a day.',
            'price' => $this->money('2.49'),
            'min_quantity' => 5,
            'max_quantity' => 100,
            'player_purchase_limit' => 10,
            'player_purchase_limit_period_days' => 1,
            'sort_order' => 2,
        ], [
            [
                'trigger' => StoreCommandTrigger::PURCHASE,
                'command' => 'xp give {PLAYER_USERNAME} {QUANTITY}00',
            ],
        ]);
    }

    /**
     * MVP is gated behind VIP+, which is satisfied by an active grant or by buying both together.
     */
    private function linkRequirements(): void
    {
        $mvp = StorePackage::where('slug', 'mvp')->first();
        $vipPlus = StorePackage::where('slug', 'vip-plus')->first();

        if ($mvp && $vipPlus) {
            $mvp->update(['required_packages_mode' => StorePackageRequirementMode::ALL]);
            $mvp->requiredPackages()->sync([$vipPlus->id]);
        }
    }

    // --- Builders -------------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function category(string $name, StoreCategoryDisplayType $displayType, array $attributes = []): StoreCategory
    {
        return StoreCategory::create(array_merge([
            'name' => $name,
            'slug' => Str::slug($name),
            'display_type' => $displayType,
            'is_visible' => true,
            'is_enabled' => true,
            'is_cumulative' => false,
            'sort_order' => 0,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array<string, mixed>>  $commands
     */
    private function package(StoreCategory $category, string $name, array $attributes = [], array $commands = []): StorePackage
    {
        $package = StorePackage::create(array_merge([
            'store_category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($attributes['slug'] ?? $name),
            'type' => StorePackageType::MINECRAFT_PACKAGE,
            'discount_bp' => 0,
            'is_pay_what_you_want' => false,
            'is_visible' => true,
            'is_enabled' => true,
            'requires_login' => false,
            'is_featured' => false,
            'is_giftable' => false,
            'min_quantity' => 1,
            'sold_count' => 0,
            'required_packages_mode' => StorePackageRequirementMode::ALL,
        ], $attributes));

        foreach ($commands as $index => $command) {
            $package->commands()->create(array_merge([
                'is_player_online_required' => false,
                'delay_seconds' => 0,
                'is_repeat_per_quantity' => false,
                // Every demo command goes everywhere, which is also what an empty server list means.
                'is_run_on_all_servers' => true,
                'sort_order' => $index,
            ], $command));
        }

        return $package;
    }
}
