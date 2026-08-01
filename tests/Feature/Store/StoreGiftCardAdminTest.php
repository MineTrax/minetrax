<?php

use App\Enums\StoreGiftCardTransactionType;
use App\Models\StoreCurrency;
use App\Models\StoreGiftCard;
use App\Models\StoreOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
});

function giftCardAdminValidPayload(array $overrides = []): array
{
    return array_merge([
        'balance' => 2500,
        'currency_code' => StoreCurrency::firstWhere('is_base', true)->code,
        'username' => null,
        'expires_at' => null,
        'note' => 'Compensation for a failed delivery',
    ], $overrides);
}

test('guest and non staff are denied', function () {
    $this->get(route('admin.store.gift-card.index'))->assertStatus(302);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.store.gift-card.index'))->assertStatus(302);
});

test('staff without the permission are forbidden', function () {
    // Moderator is staff but is granted no store permissions by RoleSeeder.
    $staff = User::factory()->create();
    $staff->assignRole('moderator');

    $this->actingAs($staff)->get(route('admin.store.gift-card.index'))->assertStatus(403);
});

test('superadmin can list gift cards', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreGiftCard::factory()->create();

    $this->get(route('admin.store.gift-card.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreGiftCard/IndexStoreGiftCard')
            ->has('cards.data', 1)
        );
});

test('the index is unavailable when the module is disabled', function () {
    config(['store.enabled' => false]);

    // Superadmin bypasses the policy gate, so a permissioned non-superadmin proves the gate.
    $staff = User::factory()->create();
    $staff->assignRole('admin');

    $this->actingAs($staff)->get(route('admin.store.gift-card.index'))->assertStatus(403);
});

test('a balance is formatted in the cards own currency', function () {
    // 2000 minor units is $20.00, and the listing must never print the raw integer.
    $this->actingAs(User::whereId(1)->first());
    StoreGiftCard::factory()->create(['balance' => 2000, 'original_balance' => 2000]);

    $this->get(route('admin.store.gift-card.index'))
        ->assertInertia(fn ($page) => $page->where('cards.data.0.balance_formatted', '$20.00'));
});

test('admin can issue a gift card', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.gift-card.store'), giftCardAdminValidPayload());

    $card = StoreGiftCard::firstOrFail();
    expect($card->balance)->toBe(2500);
    expect($card->original_balance)->toBe(2500);
    expect($card->is_enabled)->toBeTrue();
    expect($card->created_by)->toBe(1);
});

test('issuing redirects to the card so the code can be passed on', function () {
    // The full code is shown nowhere else, and whoever issued it needs to give it to somebody.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.gift-card.store'), giftCardAdminValidPayload())
        ->assertRedirect(route('admin.store.gift-card.show', StoreGiftCard::firstOrFail()->id));
});

test('issuing writes an issue entry naming who did it', function () {
    // A card with no buyer behind it has to say where it came from.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.gift-card.store'), giftCardAdminValidPayload(['note' => 'Goodwill']));

    $transaction = StoreGiftCard::firstOrFail()->transactions()->firstOrFail();
    expect($transaction->type)->toEqual(StoreGiftCardTransactionType::ISSUE);
    expect($transaction->amount)->toBe(2500);
    expect($transaction->balance_after)->toBe(2500);
    expect($transaction->note)->toBe('Goodwill');
    expect($transaction->created_by)->toBe(1);
});

test('the generated code is unique and readable', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.gift-card.store'), giftCardAdminValidPayload());
    $this->post(route('admin.store.gift-card.store'), giftCardAdminValidPayload());

    $codes = StoreGiftCard::pluck('code');
    expect($codes)->toHaveCount(2);
    expect($codes->unique())->toHaveCount(2);
    // No O/0 or I/1, so a player can read one off a screen: the alphabet excludes them.
    expect($codes->first())->toMatch('/^[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}$/');
});

test('a card can be issued to a named account', function () {
    $this->actingAs(User::whereId(1)->first());
    $recipient = User::factory()->create(['username' => 'compensated']);

    $this->post(route('admin.store.gift-card.store'), giftCardAdminValidPayload(['username' => 'compensated']));

    expect(StoreGiftCard::firstOrFail()->issued_to_user_id)->toBe($recipient->id);
});

test('an unknown recipient is refused', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.gift-card.store'), giftCardAdminValidPayload(['username' => 'nobody-here']))
        ->assertSessionHasErrors('username');

    $this->assertDatabaseCount('store_gift_cards', 0);
});

test('a zero balance is refused', function () {
    // A card worth nothing is only a support ticket waiting to happen.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.gift-card.store'), giftCardAdminValidPayload(['balance' => 0]))
        ->assertSessionHasErrors('balance');
});

