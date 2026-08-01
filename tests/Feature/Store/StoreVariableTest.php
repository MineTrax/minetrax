<?php

use App\Enums\StorePackageCommandTrigger;
use App\Enums\StoreVariableType;
use App\Jobs\RunCommandQueueJob;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    $this->enableStoreGateways(['manual']);

    $this->withCookie(StoreCartService::COOKIE, 'guest-cart-token');

    $this->withoutMiddleware([
        ThrottleRequests::class,
        ThrottleRequestsWithRedis::class,
    ]);

    // The queue is sync in tests, so a dispatched command would open a real socket to the
    // factory server and block for the 10s webquery timeout. Every assertion here is about
    // the command_queues rows, which are written before dispatch.
    Queue::fake([RunCommandQueueJob::class]);
});

/**
 * @return array<string, mixed>
 */
function variableValidPayload(array $overrides = []): array
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

function packageWithVariable(StoreVariable $variable, array $packageAttributes = []): StorePackage
{
    $package = StorePackage::factory()->create(array_merge(['price' => 1000], $packageAttributes));
    $package->variables()->attach($variable->id, ['sort_order' => 0]);

    return $package->fresh('variables');
}

function variableCheckout(array $overrides = []): TestResponse
{
    if (! Player::where('username', 'Steve')->exists()) {
        Player::factory()->create(['username' => 'Steve']);
    }

    return test()->post(route('store.checkout.store'), array_merge([
        'player_username' => 'Steve',
        'email' => 'buyer@example.com',
        'gateway' => 'manual',
        'accept_terms' => true,
    ], $overrides));
}

test('guest and non staff are denied', function () {
    $this->get(route('admin.store.variable.index'))->assertStatus(302);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.store.variable.index'))->assertStatus(302);
});

test('admin can view the variable listing', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreVariable::factory()->count(2)->create();

    $this->get(route('admin.store.variable.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreVariable/IndexStoreVariable', false)
            ->has('variables.data', 2)
        );
});

test('admin can create a variable', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.variable.store'), variableValidPayload())
        ->assertRedirect(route('admin.store.variable.index'));

    $this->assertDatabaseHas('store_variables', [
        'name' => 'Prefix Color',
        'identifier' => 'prefix_color',
        'type' => 'text',
    ]);
});

test('an identifier is normalised into a usable token', function () {
    // Whatever an admin types, what lands in the column has to be readable back off a
    // {VARIABLE_...} placeholder.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.variable.store'), variableValidPayload([
        'identifier' => 'Prefix Color!',
    ]))->assertSessionHasNoErrors();

    expect(StoreVariable::first()->identifier)->toBe('prefix_color');
});

test('the command placeholder is namespaced', function () {
    $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color']);

    expect($variable->command_placeholder)->toBe('{VARIABLE_PREFIX_COLOR}');
});

test('an identifier must be unique', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreVariable::factory()->create(['identifier' => 'prefix_color']);

    $this->post(route('admin.store.variable.store'), variableValidPayload())
        ->assertSessionHasErrors(['identifier']);
});

test('an identifier that shadows a built in placeholder is rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.variable.store'), variableValidPayload([
        'identifier' => 'player_username',
    ]))->assertSessionHasErrors(['identifier']);
});

test('a dropdown must carry its choices', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.variable.store'), variableValidPayload([
        'type' => StoreVariableType::SELECT->value,
        'options' => null,
    ]))->assertSessionHasErrors(['options']);
});

test('switching away from a dropdown clears its choices', function () {
    $this->actingAs(User::whereId(1)->first());
    $variable = StoreVariable::factory()->select()->create();

    $this->put(route('admin.store.variable.update', $variable->id), variableValidPayload([
        'identifier' => $variable->identifier,
        'type' => StoreVariableType::TEXT->value,
        'options' => 'Red,Green',
    ]))->assertSessionHasNoErrors();

    expect($variable->fresh()->options)->toBeNull();
});

test('deleting a variable leaves placed orders readable', function () {
    $this->actingAs(User::whereId(1)->first());
    $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'name' => 'Prefix Color']);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'Gold'],
    ]);
    variableCheckout()->assertSessionHasNoErrors();

    $this->delete(route('admin.store.variable.delete', $variable->id));

    // The snapshot carries the name, so the order still reads correctly.
    $this->assertDatabaseMissing('store_variables', ['id' => $variable->id]);

    // assertEquals, not assertSame: MySQL normalises the key order of a JSON object, so the
    // round-trip is key-order insensitive by nature.
    expect(StoreOrder::first()->items->first()->variable_values)->toEqual([['identifier' => 'prefix_color', 'name' => 'Prefix Color', 'value' => 'Gold']]);
});

test('admin can attach variables to a package in order', function () {
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

    expect($package->variables->pluck('id')->all())->toEqual([$second->id, $first->id]);
});

