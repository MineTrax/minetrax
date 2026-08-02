<?php

use App\Enums\StoreDiscountType;
use App\Models\StoreCategory;
use App\Models\StoreCoupon;
use App\Models\StoreCurrency;
use App\Models\StoreGiftCard;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StoreSale;
use App\Models\StoreTax;
use App\Models\User;
use App\Services\StorePricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
    $this->pricing = app(StorePricingService::class);
});

function line(StorePackage $package, int $quantity = 1): array
{
    return ['package' => $package, 'quantity' => $quantity];
}

/**
 * The global tax rule, which is what a store with one flat rate now has instead of a setting.
 */
function setTax(string $mode, int $rateBp): void
{
    Cache::flush();

    StoreTax::updateOrCreate(
        ['country_id' => null],
        ['name' => 'Tax', 'rate_bp' => $rateBp, 'is_inclusive' => $mode === 'inclusive', 'is_enabled' => true],
    );

    test()->pricing = app(StorePricingService::class);
}

test('a simple basket totals correctly', function () {
    $package = StorePackage::factory()->create(['price' => 999]);

    $quote = $this->pricing->quote([line($package, 3)]);

    expect($quote['subtotal'])->toEqual(2997);
    expect($quote['total'])->toEqual(2997);
    expect($quote['amount_due'])->toEqual(2997);
    expect($quote['formatted']['total'])->toEqual('$29.97');
});

test('the pricing invariant holds', function () {
    $package = StorePackage::factory()->create(['price' => 1999]);
    setTax('exclusive', 2000);

    $quote = $this->pricing->quote([line($package, 2)]);

    expect($quote['total'])->toBe($quote['subtotal'] - $quote['coupon_discount'] + $quote['tax_amount'], 'subtotal - coupon + tax must equal total');
    expect($quote['gift_card_amount'] + $quote['amount_due'])->toBe($quote['total'], 'total must equal gift card coverage plus amount due');
});

test('a percentage sale is applied with basis point precision', function () {
    $package = StorePackage::factory()->create(['price' => 1999]);
    StoreSale::factory()->create([
        'name' => 'Summer', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 2500, 'is_enabled' => true,
    ]);

    $quote = $this->pricing->quote([line($package)]);

    // 25% of 1999 = 499.75, truncated to 499.
    expect($quote['items'][0]['unit_price'])->toEqual(1500);
    expect($quote['items'][0]['sale_name'])->toEqual('Summer');
    expect($quote['sale_discount'])->toEqual(499);
});

test('sales never stack and the largest saving wins', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreSale::factory()->create(['name' => 'Small', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 1000]);
    StoreSale::factory()->create(['name' => 'Big', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 3000]);

    $quote = $this->pricing->quote([line($package)]);

    expect($quote['items'][0]['unit_price'])->toEqual(700, 'Only the largest sale applies.');
    expect($quote['items'][0]['sale_name'])->toEqual('Big');
});

test('an expired or future sale is ignored', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreSale::factory()->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000, 'ends_at' => now()->subDay()]);
    StoreSale::factory()->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000, 'starts_at' => now()->addDay()]);
    StoreSale::factory()->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000, 'is_enabled' => false]);

    expect($this->pricing->quote([line($package)])['total'])->toEqual(1000);
});

test('a scoped sale only touches its own category', function () {
    $category = StoreCategory::factory()->create();
    $inScope = StorePackage::factory()->create(['price' => 1000, 'store_category_id' => $category->id]);
    $outOfScope = StorePackage::factory()->create(['price' => 1000]);

    $sale = StoreSale::factory()->forCategories()->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000]);
    $sale->saleables()->create(['saleable_type' => StoreCategory::class, 'saleable_id' => $category->id]);

    $quote = $this->pricing->quote([line($inScope), line($outOfScope)]);

    expect($quote['items'][0]['unit_price'])->toEqual(500);
    expect($quote['items'][1]['unit_price'])->toEqual(1000);
});

test('a scoped sale with nothing named discounts nothing', function () {
    // The old behaviour read an empty scope as store-wide, so a package-scoped sale whose rows had
    // been cleared quietly discounted the entire catalogue.
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreSale::factory()->forPackages()->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000]);

    $quote = $this->pricing->quote([line($package)]);

    expect($quote['items'][0]['unit_price'])->toEqual(1000);
    expect($quote['items'][0]['sale_name'])->toBeNull();
});

test('a category scoped sale ignores stray package rows', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->forCategories()->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000]);
    $sale->saleables()->create(['saleable_type' => StorePackage::class, 'saleable_id' => $package->id]);

    expect($this->pricing->quote([line($package)])['items'][0]['unit_price'])->toEqual(1000);
});