test('an unknown currency is refused', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.gift-card.store'), giftCardAdminValidPayload(['currency_code' => 'ZZZ']))
        ->assertSessionHasErrors('currency_code');
});

test('an expiry in the past is refused on create', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.gift-card.store'), giftCardAdminValidPayload([
        'expires_at' => now()->subDay()->toDateTimeString(),
    ]))->assertSessionHasErrors('expires_at');
});

test('the card page shows its ledger', function () {
    $this->actingAs(User::whereId(1)->first());
    $card = StoreGiftCard::factory()->create(['balance' => 1500, 'original_balance' => 2000]);
    $order = StoreOrder::factory()->completed()->create();
    $card->transactions()->create([
        'store_order_id' => $order->id,
        'type' => StoreGiftCardTransactionType::REDEEM,
        'amount' => -500,
        'balance_after' => 1500,
    ]);

    $this->get(route('admin.store.gift-card.show', $card->id))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreGiftCard/ShowStoreGiftCard')
            ->has('card.transactions', 1)
            ->where('card.transactions.0.amount_formatted', '-$5.00')
            ->where('card.transactions.0.balance_after_formatted', '$15.00')
        );
});

test('admin can edit a cards recipient expiry and enabled flag', function () {
    $this->actingAs(User::whereId(1)->first());
    $card = StoreGiftCard::factory()->create();
    $recipient = User::factory()->create(['username' => 'newowner']);

    $this->put(route('admin.store.gift-card.update', $card->id), [
        'username' => 'newowner',
        'expires_at' => null,
        'is_enabled' => false,
    ])->assertRedirect(route('admin.store.gift-card.show', $card->id));

    $card->refresh();
    expect($card->issued_to_user_id)->toBe($recipient->id);
    expect($card->is_enabled)->toBeFalse();
});

test('editing cannot move the balance', function () {
    // The ledger is the card's history, so a balance set behind its back would make the two disagree.
    $this->actingAs(User::whereId(1)->first());
    $card = StoreGiftCard::factory()->create(['balance' => 2000]);

    $this->put(route('admin.store.gift-card.update', $card->id), [
        'username' => null,
        'expires_at' => null,
        'is_enabled' => true,
        'balance' => 999999,
        'currency_code' => 'JPY',
    ]);

    $card->refresh();
    expect($card->balance)->toBe(2000);
    expect($card->currency_code)->not->toBe('JPY');
});

test('a card can be topped up', function () {
    $this->actingAs(User::whereId(1)->first());
    $card = StoreGiftCard::factory()->create(['balance' => 1000, 'original_balance' => 1000]);

    $this->post(route('admin.store.gift-card.adjust', $card->id), ['amount' => 500, 'note' => 'Extra apology'])
        ->assertRedirect();

    $card->refresh();
    expect($card->balance)->toBe(1500);
    // A top-up raises what the card was ever worth, so the "of X" figure still makes sense.
    expect($card->original_balance)->toBe(1500);

    $entry = $card->transactions()->latest('id')->firstOrFail();
    expect($entry->type)->toEqual(StoreGiftCardTransactionType::ADJUSTMENT);
    expect($entry->amount)->toBe(500);
    expect($entry->balance_after)->toBe(1500);
    expect($entry->created_by)->toBe(1);
});

test('credit can be taken off a card', function () {
    $this->actingAs(User::whereId(1)->first());
    $card = StoreGiftCard::factory()->create(['balance' => 1000, 'original_balance' => 1000]);

    $this->post(route('admin.store.gift-card.adjust', $card->id), ['amount' => -400]);

    $card->refresh();
    expect($card->balance)->toBe(600);
    // Taking credit off does not rewrite what was issued.
    expect($card->original_balance)->toBe(1000);
});

test('an adjustment that would go below zero is refused', function () {
    // Refused rather than clamped: the admin has the wrong figure in mind and should be told.
    $this->actingAs(User::whereId(1)->first());
    $card = StoreGiftCard::factory()->create(['balance' => 300]);

    $this->post(route('admin.store.gift-card.adjust', $card->id), ['amount' => -500]);

    expect($card->fresh()->balance)->toBe(300);
    $this->assertDatabaseCount('store_gift_card_transactions', 0);
});

test('a zero adjustment is refused', function () {
    $this->actingAs(User::whereId(1)->first());
    $card = StoreGiftCard::factory()->create();

    $this->post(route('admin.store.gift-card.adjust', $card->id), ['amount' => 0])
        ->assertSessionHasErrors('amount');
});

test('an unused card can be deleted', function () {
    $this->actingAs(User::whereId(1)->first());
    $card = StoreGiftCard::factory()->create();

    $this->delete(route('admin.store.gift-card.delete', $card->id))
        ->assertRedirect(route('admin.store.gift-card.index'));

    $this->assertDatabaseMissing('store_gift_cards', ['id' => $card->id]);
});

