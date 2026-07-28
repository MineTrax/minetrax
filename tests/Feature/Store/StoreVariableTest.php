<?php

namespace Tests\Feature\Store;

use App\Enums\StorePackageCommandTrigger;
use App\Enums\StoreVariableType;
use App\Models\CommandQueue;
use App\Models\Player;
use App\Models\Server;
use App\Models\StoreCartItem;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StorePackageCommand;
use App\Models\StoreVariable;
use App\Models\User;
use App\Services\StoreCartService;
use App\Services\StoreOrderService;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Store variables: reusable fields the buyer fills in while ordering, whose answers are substituted
 * into the package's commands.
 */
class StoreVariableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);
        $this->baseCurrency();

        $settings = app(StoreSettings::class);
        $settings->enabled_gateways = ['manual'];
        $settings->save();

        $this->withCookie(StoreCartService::COOKIE, 'guest-cart-token');

        $this->withoutMiddleware([
            ThrottleRequests::class,
            ThrottleRequestsWithRedis::class,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Prefix Color',
            'identifier' => 'prefix_color',
            'description' => 'The colour of your name prefix.',
            'type' => StoreVariableType::TEXT->value,
            'options' => null,
            'placeholder' => null,
            'is_required' => true,
            'max_length' => 32,
            'is_enabled' => true,
            'sort_order' => 0,
        ], $overrides);
    }

    private function packageWithVariable(StoreVariable $variable, array $packageAttributes = []): StorePackage
    {
        $package = StorePackage::factory()->create(array_merge(['price' => 1000], $packageAttributes));
        $package->variables()->attach($variable->id, ['sort_order' => 0]);

        return $package->fresh('variables');
    }

    private function checkout(array $overrides = []): TestResponse
    {
        if (! Player::where('username', 'Steve')->exists()) {
            Player::factory()->create(['username' => 'Steve']);
        }

        return $this->post(route('store.checkout.store'), array_merge([
            'player_username' => 'Steve',
            'email' => 'buyer@example.com',
            'gateway' => 'manual',
            'accept_terms' => true,
        ], $overrides));
    }

    // --- Admin CRUD ----------------------------------------------------------------------------

    public function test_guest_and_non_staff_are_denied()
    {
        $this->get(route('admin.store.variable.index'))->assertStatus(302);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.store.variable.index'))->assertStatus(302);
    }

    public function test_admin_can_view_the_variable_listing()
    {
        $this->actingAs(User::whereId(1)->first());
        StoreVariable::factory()->count(2)->create();

        $this->get(route('admin.store.variable.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/StoreVariable/IndexStoreVariable', false)
                ->has('variables.data', 2)
            );
    }

    public function test_admin_can_create_a_variable()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.variable.store'), $this->validPayload())
            ->assertRedirect(route('admin.store.variable.index'));

        $this->assertDatabaseHas('store_variables', [
            'name' => 'Prefix Color',
            'identifier' => 'prefix_color',
            'type' => 'text',
        ]);
    }

    public function test_an_identifier_is_normalised_into_a_usable_token()
    {
        // Whatever an admin types, what lands in the column has to be readable back off a
        // {VARIABLE_...} placeholder.
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.variable.store'), $this->validPayload([
            'identifier' => 'Prefix Color!',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('prefix_color', StoreVariable::first()->identifier);
    }

    public function test_the_command_placeholder_is_namespaced()
    {
        $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color']);

        $this->assertSame('{VARIABLE_PREFIX_COLOR}', $variable->command_placeholder);
    }

    public function test_an_identifier_must_be_unique()
    {
        $this->actingAs(User::whereId(1)->first());
        StoreVariable::factory()->create(['identifier' => 'prefix_color']);

        $this->post(route('admin.store.variable.store'), $this->validPayload())
            ->assertSessionHasErrors(['identifier']);
    }

    public function test_an_identifier_that_shadows_a_built_in_placeholder_is_rejected()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.variable.store'), $this->validPayload([
            'identifier' => 'player_username',
        ]))->assertSessionHasErrors(['identifier']);
    }

    public function test_a_dropdown_must_carry_its_choices()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.variable.store'), $this->validPayload([
            'type' => StoreVariableType::SELECT->value,
            'options' => null,
        ]))->assertSessionHasErrors(['options']);
    }

    public function test_switching_away_from_a_dropdown_clears_its_choices()
    {
        $this->actingAs(User::whereId(1)->first());
        $variable = StoreVariable::factory()->select()->create();

        $this->put(route('admin.store.variable.update', $variable->id), $this->validPayload([
            'identifier' => $variable->identifier,
            'type' => StoreVariableType::TEXT->value,
            'options' => 'Red,Green',
        ]))->assertSessionHasNoErrors();

        $this->assertNull($variable->fresh()->options);
    }

    public function test_deleting_a_variable_leaves_placed_orders_readable()
    {
        $this->actingAs(User::whereId(1)->first());
        $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'name' => 'Prefix Color']);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'Gold'],
        ]);
        $this->checkout()->assertSessionHasNoErrors();

        $this->delete(route('admin.store.variable.delete', $variable->id));

        // The snapshot carries the name, so the order still reads correctly.
        $this->assertDatabaseMissing('store_variables', ['id' => $variable->id]);
        // assertEquals, not assertSame: MySQL normalises the key order of a JSON object, so the
        // round-trip is key-order insensitive by nature.
        $this->assertEquals(
            [['identifier' => 'prefix_color', 'name' => 'Prefix Color', 'value' => 'Gold']],
            StoreOrder::first()->items->first()->variable_values
        );
    }

    // --- Attaching to a package ----------------------------------------------------------------

    public function test_admin_can_attach_variables_to_a_package_in_order()
    {
        $this->actingAs(User::whereId(1)->first());
        $first = StoreVariable::factory()->create();
        $second = StoreVariable::factory()->create();

        $this->post(route('admin.store.package.store'), [
            'name' => 'VIP Rank',
            'type' => 'minecraft_package',
            'price' => 1000,
            'is_pay_what_you_want' => false,
            'is_gift_card_amount_same_as_price' => false,
            'is_visible' => true, 'is_enabled' => true, 'requires_login' => false,
            'is_featured' => false, 'is_giftable' => false,
            'min_quantity' => 1,
            'required_packages_mode' => 'all',
            'variables' => [$second->id, $first->id],
        ])->assertSessionHasNoErrors();

        $package = StorePackage::first();

        $this->assertEquals([$second->id, $first->id], $package->variables->pluck('id')->all());
    }

    public function test_the_package_page_exposes_a_formkit_schema_for_its_variables()
    {
        $variable = StoreVariable::factory()->select('Gold,Silver')->create([
            'name' => 'Prefix Color',
            'identifier' => 'prefix_color',
        ]);
        $package = $this->packageWithVariable($variable);

        $this->get(route('store.package', $package->slug))
            ->assertOk()
            ->assertInertia(function ($page) {
                $schema = $page->toArray()['props']['variableSchema'];

                $this->assertCount(1, $schema);
                $this->assertSame('select', $schema[0]['type']);
                $this->assertSame('prefix_color', $schema[0]['name']);
                $this->assertSame('Prefix Color', $schema[0]['label']);
                $this->assertSame('Gold,Silver', $schema[0]['options']);
                $this->assertSame('required', $schema[0]['validation']);
            });
    }

    public function test_a_disabled_variable_is_not_asked_for()
    {
        $package = $this->packageWithVariable(StoreVariable::factory()->disabled()->create());

        $this->get(route('store.package', $package->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('variableSchema', 0));
    }

    // --- The buyer filling them in --------------------------------------------------------------

    public function test_a_value_is_stored_on_the_cart_line()
    {
        $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color']);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'Gold'],
        ])->assertSessionHasNoErrors();

        $this->assertSame(
            ['prefix_color' => 'Gold'],
            StoreCartItem::first()->variable_values
        );
    }

    public function test_a_required_variable_cannot_be_left_blank()
    {
        $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'name' => 'Prefix Color']);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1])
            ->assertSessionHasErrors(['variables.prefix_color']);

        $this->assertDatabaseCount('store_cart_items', 0);
    }

    public function test_an_optional_variable_may_be_left_blank()
    {
        $variable = StoreVariable::factory()->optional()->create(['identifier' => 'nickname']);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1])
            ->assertSessionHasNoErrors();

        $this->assertNull(StoreCartItem::first()->variable_values);
    }

    public function test_a_dropdown_value_must_be_one_of_the_offered_choices()
    {
        $variable = StoreVariable::factory()->select('Gold,Silver')->create(['identifier' => 'tier']);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['tier' => 'Diamond'],
        ])->assertSessionHasErrors(['variables.tier']);
    }

    public function test_a_dropdown_value_is_stored_with_the_admins_own_spelling()
    {
        // The command receives the canonical spelling rather than whatever casing was posted.
        $variable = StoreVariable::factory()->select('Gold,Silver')->create(['identifier' => 'tier']);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['tier' => 'gOLD'],
        ])->assertSessionHasNoErrors();

        $this->assertSame(['tier' => 'Gold'], StoreCartItem::first()->variable_values);
    }

    public function test_a_number_variable_rejects_anything_that_is_not_a_number()
    {
        $variable = StoreVariable::factory()->number()->create(['identifier' => 'amount']);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['amount' => 'lots'],
        ])->assertSessionHasErrors(['variables.amount']);
    }

    public function test_a_value_longer_than_the_maximum_is_rejected()
    {
        $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'max_length' => 5]);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'far too long'],
        ])->assertSessionHasErrors(['variables.prefix_color']);
    }

    /**
     * The load-bearing one. These values are substituted into a command that runs on a live server,
     * so anything that could break out of an argument is refused rather than escaped.
     */
    public function test_characters_that_could_alter_a_command_are_refused()
    {
        $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'max_length' => 64]);
        $package = $this->packageWithVariable($variable);

        foreach (['Gold; op Bob', 'Gold | op Bob', 'Gold {PLAYER_USERNAME}', "Gold\nop Bob", 'Gold`whoami`', 'Gold$USER'] as $attempt) {
            $this->post(route('store.cart.store'), [
                'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => $attempt],
            ]);

            // Asserted by hand rather than with assertSessionHasErrors so the failure names the
            // input that got through.
            $errors = session('errors');

            $this->assertTrue(
                $errors !== null && $errors->has('variables.prefix_color'),
                "[{$attempt}] should have been refused."
            );
        }

        $this->assertDatabaseCount('store_cart_items', 0);
    }

    public function test_a_minecraft_colour_code_is_accepted()
    {
        // Refusing `&` would make the feature useless for the thing it exists for.
        $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'max_length' => 64]);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => '&6Gold [VIP]'],
        ])->assertSessionHasNoErrors();
    }

    public function test_a_value_for_a_variable_the_package_does_not_have_is_dropped()
    {
        $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color']);
        $stranger = StoreVariable::factory()->create(['identifier' => 'not_mine']);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id,
            'quantity' => 1,
            'variables' => ['prefix_color' => 'Gold', $stranger->identifier => 'injected'],
        ])->assertSessionHasNoErrors();

        $this->assertSame(['prefix_color' => 'Gold'], StoreCartItem::first()->variable_values);
    }

    public function test_re_adding_a_package_replaces_the_answers_rather_than_keeping_both()
    {
        $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color']);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'Gold'],
        ]);
        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'Silver'],
        ]);

        $this->assertDatabaseCount('store_cart_items', 1);
        $this->assertSame(['prefix_color' => 'Silver'], StoreCartItem::first()->variable_values);
    }

    public function test_the_cart_shows_the_answers_by_name()
    {
        $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'name' => 'Prefix Color']);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'Gold'],
        ]);

        $this->get(route('store.cart.show'))
            ->assertInertia(function ($page) {
                $variables = $page->toArray()['props']['quote']['items'][0]['variables'];

                $this->assertSame([['identifier' => 'prefix_color', 'name' => 'Prefix Color', 'value' => 'Gold']], $variables);
            });
    }

    // --- Checkout -------------------------------------------------------------------------------

    public function test_the_answers_are_snapshotted_onto_the_order_item()
    {
        $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'name' => 'Prefix Color']);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'Gold'],
        ]);
        $this->checkout()->assertSessionHasNoErrors();

        $this->assertEquals(
            [['identifier' => 'prefix_color', 'name' => 'Prefix Color', 'value' => 'Gold']],
            StoreOrder::first()->items->first()->variable_values
        );
    }

    public function test_a_variable_made_required_after_the_item_was_carted_blocks_checkout()
    {
        // The cart row is revalidated at purchase, not trusted.
        $variable = StoreVariable::factory()->optional()->create(['identifier' => 'prefix_color']);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1])
            ->assertSessionHasNoErrors();

        $variable->update(['is_required' => true]);

        $this->checkout()->assertSessionHasErrors(['variables.prefix_color']);
        $this->assertDatabaseCount('store_orders', 0);
    }

    public function test_a_choice_removed_after_the_item_was_carted_blocks_checkout()
    {
        $variable = StoreVariable::factory()->select('Gold,Silver')->create(['identifier' => 'tier']);
        $package = $this->packageWithVariable($variable);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['tier' => 'Gold'],
        ])->assertSessionHasNoErrors();

        $variable->update(['options' => 'Silver,Bronze']);

        $this->checkout()->assertSessionHasErrors(['variables.tier']);
    }

    // --- Delivery -------------------------------------------------------------------------------

    private function payFor(StorePackage $package): StoreOrder
    {
        $order = StoreOrder::latest('id')->first();

        app(StoreOrderService::class)->markPaid(
            $order,
            $order->payments()->first(),
            (int) $order->amount_due,
            $order->currency
        );

        return $order->fresh();
    }

    public function test_a_variable_value_is_substituted_into_the_command()
    {
        Server::factory()->create();
        $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color']);
        $package = $this->packageWithVariable($variable);
        StorePackageCommand::factory()->create([
            'store_package_id' => $package->id,
            'trigger' => StorePackageCommandTrigger::PURCHASE,
            'command' => 'lp user {PLAYER_USERNAME} meta setprefix {VARIABLE_PREFIX_COLOR}',
        ]);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'Gold'],
        ]);
        $this->checkout()->assertSessionHasNoErrors();
        $this->payFor($package);

        $this->assertSame(
            'lp user Steve meta setprefix Gold',
            CommandQueue::where('tag', 'store')->first()->parsed_command
        );
    }

    public function test_a_variable_cannot_overwrite_a_built_in_placeholder()
    {
        // The identifier check rejects `player_username` at creation, but the substitution keys are
        // namespaced too, so even a variable created before that rule cannot reach it.
        Server::factory()->create();
        $variable = StoreVariable::factory()->create(['identifier' => 'player_username']);
        $package = $this->packageWithVariable($variable);
        StorePackageCommand::factory()->create([
            'store_package_id' => $package->id,
            'command' => 'say {PLAYER_USERNAME}',
        ]);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['player_username' => 'Impostor'],
        ]);
        $this->checkout()->assertSessionHasNoErrors();
        $this->payFor($package);

        $this->assertSame('say Steve', CommandQueue::where('tag', 'store')->first()->parsed_command);
    }

    public function test_an_unanswered_optional_variable_leaves_its_placeholder_in_place()
    {
        // Nothing to substitute, so the raw placeholder survives rather than the command silently
        // becoming a different one.
        Server::factory()->create();
        $variable = StoreVariable::factory()->optional()->create(['identifier' => 'nickname']);
        $package = $this->packageWithVariable($variable);
        StorePackageCommand::factory()->create([
            'store_package_id' => $package->id,
            'command' => 'say hello {VARIABLE_NICKNAME}',
        ]);

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
        $this->checkout()->assertSessionHasNoErrors();
        $this->payFor($package);

        $this->assertSame(
            'say hello {VARIABLE_NICKNAME}',
            CommandQueue::where('tag', 'store')->first()->parsed_command
        );
    }

    public function test_a_checkbox_answer_substitutes_as_true_or_false()
    {
        Server::factory()->create();
        $variable = StoreVariable::factory()->checkbox()->create(['identifier' => 'glow']);
        $package = $this->packageWithVariable($variable);
        StorePackageCommand::factory()->create([
            'store_package_id' => $package->id,
            'command' => 'glow set {PLAYER_USERNAME} {VARIABLE_GLOW}',
        ]);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['glow' => true],
        ]);
        $this->checkout()->assertSessionHasNoErrors();
        $this->payFor($package);

        $this->assertSame(
            'glow set Steve true',
            CommandQueue::where('tag', 'store')->first()->parsed_command
        );
    }
}