test('the package page exposes a formkit schema for its variables', function () {
    $variable = StoreVariable::factory()->select('Gold,Silver')->create([
        'name' => 'Prefix Color',
        'identifier' => 'prefix_color',
    ]);
    $package = packageWithVariable($variable);

    $this->get(route('store.package', $package->slug))
        ->assertOk()
        ->assertInertia(function ($page) {
            $schema = $page->toArray()['props']['variableSchema'];

            expect($schema)->toHaveCount(1);
            expect($schema[0]['type'])->toBe('select');
            expect($schema[0]['name'])->toBe('prefix_color');
            expect($schema[0]['label'])->toBe('Prefix Color');
            expect($schema[0]['options'])->toBe('Gold,Silver');
            expect($schema[0]['validation'])->toBe('required');
        });
});

test('the description reaches the buyer as text not markup', function () {
    // It is authored in a rich text editor, but FormKit renders `help` as text, so the markup
    // would otherwise appear literally under the input.
    $variable = StoreVariable::factory()->create([
        'identifier' => 'prefix_color',
        'description' => '<p>Pick a <strong>colour</strong> &amp; go.</p>',
    ]);
    $package = packageWithVariable($variable);

    $this->get(route('store.package', $package->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('variableSchema.0.help', 'Pick a colour & go.')
        );
});

test('a variable with no description carries no help text', function () {
    $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'description' => null]);
    $package = packageWithVariable($variable);

    $this->get(route('store.package', $package->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('variableSchema.0.help', null));
});

test('a disabled variable is not asked for', function () {
    $package = packageWithVariable(StoreVariable::factory()->disabled()->create());

    $this->get(route('store.package', $package->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('variableSchema', 0));
});

test('a value is stored on the cart line', function () {
    $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color']);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'Gold'],
    ])->assertSessionHasNoErrors();

    expect(StoreCartItem::first()->variable_values)->toBe(['prefix_color' => 'Gold']);
});

test('a required variable cannot be left blank', function () {
    $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'name' => 'Prefix Color']);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1])
        ->assertSessionHasErrors(['variables.prefix_color']);

    $this->assertDatabaseCount('store_cart_items', 0);
});

test('an optional variable may be left blank', function () {
    $variable = StoreVariable::factory()->optional()->create(['identifier' => 'nickname']);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1])
        ->assertSessionHasNoErrors();

    expect(StoreCartItem::first()->variable_values)->toBeNull();
});

test('a dropdown value must be one of the offered choices', function () {
    $variable = StoreVariable::factory()->select('Gold,Silver')->create(['identifier' => 'tier']);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['tier' => 'Diamond'],
    ])->assertSessionHasErrors(['variables.tier']);
});

test('a dropdown value is stored with the admins own spelling', function () {
    // The command receives the canonical spelling rather than whatever casing was posted.
    $variable = StoreVariable::factory()->select('Gold,Silver')->create(['identifier' => 'tier']);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['tier' => 'gOLD'],
    ])->assertSessionHasNoErrors();

    expect(StoreCartItem::first()->variable_values)->toBe(['tier' => 'Gold']);
});

test('a number variable rejects anything that is not a number', function () {
    $variable = StoreVariable::factory()->number()->create(['identifier' => 'amount']);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['amount' => 'lots'],
    ])->assertSessionHasErrors(['variables.amount']);
});

test('a value longer than the maximum is rejected', function () {
    $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'max_length' => 5]);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'far too long'],
    ])->assertSessionHasErrors(['variables.prefix_color']);
});

test('characters that could alter a command are refused', function () {
    $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'max_length' => 64]);
    $package = packageWithVariable($variable);

    foreach (['Gold; op Bob', 'Gold | op Bob', 'Gold {PLAYER_USERNAME}', "Gold\nop Bob", 'Gold`whoami`', 'Gold$USER'] as $attempt) {
        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => $attempt],
        ]);

        // Asserted by hand rather than with assertSessionHasErrors so the failure names the
        // input that got through.
        $errors = session('errors');

        expect($errors !== null && $errors->has('variables.prefix_color'))->toBeTrue("[{$attempt}] should have been refused.");
    }

    $this->assertDatabaseCount('store_cart_items', 0);
});

test('a minecraft colour code is accepted', function () {
    // Refusing `&` would make the feature useless for the thing it exists for.
    $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'max_length' => 64]);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => '&6Gold [VIP]'],
    ])->assertSessionHasNoErrors();
});

test('a value for a variable the package does not have is dropped', function () {
    $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color']);
    $stranger = StoreVariable::factory()->create(['identifier' => 'not_mine']);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id,
        'quantity' => 1,
        'variables' => ['prefix_color' => 'Gold', $stranger->identifier => 'injected'],
    ])->assertSessionHasNoErrors();

    expect(StoreCartItem::first()->variable_values)->toBe(['prefix_color' => 'Gold']);
});