test('a card that has paid for an order cannot be deleted', function () {
    // Its ledger is part of that order's money trail and cascades away with the row.
    $this->actingAs(User::whereId(1)->first());
    $card = StoreGiftCard::factory()->create();
    $order = StoreOrder::factory()->completed()->create();
    $card->transactions()->create([
        'store_order_id' => $order->id,
        'type' => StoreGiftCardTransactionType::REDEEM,
        'amount' => -500,
        'balance_after' => 1500,
    ]);

    $this->delete(route('admin.store.gift-card.delete', $card->id));

    $this->assertDatabaseHas('store_gift_cards', ['id' => $card->id]);
});

test('the listing can be narrowed to cards with credit left', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreGiftCard::factory()->create(['code' => 'HASCREDIT-1', 'balance' => 500]);
    StoreGiftCard::factory()->emptied()->create(['code' => 'SPENT-1']);

    $this->get(route('admin.store.gift-card.index', ['filter' => ['spendable' => 'true']]))
        ->assertInertia(fn ($page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.code', 'HASCREDIT-1')
        );
});

test('the listing can be searched by code or recipient', function () {
    $this->actingAs(User::whereId(1)->first());
    $recipient = User::factory()->create(['username' => 'compensated']);
    StoreGiftCard::factory()->create(['code' => 'FINDME-AAAA', 'issued_to_user_id' => $recipient->id]);
    StoreGiftCard::factory()->create(['code' => 'OTHER-BBBB']);

    foreach (['FINDME', 'compensated'] as $needle) {
        $this->get(route('admin.store.gift-card.index', ['filter' => ['q' => $needle]]))
            ->assertInertia(fn ($page) => $page
                ->has('cards.data', 1)
                ->where('cards.data.0.code', 'FINDME-AAAA')
            );
    }
});

test('gift cards stay off the admin roles curated subset', function () {
    // A standing decision from Phase 1: issuing credit is handing out money, so it is superadmin-only
    // until an owner deliberately grants it through the Roles UI.
    $staff = User::factory()->create();
    $staff->assignRole('admin');

    $this->actingAs($staff)->get(route('admin.store.gift-card.index'))->assertStatus(403);
});

test('a permissioned non superadmin can manage gift cards', function () {
    // Granted through the Roles UI, which is how support staff would get this.
    $staff = User::factory()->create();
    $staff->assignRole('admin');
    $staff->givePermissionTo(['read store_gift_cards', 'create store_gift_cards', 'update store_gift_cards']);

    $this->actingAs($staff)->get(route('admin.store.gift-card.index'))->assertStatus(200);
    $this->actingAs($staff)->post(route('admin.store.gift-card.store'), giftCardAdminValidPayload())
        ->assertRedirect(route('admin.store.gift-card.show', StoreGiftCard::firstOrFail()->id));
});

test('a manually issued card is redeemable at the cart like a bought one', function () {
    // The point of the screen: support hands over a code and it works.
    $this->actingAs(User::whereId(1)->first());
    $this->post(route('admin.store.gift-card.store'), giftCardAdminValidPayload());
    $code = StoreGiftCard::firstOrFail()->code;

    $buyer = User::factory()->create();
    $this->actingAs($buyer)->post(route('store.cart.code'), ['code' => $code])
        ->assertRedirect(route('store.cart.show'));

    $this->assertDatabaseHas('store_carts', [
        'user_id' => $buyer->id,
        'store_gift_card_id' => StoreGiftCard::firstOrFail()->id,
    ]);
});

test('the gift card listing separates staff issued cards from purchased ones', function () {
    $staff = User::factory()->create(['username' => 'supportsam']);
    $byHand = StoreGiftCard::factory()->create(['created_by' => $staff->id]);
    // How a bought card looks: minted by the fulfilment job with nobody behind it.
    $purchased = StoreGiftCard::factory()->create(['created_by' => null]);

    $this->actingAs(User::whereId(1)->first())
        ->get(route('admin.store.gift-card.index'))
        ->assertStatus(200)
        ->assertInertia(function ($page) use ($byHand, $purchased) {
            $rows = collect($page->toArray()['props']['cards']['data'])->keyBy('id');

            expect($rows[$byHand->id]['creator']['username'])->toBe('supportsam');
            // The column renders "Purchased" off this null, so it has to be present and null.
            expect($rows[$purchased->id]['creator'])->toBeNull();
        });
});

test('the gift card listing can be sorted by creator', function () {
    // The column is sortable, and spatie 400s on a sort it was not told to allow.
    $this->actingAs(User::whereId(1)->first());

    $this->get(route('admin.store.gift-card.index', ['sort' => 'created_by']))->assertStatus(200);
    $this->get(route('admin.store.gift-card.index', ['sort' => '-created_by']))->assertStatus(200);
});