test('the quote reports which sale won', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreSale::factory()->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 1000]);
    $bigger = StoreSale::factory()->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000]);

    $quote = $this->pricing->quote([line($package)]);

    expect($quote['items'][0]['sale_id'])->toBe($bigger->id);
});

test('a pay what you want line reports no sale id', function () {
    $package = StorePackage::factory()->create(['price' => 1000, 'is_pay_what_you_want' => true]);
    StoreSale::factory()->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000]);

    $quote = $this->pricing->quote([line($package)]);

    expect($quote['items'][0]['sale_id'])->toBeNull();
    expect($quote['items'][0]['unit_price'])->toEqual(1000);
});

test('a sale below its minimum does not apply', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreSale::factory()->withMinimum(2000)->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 2000]);

    $quote = $this->pricing->quote([line($package)]);

    expect($quote['items'][0]['unit_price'])->toEqual(1000);
    expect($quote['items'][0]['sale_name'])->toBeNull();
    expect($quote['sale_discount'])->toEqual(0);
});

test('a sale applies once the cart reaches its minimum', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreSale::factory()->withMinimum(2000)->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 2000]);

    $quote = $this->pricing->quote([line($package, 2)]);

    expect($quote['items'][0]['unit_price'])->toEqual(800);
    expect($quote['sale_discount'])->toEqual(400);
});

test('the minimum is measured before the sale itself', function () {
    // The whole design in one assertion. The cart qualifies at 2000, the sale halves it to 1000,
    // and the sale still stands — measuring the threshold against the discounted subtotal would
    // withdraw it, which would put the cart back over, which would reapply it, forever.
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreSale::factory()->withMinimum(2000)->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000]);

    $quote = $this->pricing->quote([line($package, 2)]);

    expect($quote['qualifying_subtotal'])->toEqual(2000);
    expect($quote['subtotal'])->toEqual(1000);
    expect($quote['items'][0]['sale_name'])->not->toBeNull();
});

test('the minimum reads the price after a package own discount', function () {
    // A threshold is a spend commitment, so it is measured against what the buyer actually commits,
    // not against a list price they were never going to pay.
    $package = StorePackage::factory()->create(['price' => 1000, 'discount_bp' => 5000]);
    StoreSale::factory()->withMinimum(1500)->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 2000]);

    $quote = $this->pricing->quote([line($package, 2)]);

    expect($quote['qualifying_subtotal'])->toEqual(1000);
    expect($quote['items'][0]['sale_name'])->toBeNull();
});

test('a pay what you want line counts toward a sale minimum', function () {
    $donation = StorePackage::factory()->create(['price' => 100, 'is_pay_what_you_want' => true]);
    $rank = StorePackage::factory()->create(['price' => 1000]);
    StoreSale::factory()->withMinimum(5000)->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 2000]);

    $quote = $this->pricing->quote([
        ['package' => $donation, 'quantity' => 1, 'custom_price' => 5000, 'custom_price_currency' => 'USD'],
        line($rank),
    ]);

    expect($quote['qualifying_subtotal'])->toEqual(6000);
    // The donation unlocks the sale but never receives it.
    expect($quote['items'][0]['unit_price'])->toEqual(5000);
    expect($quote['items'][1]['unit_price'])->toEqual(800);
});

test('a sale with no minimum is unaffected', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreSale::factory()->create(['discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 2000]);

    expect($this->pricing->quote([line($package)])['items'][0]['unit_price'])->toEqual(800);
});

test('the quote reports what is still needed to unlock a sale', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreSale::factory()->withMinimum(2500)->create([
        'name' => 'Big Spender',
        'discount_type' => StoreDiscountType::PERCENT,
        'discount_value' => 2000,
    ]);

    $quote = $this->pricing->quote([line($package)]);

    expect($quote['unlockable_sales'])->toHaveCount(1);
    expect($quote['unlockable_sales'][0]['name'])->toEqual('Big Spender');
    expect($quote['unlockable_sales'][0]['remaining'])->toEqual(1500);

    // Once met it stops being something to unlock.
    expect($this->pricing->quote([line($package, 3)])['unlockable_sales'])->toBeEmpty();
});

test('a sale covering nothing in the cart is not offered as unlockable', function () {
    $inCart = StorePackage::factory()->create(['price' => 1000]);
    $elsewhere = StorePackage::factory()->create(['price' => 1000]);

    $sale = StoreSale::factory()->forPackages()->withMinimum(9000)->create();
    $sale->saleables()->create(['saleable_type' => StorePackage::class, 'saleable_id' => $elsewhere->id]);

    expect($this->pricing->quote([line($inCart)])['unlockable_sales'])->toBeEmpty();
});

