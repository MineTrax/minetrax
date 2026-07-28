<?php

namespace Tests\Feature\Store;

use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageCommandTrigger;
use App\Models\Server;
use App\Models\StoreBan;
use App\Models\StoreCategory;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use App\Models\StorePackage;
use App\Models\StorePackageCommand;
use App\Models\StorePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Validates the Store schema, models, enums and factories hang together: tables exist, factories
 * satisfy every NOT NULL constraint, relations resolve, enums serialise the way the frontend
 * expects, and the order state machine only permits legal transitions.
 */
class StoreSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_store_table_exists()
    {
        $tables = [
            'store_categories', 'store_packages', 'store_package_command_server', 'store_package_commands',
            'store_currencies', 'store_package_prices',
            'store_carts', 'store_cart_items',
            'store_coupons', 'store_couponables', 'store_sales', 'store_saleables', 'store_gift_cards',
            'store_orders', 'store_order_items', 'store_package_grants', 'store_order_deliveries',
            'store_gift_card_transactions',
            'store_payments', 'store_payment_refunds', 'store_gateway_webhooks',
            'store_bans',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table [{$table}].");
        }
    }

    public function test_no_store_migration_uses_a_database_enum_column()
    {
        // Enumerated columns are plain strings cast to PHP backed enums. MySQL enum columns
        // require an ALTER to add a value, which makes shipping a new order status painful.
        foreach (glob(database_path('migrations/*create_store_*')) as $migration) {
            $this->assertStringNotContainsString(
                '->enum(',
                file_get_contents($migration),
                'Store migrations must not use $table->enum(): '.basename($migration)
            );
        }
    }

    public function test_catalog_factories_create_valid_records()
    {
        $category = StoreCategory::factory()->create();
        $package = StorePackage::factory()->create(['store_category_id' => $category->id]);

        $this->assertDatabaseHas('store_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('store_packages', ['id' => $package->id]);
        $this->assertEquals($category->id, $package->category->id);
        $this->assertTrue($category->packages->contains($package));
    }

    public function test_package_states_apply()
    {
        $this->assertEquals(30, StorePackage::factory()->expiring()->create()->expiry_duration_days);
        $this->assertFalse(StorePackage::factory()->disabled()->create()->is_enabled);
        $this->assertFalse(StorePackage::factory()->hidden()->create()->is_visible);
    }

    public function test_package_commands_can_be_filtered_by_trigger()
    {
        $package = StorePackage::factory()->create();
        StorePackageCommand::factory()->count(2)->create(['store_package_id' => $package->id]);
        StorePackageCommand::factory()->expiry()->create(['store_package_id' => $package->id]);

        $this->assertCount(3, $package->commands);
        $this->assertCount(2, $package->commandsForTrigger(StorePackageCommandTrigger::PURCHASE)->get());
        $this->assertCount(1, $package->commandsForTrigger(StorePackageCommandTrigger::EXPIRY)->get());
    }

    public function test_a_command_can_target_specific_servers()
    {
        // Servers hang off the command, not the package, so two commands on one package can go
        // to different places.
        $command = StorePackageCommand::factory()->create(['is_run_on_all_servers' => false]);
        $server = Server::factory()->create();

        $command->servers()->attach($server);

        $this->assertTrue($command->fresh()->servers->contains($server));
        $this->assertFalse($command->fresh()->is_run_on_all_servers);
    }

    public function test_order_factory_creates_a_valid_order_with_an_auto_generated_uuid()
    {
        $order = StoreOrder::factory()->create();

        $this->assertTrue(Str::isUuid($order->uuid));
        $this->assertEquals('uuid', $order->getRouteKeyName());
        $this->assertEquals(StoreOrderStatus::PENDING, $order->status);
    }

    public function test_order_relations_resolve()
    {
        $user = User::factory()->create();
        $order = StoreOrder::factory()->forUser($user)->create();
        $item = StoreOrderItem::factory()->create(['store_order_id' => $order->id]);
        $payment = StorePayment::factory()->create(['store_order_id' => $order->id]);

        $order->refresh();

        $this->assertEquals($user->id, $order->user->id);
        $this->assertTrue($order->items->contains($item));
        $this->assertTrue($order->payments->contains($payment));
        $this->assertEquals($order->id, $item->order->id);
    }

    public function test_order_factory_states_apply()
    {
        $this->assertEquals(StoreOrderStatus::PAID, StoreOrder::factory()->paid()->create()->status);

        $completed = StoreOrder::factory()->completed()->create();
        $this->assertEquals(StoreOrderStatus::COMPLETED, $completed->status);
        $this->assertNotNull($completed->completed_at);

        $this->assertNull(StoreOrder::factory()->guest()->create()->user_id);
    }

    public function test_order_totals_are_stored_as_integer_minor_units()
    {
        $order = StoreOrder::factory()->create(['total' => 1999, 'amount_due' => 1999]);

        $this->assertIsInt($order->fresh()->total);
        $this->assertSame(1999, $order->fresh()->total);
    }

    public function test_order_status_enum_serialises_as_key_value_for_the_frontend()
    {
        // BaseModel::attributesToArray turns HasKeyValueSerialization enums into {key, value},
        // which is what every Vue page in this codebase reads.
        $order = StoreOrder::factory()->create();
        $array = $order->toArray();

        $this->assertIsArray($array['status']);
        $this->assertEquals('PENDING', $array['status']['key']);
        $this->assertEquals('pending', $array['status']['value']);
    }

    public function test_order_state_machine_permits_only_legal_transitions()
    {
        $this->assertTrue(StoreOrderStatus::PENDING->canTransitionTo(StoreOrderStatus::PAID));
        $this->assertTrue(StoreOrderStatus::PENDING->canTransitionTo(StoreOrderStatus::CANCELLED));
        $this->assertTrue(StoreOrderStatus::PAID->canTransitionTo(StoreOrderStatus::COMPLETED));
        $this->assertTrue(StoreOrderStatus::COMPLETED->canTransitionTo(StoreOrderStatus::REFUNDED));
        $this->assertTrue(StoreOrderStatus::COMPLETED->canTransitionTo(StoreOrderStatus::CHARGEBACK));
        $this->assertTrue(StoreOrderStatus::PARTIALLY_REFUNDED->canTransitionTo(StoreOrderStatus::REFUNDED));

        // An out-of-order webhook must never be able to skip or reverse the lifecycle.
        $this->assertFalse(StoreOrderStatus::PENDING->canTransitionTo(StoreOrderStatus::COMPLETED));
        $this->assertFalse(StoreOrderStatus::PENDING->canTransitionTo(StoreOrderStatus::REFUNDED));
        $this->assertFalse(StoreOrderStatus::COMPLETED->canTransitionTo(StoreOrderStatus::PENDING));
        $this->assertFalse(StoreOrderStatus::REFUNDED->canTransitionTo(StoreOrderStatus::COMPLETED));
        $this->assertFalse(StoreOrderStatus::CANCELLED->canTransitionTo(StoreOrderStatus::PAID));
    }

    public function test_terminal_and_paid_states_are_classified_correctly()
    {
        $this->assertTrue(StoreOrderStatus::REFUNDED->isTerminal());
        $this->assertTrue(StoreOrderStatus::CANCELLED->isTerminal());
        $this->assertTrue(StoreOrderStatus::CHARGEBACK->isTerminal());
        $this->assertFalse(StoreOrderStatus::PENDING->isTerminal());

        // Purchase limits count paid states only, so a cancelled order restocks automatically.
        $this->assertTrue(StoreOrderStatus::PAID->isPaidState());
        $this->assertTrue(StoreOrderStatus::COMPLETED->isPaidState());
        $this->assertFalse(StoreOrderStatus::PENDING->isPaidState());
        $this->assertFalse(StoreOrderStatus::CANCELLED->isPaidState());

        $this->assertTrue(StoreOrderStatus::REFUNDED->isRevoking());
        $this->assertTrue(StoreOrderStatus::CHARGEBACK->isRevoking());
        $this->assertFalse(StoreOrderStatus::PARTIALLY_REFUNDED->isRevoking());
    }

    public function test_currency_factory_covers_non_two_decimal_currencies()
    {
        $base = $this->baseCurrency();
        $yen = StoreCurrency::factory()->zeroDecimal()->create();
        $dinar = StoreCurrency::factory()->threeDecimal()->create();

        $this->assertTrue($base->is_base);
        $this->assertEquals(2, $base->exponent);
        $this->assertEquals(0, $yen->exponent, 'JPY has no minor unit.');
        $this->assertEquals(3, $dinar->exponent, 'KWD has three minor-unit digits.');

        $this->assertEquals($base->id, StoreCurrency::base()->id);
        $this->assertCount(3, StoreCurrency::enabled()->get());
    }

    public function test_delivery_table_has_no_status_column()
    {
        // Delivery health is read through the joined command_queues row so there is exactly one
        // source of truth. A status column here would inevitably drift out of sync.
        $this->assertFalse(Schema::hasColumn('store_order_deliveries', 'status'));
        $this->assertTrue(Schema::hasColumn('store_order_deliveries', 'command_queue_id'));
    }

    public function test_cart_items_cache_no_package_price()
    {
        // Carts are always priced live; a copied package price is a tampering and staleness
        // vector. custom_price is the one exception and is not a copy of anything: it is what the
        // buyer typed for a pay-what-you-want package, so it has nowhere else to live.
        $this->assertFalse(Schema::hasColumn('store_cart_items', 'price'));
        $this->assertFalse(Schema::hasColumn('store_cart_items', 'unit_price'));
        $this->assertTrue(Schema::hasColumn('store_cart_items', 'custom_price'));
        $this->assertTrue(Schema::hasColumn('store_cart_items', 'custom_price_currency'));
    }

    public function test_ban_factory_and_active_scope()
    {
        StoreBan::factory()->create();
        StoreBan::factory()->create(['expires_at' => now()->subDay()]);
        StoreBan::factory()->create(['expires_at' => now()->addDay()]);

        $this->assertCount(3, StoreBan::all());
        $this->assertCount(2, StoreBan::active()->get(), 'An expired ban must not block checkout.');
    }
}
