<?php

use App\Enums\StoreOrderStatus;
use App\Models\StoreOrder;
use App\Models\StoreReferral;
use App\Models\StoreReferralPayout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
});

test('a referrer sees their own three figures', function () {
    $referrer = User::factory()->create();
    $referral = StoreReferral::factory()->forUser($referrer)->share(500)->create(['code' => 'KAKAMORA']);

    StoreOrder::factory()->paid()->create([
        'store_referral_id' => $referral->id,
        'referral_earning_base' => 300,
    ]);
    StoreOrder::factory()->completed()->create([
        'store_referral_id' => $referral->id,
        'referral_earning_base' => 200,
    ]);
    StoreReferralPayout::factory()->of(200)->create(['store_referral_id' => $referral->id]);

    $this->actingAs($referrer)
        ->get(route('store.my-referral.show'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Store/MyStoreReferral')
            ->where('referral.code', 'KAKAMORA')
            ->where('referral.owed', 300)
            ->where('referral.orders_count', 2)
            ->has('payouts.data', 1)
            ->where('trackingUrl', route('store.index').'?ref=KAKAMORA')
        );
});

test('the figures agree with what the admin page shows', function () {
    // Two surfaces reading one definition. If they ever diverged, a referrer would be told they are
    // owed one thing while staff are told another.
    $referrer = User::factory()->create();
    $referral = StoreReferral::factory()->forUser($referrer)->create();

    StoreOrder::factory()->paid()->create([
        'store_referral_id' => $referral->id,
        'referral_earning_base' => 750,
    ]);
    StoreReferralPayout::factory()->of(250)->create(['store_referral_id' => $referral->id]);

    $mine = $this->actingAs($referrer)
        ->get(route('store.my-referral.show'))
        ->viewData('page')['props']['referral']['owed'];

    $theirs = $this->actingAs(User::whereId(1)->first())
        ->get(route('admin.store.referral.show', $referral->id))
        ->viewData('page')['props']['referral']['owed'];

    expect($mine)->toBe(500);
    expect($theirs)->toBe($mine);
});

test('another member cannot reach it', function () {
    $referrer = User::factory()->create();
    StoreReferral::factory()->forUser($referrer)->create();

    // 404 rather than 403: a member with no code should not be told one exists to be refused.
    $this->actingAs(User::factory()->create())
        ->get(route('store.my-referral.show'))
        ->assertStatus(404);
});

test('a code with no member attached belongs to nobody', function () {
    StoreReferral::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('store.my-referral.show'))
        ->assertStatus(404);
});

test('a guest is sent to log in', function () {
    $this->get(route('store.my-referral.show'))->assertStatus(302);
});

test('the module toggle closes the page', function () {
    config(['store.enabled' => false]);

    $referrer = User::factory()->create();
    StoreReferral::factory()->forUser($referrer)->create();

    $this->actingAs($referrer)->get(route('store.my-referral.show'))->assertStatus(403);
});

test('refunded orders are excluded from what the referrer is told they earned', function () {
    $referrer = User::factory()->create();
    $referral = StoreReferral::factory()->forUser($referrer)->create();

    StoreOrder::factory()->paid()->create([
        'store_referral_id' => $referral->id,
        'referral_earning_base' => 400,
    ]);
    StoreOrder::factory()->create([
        'store_referral_id' => $referral->id,
        'status' => StoreOrderStatus::REFUNDED,
        'referral_earning_base' => 999,
    ]);

    $this->actingAs($referrer)
        ->get(route('store.my-referral.show'))
        ->assertInertia(fn ($page) => $page
            ->where('referral.owed', 400)
            ->where('referral.orders_count', 1)
        );
});

test('the account menu only offers the page to someone who holds a code', function () {
    $referrer = User::factory()->create();
    StoreReferral::factory()->forUser($referrer)->create();
    $other = User::factory()->create();

    $this->actingAs($referrer)->get(route('store.index'))
        ->assertInertia(fn ($page) => $page->where('store.hasReferral', true));

    $this->actingAs($other)->get(route('store.index'))
        ->assertInertia(fn ($page) => $page->where('store.hasReferral', false));
});