test('a percentage coupon discounts the basket', function () {
    $package = StorePackage::factory()->create(['price' => 2000]);
    $coupon = StoreCoupon::create([
        'code' => 'SAVE10', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 1000, 'is_enabled' => true, 'used_count' => 0,
    ]);

    $quote = $this->pricing->quote([line($package)], null, $coupon);

    expect($quote['coupon_discount'])->toEqual(200);
    expect($quote['total'])->toEqual(1800);
    expect($quote['coupon_code'])->toEqual('SAVE10');
});

test('a fixed coupon cannot discount more than the basket', function () {
    $package = StorePackage::factory()->create(['price' => 500]);
    $coupon = StoreCoupon::create([
        'code' => 'BIG', 'discount_type' => StoreDiscountType::FIXED, 'discount_value' => 100000, 'is_enabled' => true, 'used_count' => 0,
    ]);

    $quote = $this->pricing->quote([line($package)], null, $coupon);

    expect($quote['coupon_discount'])->toEqual(500);
    expect($quote['total'])->toEqual(0);
});

test('an expired coupon is rejected with a reason', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    $coupon = StoreCoupon::create([
        'code' => 'OLD', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000,
        'is_enabled' => true, 'used_count' => 0, 'expires_at' => now()->subDay(),
    ]);

    $quote = $this->pricing->quote([line($package)], null, $coupon);

    expect($quote['coupon_discount'])->toEqual(0);
    expect($quote['coupon_error'])->not->toBeNull();
    expect($quote['total'])->toEqual(1000);
});

test('a fully redeemed coupon is rejected', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    $coupon = StoreCoupon::create([
        'code' => 'GONE', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000,
        'is_enabled' => true, 'max_uses_total' => 5, 'used_count' => 5,
    ]);

    $quote = $this->pricing->quote([line($package)], null, $coupon);

    expect($quote['coupon_discount'])->toEqual(0);
    expect($quote['coupon_error'])->not->toBeNull();
});

test('a minimum basket coupon is rejected below the threshold', function () {
    $package = StorePackage::factory()->create(['price' => 500]);
    $coupon = StoreCoupon::create([
        'code' => 'MIN', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000,
        'is_enabled' => true, 'used_count' => 0, 'min_basket_amount' => 2000,
    ]);

    expect($this->pricing->quote([line($package)], null, $coupon)['coupon_discount'])->toEqual(0);
});

test('a per user coupon counts only paid orders', function () {
    $user = User::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $coupon = StoreCoupon::create([
        'code' => 'ONCE', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000,
        'is_enabled' => true, 'used_count' => 0, 'max_uses_per_user' => 1,
    ]);

    // An abandoned order must not burn the user's allowance.
    StoreOrder::factory()->create(['user_id' => $user->id, 'store_coupon_id' => $coupon->id]);
    expect($this->pricing->quote([line($package)], null, $coupon, null, $user)['coupon_discount'])->toEqual(500);

    StoreOrder::factory()->paid()->create(['user_id' => $user->id, 'store_coupon_id' => $coupon->id]);
    expect($this->pricing->quote([line($package)], null, $coupon->fresh(), null, $user)['coupon_discount'])->toEqual(0);
});

test('a scoped coupon only discounts its own packages', function () {
    $inScope = StorePackage::factory()->create(['price' => 1000]);
    $outOfScope = StorePackage::factory()->create(['price' => 1000]);

    $coupon = StoreCoupon::create([
        'code' => 'SCOPED', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000, 'is_enabled' => true, 'used_count' => 0,
    ]);
    $coupon->couponables()->create(['couponable_type' => StorePackage::class, 'couponable_id' => $inScope->id]);

    $quote = $this->pricing->quote([line($inScope), line($outOfScope)], null, $coupon->fresh());

    // 50% of the 1000 in-scope line only.
    expect($quote['coupon_discount'])->toEqual(500);
});

test('a coupon matching nothing in the cart reports an error', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    $other = StorePackage::factory()->create();

    $coupon = StoreCoupon::create([
        'code' => 'NOPE', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000, 'is_enabled' => true, 'used_count' => 0,
    ]);
    $coupon->couponables()->create(['couponable_type' => StorePackage::class, 'couponable_id' => $other->id]);

    $quote = $this->pricing->quote([line($package)], null, $coupon->fresh());

    expect($quote['coupon_discount'])->toEqual(0);
    expect($quote['coupon_error'])->not->toBeNull();
});