test('re adding a package replaces the answers rather than keeping both', function () {
    $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color']);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'Gold'],
    ]);
    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'Silver'],
    ]);

    $this->assertDatabaseCount('store_cart_items', 1);
    expect(StoreCartItem::first()->variable_values)->toBe(['prefix_color' => 'Silver']);
});

test('the cart shows the answers by name', function () {
    $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'name' => 'Prefix Color']);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'Gold'],
    ]);

    $this->get(route('store.cart.show'))
        ->assertInertia(function ($page) {
            $variables = $page->toArray()['props']['quote']['items'][0]['variables'];

            expect($variables)->toBe([['identifier' => 'prefix_color', 'name' => 'Prefix Color', 'value' => 'Gold']]);
        });
});

test('the answers are snapshotted onto the order item', function () {
    $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color', 'name' => 'Prefix Color']);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'Gold'],
    ]);
    variableCheckout()->assertSessionHasNoErrors();

    expect(StoreOrder::first()->items->first()->variable_values)->toEqual([['identifier' => 'prefix_color', 'name' => 'Prefix Color', 'value' => 'Gold']]);
});

test('a variable made required after the item was carted blocks checkout', function () {
    // The cart row is revalidated at purchase, not trusted.
    $variable = StoreVariable::factory()->optional()->create(['identifier' => 'prefix_color']);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1])
        ->assertSessionHasNoErrors();

    $variable->update(['is_required' => true]);

    variableCheckout()->assertSessionHasErrors(['variables.prefix_color']);
    $this->assertDatabaseCount('store_orders', 0);
});

test('a choice removed after the item was carted blocks checkout', function () {
    $variable = StoreVariable::factory()->select('Gold,Silver')->create(['identifier' => 'tier']);
    $package = packageWithVariable($variable);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['tier' => 'Gold'],
    ])->assertSessionHasNoErrors();

    $variable->update(['options' => 'Silver,Bronze']);

    variableCheckout()->assertSessionHasErrors(['variables.tier']);
});

// --- Delivery -------------------------------------------------------------------------------
function variablePayFor(StorePackage $package): StoreOrder
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

test('a variable value is substituted into the command', function () {
    Server::factory()->create();
    $variable = StoreVariable::factory()->create(['identifier' => 'prefix_color']);
    $package = packageWithVariable($variable);
    StorePackageCommand::factory()->create([
        'store_package_id' => $package->id,
        'trigger' => StorePackageCommandTrigger::PURCHASE,
        'command' => 'lp user {PLAYER_USERNAME} meta setprefix {VARIABLE_PREFIX_COLOR}',
    ]);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['prefix_color' => 'Gold'],
    ]);
    variableCheckout()->assertSessionHasNoErrors();
    variablePayFor($package);

    expect(CommandQueue::where('tag', 'store')->first()->parsed_command)->toBe('lp user Steve meta setprefix Gold');
});

test('a variable cannot overwrite a built in placeholder', function () {
    // The identifier check rejects `player_username` at creation, but the substitution keys are
    // namespaced too, so even a variable created before that rule cannot reach it.
    Server::factory()->create();
    $variable = StoreVariable::factory()->create(['identifier' => 'player_username']);
    $package = packageWithVariable($variable);
    StorePackageCommand::factory()->create([
        'store_package_id' => $package->id,
        'command' => 'say {PLAYER_USERNAME}',
    ]);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['player_username' => 'Impostor'],
    ]);
    variableCheckout()->assertSessionHasNoErrors();
    variablePayFor($package);

    expect(CommandQueue::where('tag', 'store')->first()->parsed_command)->toBe('say Steve');
});

test('an unanswered optional variable leaves its placeholder in place', function () {
    // Nothing to substitute, so the raw placeholder survives rather than the command silently
    // becoming a different one.
    Server::factory()->create();
    $variable = StoreVariable::factory()->optional()->create(['identifier' => 'nickname']);
    $package = packageWithVariable($variable);
    StorePackageCommand::factory()->create([
        'store_package_id' => $package->id,
        'command' => 'say hello {VARIABLE_NICKNAME}',
    ]);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
    variableCheckout()->assertSessionHasNoErrors();
    variablePayFor($package);

    expect(CommandQueue::where('tag', 'store')->first()->parsed_command)->toBe('say hello {VARIABLE_NICKNAME}');
});

test('a checkbox answer substitutes as true or false', function () {
    Server::factory()->create();
    $variable = StoreVariable::factory()->checkbox()->create(['identifier' => 'glow']);
    $package = packageWithVariable($variable);
    StorePackageCommand::factory()->create([
        'store_package_id' => $package->id,
        'command' => 'glow set {PLAYER_USERNAME} {VARIABLE_GLOW}',
    ]);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'variables' => ['glow' => true],
    ]);
    variableCheckout()->assertSessionHasNoErrors();
    variablePayFor($package);

    expect(CommandQueue::where('tag', 'store')->first()->parsed_command)->toBe('glow set Steve true');
});
