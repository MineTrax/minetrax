<?php

namespace Tests\Feature\Store;

use App\Enums\StoreDiscountType;
use App\Models\StoreCategory;
use App\Models\StoreCoupon;
use App\Models\StoreCurrency;
use App\Models\StoreGiftCard;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StoreSale;
use App\Models\User;
use App\Services\StorePricingService;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorePricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private StorePricingService $pricing;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.enabled' => true]);
        $this->baseCurrency();
        $this->pricing = app(StorePricingService::class);
    }

    private function line(StorePackage $package, int $quantity = 1): array
    {
        return ['package' => $package, 'quantity' => $quantity];
    }

    private function setTax(string $mode, int $rateBp): void
    {
        $settings = app(StoreSettings::class);
        $settings->tax_mode = $mode;
        $settings->tax_rate_bp = $rateBp;
        $settings->save();

        $this->pricing = app(StorePricingService::class);
    }

    public function test_a_simple_basket_totals_correctly()
    {
        $package = StorePackage::factory()->create(['price' => 999]);

        $quote = $this->pricing->quote([$this->line($package, 3)]);

        $this->assertEquals(2997, $quote['subtotal']);
        $this->assertEquals(2997, $quote['total']);
        $this->assertEquals(2997, $quote['amount_due']);
        $this->assertEquals('$29.97', $quote['formatted']['total']);
    }

    public function test_the_pricing_invariant_holds()
    {
        $package = StorePackage::factory()->create(['price' => 1999]);
        $this->setTax('exclusive', 2000);

        $quote = $this->pricing->quote([$this->line($package, 2)]);

        $this->assertSame(
            $quote['subtotal'] - $quote['coupon_discount'] + $quote['tax_amount'],
            $quote['total'],
            'subtotal - coupon + tax must equal total'
        );
        $this->assertSame(
            $quote['total'],
            $quote['gift_card_amount'] + $quote['amount_due'],
            'total must equal gift card coverage plus amount due'
        );
    }

    public function test_a_percentage_sale_is_applied_with_basis_point_precision()
    {
        $package = StorePackage::factory()->create(['price' => 1999]);
        StoreSale::factory()->create([
            'name' => 'Summer', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 2500, 'is_enabled' => true,
        ]);

        $quote = $this->pricing->quote([$this->line($package)]);

        // 25% of 1999 = 499.75, truncated to 499.
        $this->assertEquals(1500, $quote['items'][0]['unit_price']);
        $this->assertEquals('Summer', $quote['items'][0]['sale_name']);
        $this->assertEquals(499, $quote['sale_discount']);
    }

    public function test_sales_never_stack_and_the_largest_saving_wins()
    {
        $package = StorePackage::factory()->create(['price' => 1000]);
        StoreSale::factory()->create(['name' => 'Small', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 1000]);
        StoreSale::factory()->create(['name' => 'Big', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 3000]);

        $quote = $this->pricing->quote([$this->line($package)]);

        $this->assertEquals(700, $quote['items'][0]['unit_price'], 'Only the largest sale applies.');
        $this->assertEquals('Big', $quote['items'][0]['sale_name']);
    }

    public function test_an_expired_or_future_sale_is_ignored()
    {
        $package = StorePackage::factory()->create(['price' => 1000]);
        StoreSale::factory()->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000, 'ends_at' => now()->subDay()]);
        StoreSale::factory()->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000, 'starts_at' => now()->addDay()]);
        StoreSale::factory()->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000, 'is_enabled' => false]);

        $this->assertEquals(1000, $this->pricing->quote([$this->line($package)])['total']);
    }

    public function test_a_scoped_sale_only_touches_its_own_category()
    {
        $category = StoreCategory::factory()->create();
        $inScope = StorePackage::factory()->create(['price' => 1000, 'store_category_id' => $category->id]);
        $outOfScope = StorePackage::factory()->create(['price' => 1000]);

        $sale = StoreSale::factory()->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000]);
        $sale->saleables()->create(['saleable_type' => StoreCategory::class, 'saleable_id' => $category->id]);

        $quote = $this->pricing->quote([$this->line($inScope), $this->line($outOfScope)]);

        $this->assertEquals(500, $quote['items'][0]['unit_price']);
        $this->assertEquals(1000, $quote['items'][1]['unit_price']);
    }

    public function test_a_percentage_coupon_discounts_the_basket()
    {
        $package = StorePackage::factory()->create(['price' => 2000]);
        $coupon = StoreCoupon::create([
            'code' => 'SAVE10', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 1000, 'is_enabled' => true, 'used_count' => 0,
        ]);

        $quote = $this->pricing->quote([$this->line($package)], null, $coupon);

        $this->assertEquals(200, $quote['coupon_discount']);
        $this->assertEquals(1800, $quote['total']);
        $this->assertEquals('SAVE10', $quote['coupon_code']);
    }

    public function test_a_fixed_coupon_cannot_discount_more_than_the_basket()
    {
        $package = StorePackage::factory()->create(['price' => 500]);
        $coupon = StoreCoupon::create([
            'code' => 'BIG', 'discount_type' => StoreDiscountType::FIXED, 'discount_value' => 100000, 'is_enabled' => true, 'used_count' => 0,
        ]);

        $quote = $this->pricing->quote([$this->line($package)], null, $coupon);

        $this->assertEquals(500, $quote['coupon_discount']);
        $this->assertEquals(0, $quote['total']);
    }

    public function test_an_expired_coupon_is_rejected_with_a_reason()
    {
        $package = StorePackage::factory()->create(['price' => 1000]);
        $coupon = StoreCoupon::create([
            'code' => 'OLD', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000,
            'is_enabled' => true, 'used_count' => 0, 'expires_at' => now()->subDay(),
        ]);

        $quote = $this->pricing->quote([$this->line($package)], null, $coupon);

        $this->assertEquals(0, $quote['coupon_discount']);
        $this->assertNotNull($quote['coupon_error']);
        $this->assertEquals(1000, $quote['total']);
    }

    public function test_a_fully_redeemed_coupon_is_rejected()
    {
        $package = StorePackage::factory()->create(['price' => 1000]);
        $coupon = StoreCoupon::create([
            'code' => 'GONE', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000,
            'is_enabled' => true, 'max_uses_total' => 5, 'used_count' => 5,
        ]);

        $quote = $this->pricing->quote([$this->line($package)], null, $coupon);

        $this->assertEquals(0, $quote['coupon_discount']);
        $this->assertNotNull($quote['coupon_error']);
    }

    public function test_a_minimum_basket_coupon_is_rejected_below_the_threshold()
    {
        $package = StorePackage::factory()->create(['price' => 500]);
        $coupon = StoreCoupon::create([
            'code' => 'MIN', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000,
            'is_enabled' => true, 'used_count' => 0, 'min_basket_amount' => 2000,
        ]);

        $this->assertEquals(0, $this->pricing->quote([$this->line($package)], null, $coupon)['coupon_discount']);
    }

    public function test_a_per_user_coupon_counts_only_paid_orders()
    {
        $user = User::factory()->create();
        $package = StorePackage::factory()->create(['price' => 1000]);
        $coupon = StoreCoupon::create([
            'code' => 'ONCE', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000,
            'is_enabled' => true, 'used_count' => 0, 'max_uses_per_user' => 1,
        ]);

        // An abandoned order must not burn the user's allowance.
        StoreOrder::factory()->create(['user_id' => $user->id, 'store_coupon_id' => $coupon->id]);
        $this->assertEquals(500, $this->pricing->quote([$this->line($package)], null, $coupon, null, $user)['coupon_discount']);

        StoreOrder::factory()->paid()->create(['user_id' => $user->id, 'store_coupon_id' => $coupon->id]);
        $this->assertEquals(0, $this->pricing->quote([$this->line($package)], null, $coupon->fresh(), null, $user)['coupon_discount']);
    }

    public function test_a_scoped_coupon_only_discounts_its_own_packages()
    {
        $inScope = StorePackage::factory()->create(['price' => 1000]);
        $outOfScope = StorePackage::factory()->create(['price' => 1000]);

        $coupon = StoreCoupon::create([
            'code' => 'SCOPED', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000, 'is_enabled' => true, 'used_count' => 0,
        ]);
        $coupon->couponables()->create(['couponable_type' => StorePackage::class, 'couponable_id' => $inScope->id]);

        $quote = $this->pricing->quote([$this->line($inScope), $this->line($outOfScope)], null, $coupon->fresh());

        // 50% of the 1000 in-scope line only.
        $this->assertEquals(500, $quote['coupon_discount']);
    }

    public function test_a_coupon_matching_nothing_in_the_cart_reports_an_error()
    {
        $package = StorePackage::factory()->create(['price' => 1000]);
        $other = StorePackage::factory()->create();

        $coupon = StoreCoupon::create([
            'code' => 'NOPE', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000, 'is_enabled' => true, 'used_count' => 0,
        ]);
        $coupon->couponables()->create(['couponable_type' => StorePackage::class, 'couponable_id' => $other->id]);

        $quote = $this->pricing->quote([$this->line($package)], null, $coupon->fresh());

        $this->assertEquals(0, $quote['coupon_discount']);
        $this->assertNotNull($quote['coupon_error']);
    }

    public function test_exclusive_tax_is_added_on_top()
    {
        $this->setTax('exclusive', 2000);
        $package = StorePackage::factory()->create(['price' => 1000]);

        $quote = $this->pricing->quote([$this->line($package)]);

        $this->assertEquals(200, $quote['tax_amount']);
        $this->assertEquals(1200, $quote['total']);
    }

    public function test_inclusive_tax_is_extracted_and_leaves_the_total_unchanged()
    {
        $this->setTax('inclusive', 2000);
        $package = StorePackage::factory()->create(['price' => 1200]);

        $quote = $this->pricing->quote([$this->line($package)]);

        // 1200 contains 200 of tax at 20%; the buyer still pays 1200.
        $this->assertEquals(200, $quote['tax_amount']);
        $this->assertEquals(1200, $quote['total']);
    }

    public function test_tax_is_charged_after_the_coupon_not_before()
    {
        $this->setTax('exclusive', 2000);
        $package = StorePackage::factory()->create(['price' => 2000]);
        $coupon = StoreCoupon::create([
            'code' => 'HALF', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000, 'is_enabled' => true, 'used_count' => 0,
        ]);

        $quote = $this->pricing->quote([$this->line($package)], null, $coupon);

        $this->assertEquals(200, $quote['tax_amount'], 'Tax applies to the discounted 1000, not the original 2000.');
        $this->assertEquals(1200, $quote['total']);
    }

    public function test_no_tax_mode_charges_nothing()
    {
        $this->setTax('none', 2000);
        $package = StorePackage::factory()->create(['price' => 1000]);

        $this->assertEquals(0, $this->pricing->quote([$this->line($package)])['tax_amount']);
    }

    public function test_a_gift_card_covers_part_of_the_total()
    {
        $package = StorePackage::factory()->create(['price' => 2000]);
        $giftCard = StoreGiftCard::create([
            'code' => 'GC1', 'currency_code' => 'USD', 'original_balance' => 500, 'balance' => 500, 'is_enabled' => true,
        ]);

        $quote = $this->pricing->quote([$this->line($package)], null, null, $giftCard);

        $this->assertEquals(500, $quote['gift_card_amount']);
        $this->assertEquals(1500, $quote['amount_due']);
        $this->assertEquals(2000, $quote['total']);
    }

    public function test_a_gift_card_larger_than_the_total_only_covers_the_total()
    {
        $package = StorePackage::factory()->create(['price' => 500]);
        $giftCard = StoreGiftCard::create([
            'code' => 'GC2', 'currency_code' => 'USD', 'original_balance' => 10000, 'balance' => 10000, 'is_enabled' => true,
        ]);

        $quote = $this->pricing->quote([$this->line($package)], null, null, $giftCard);

        $this->assertEquals(500, $quote['gift_card_amount']);
        $this->assertEquals(0, $quote['amount_due'], 'A fully covered order skips the gateway entirely.');
    }

    public function test_an_expired_gift_card_covers_nothing()
    {
        $package = StorePackage::factory()->create(['price' => 2000]);
        $giftCard = StoreGiftCard::create([
            'code' => 'GC3', 'currency_code' => 'USD', 'original_balance' => 500, 'balance' => 500,
            'is_enabled' => true, 'expires_at' => now()->subDay(),
        ]);

        $this->assertEquals(0, $this->pricing->quote([$this->line($package)], null, null, $giftCard)['gift_card_amount']);
    }

    public function test_quoting_in_a_second_currency_converts_and_records_the_base_total()
    {
        $yen = StoreCurrency::factory()->zeroDecimal()->create(); // 150 JPY per USD
        $package = StorePackage::factory()->create(['price' => 1000]); // $10.00

        $quote = $this->pricing->quote([$this->line($package, 2)], $yen);

        $this->assertEquals('JPY', $quote['currency']);
        $this->assertEquals(3000, $quote['total'], '2 x ¥1500');
        $this->assertEquals('¥3,000', $quote['formatted']['total']);
        // Reporting still needs the base figure, snapshotted at this rate.
        $this->assertEquals(2000, $quote['base_total']);
        $this->assertEquals('USD', $quote['base_currency']);
    }

    public function test_the_invariant_still_holds_in_a_zero_decimal_currency_with_tax_and_a_coupon()
    {
        $this->setTax('exclusive', 1000);
        $yen = StoreCurrency::factory()->zeroDecimal()->create();
        $package = StorePackage::factory()->create(['price' => 1000]);
        $coupon = StoreCoupon::create([
            'code' => 'TEN', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 1000, 'is_enabled' => true, 'used_count' => 0,
        ]);

        $quote = $this->pricing->quote([$this->line($package, 3)], $yen, $coupon);

        $this->assertSame(
            $quote['subtotal'] - $quote['coupon_discount'] + $quote['tax_amount'],
            $quote['total']
        );
        $this->assertSame($quote['total'], $quote['gift_card_amount'] + $quote['amount_due']);
    }
}