test('exclusive tax is added on top', function () {
    setTax('exclusive', 2000);
    $package = StorePackage::factory()->create(['price' => 1000]);

    $quote = $this->pricing->quote([line($package)]);

    expect($quote['tax_amount'])->toEqual(200);
    expect($quote['total'])->toEqual(1200);
});

test('inclusive tax is extracted and leaves the total unchanged', function () {
    setTax('inclusive', 2000);
    $package = StorePackage::factory()->create(['price' => 1200]);

    $quote = $this->pricing->quote([line($package)]);

    // 1200 contains 200 of tax at 20%; the buyer still pays 1200.
    expect($quote['tax_amount'])->toEqual(200);
    expect($quote['total'])->toEqual(1200);
});

test('tax is charged after the coupon not before', function () {
    setTax('exclusive', 2000);
    $package = StorePackage::factory()->create(['price' => 2000]);
    $coupon = StoreCoupon::create([
        'code' => 'HALF', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000, 'is_enabled' => true, 'used_count' => 0,
    ]);

    $quote = $this->pricing->quote([line($package)], null, $coupon);

    expect($quote['tax_amount'])->toEqual(200, 'Tax applies to the discounted 1000, not the original 2000.');
    expect($quote['total'])->toEqual(1200);
});

test('a store with no tax rule charges nothing', function () {
    // There is no "none" mode any more: a store that owes no tax simply has no rule.
    $package = StorePackage::factory()->create(['price' => 1000]);

    expect($this->pricing->quote([line($package)])['tax_amount'])->toEqual(0);
});

test('a gift card covers part of the total', function () {
    $package = StorePackage::factory()->create(['price' => 2000]);
    $giftCard = StoreGiftCard::create([
        'code' => 'GC1', 'currency_code' => 'USD', 'original_balance' => 500, 'balance' => 500, 'is_enabled' => true,
    ]);

    $quote = $this->pricing->quote([line($package)], null, null, $giftCard);

    expect($quote['gift_card_amount'])->toEqual(500);
    expect($quote['amount_due'])->toEqual(1500);
    expect($quote['total'])->toEqual(2000);
});

test('a gift card larger than the total only covers the total', function () {
    $package = StorePackage::factory()->create(['price' => 500]);
    $giftCard = StoreGiftCard::create([
        'code' => 'GC2', 'currency_code' => 'USD', 'original_balance' => 10000, 'balance' => 10000, 'is_enabled' => true,
    ]);

    $quote = $this->pricing->quote([line($package)], null, null, $giftCard);

    expect($quote['gift_card_amount'])->toEqual(500);
    expect($quote['amount_due'])->toEqual(0, 'A fully covered order skips the gateway entirely.');
});

test('an expired gift card covers nothing', function () {
    $package = StorePackage::factory()->create(['price' => 2000]);
    $giftCard = StoreGiftCard::create([
        'code' => 'GC3', 'currency_code' => 'USD', 'original_balance' => 500, 'balance' => 500,
        'is_enabled' => true, 'expires_at' => now()->subDay(),
    ]);

    expect($this->pricing->quote([line($package)], null, null, $giftCard)['gift_card_amount'])->toEqual(0);
});

test('quoting in a second currency converts and records the base total', function () {
    $yen = StoreCurrency::factory()->zeroDecimal()->create();
    // 150 JPY per USD
    $package = StorePackage::factory()->create(['price' => 1000]);

    // $10.00
    $quote = $this->pricing->quote([line($package, 2)], $yen);

    expect($quote['currency'])->toEqual('JPY');
    expect($quote['total'])->toEqual(3000, '2 x ¥1500');
    expect($quote['formatted']['total'])->toEqual('¥3,000');

    // Reporting still needs the base figure, snapshotted at this rate.
    expect($quote['base_total'])->toEqual(2000);
    expect($quote['base_currency'])->toEqual('USD');
});

test('the invariant still holds in a zero decimal currency with tax and a coupon', function () {
    setTax('exclusive', 1000);
    $yen = StoreCurrency::factory()->zeroDecimal()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $coupon = StoreCoupon::create([
        'code' => 'TEN', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 1000, 'is_enabled' => true, 'used_count' => 0,
    ]);

    $quote = $this->pricing->quote([line($package, 3)], $yen, $coupon);

    expect($quote['total'])->toBe($quote['subtotal'] - $quote['coupon_discount'] + $quote['tax_amount']);
    expect($quote['gift_card_amount'] + $quote['amount_due'])->toBe($quote['total']);
});
