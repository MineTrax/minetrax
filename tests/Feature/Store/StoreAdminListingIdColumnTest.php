<?php

use App\Models\StorePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->actingAs(User::whereId(1)->first());
});

/**
 * Every store listing shows the record id in a sortable "#" column.
 *
 * The header is only half the feature: clicking it sends `?sort=id`, and spatie's query builder
 * rejects a sort it was not told to allow with a 400. A column that 400s on click looks like the
 * page is broken, so each listing is asked for both directions here rather than trusted to have
 * `id` in its allowed list.
 *
 * @return array<int, string>
 */
function storeAdminListingRoutes(): array
{
    return [
        'admin.store.package.index',
        'admin.store.category.index',
        'admin.store.order.index',
        'admin.store.coupon.index',
        'admin.store.sale.index',
        'admin.store.currency.index',
        'admin.store.variable.index',
        'admin.store.grant.index',
        'admin.store.ban.index',
        'admin.store.gift-card.index',
    ];
}

test('every store listing sorts by id in both directions', function (string $routeName) {
    $this->get(route($routeName, ['sort' => 'id']))->assertStatus(200);
    $this->get(route($routeName, ['sort' => '-id']))->assertStatus(200);
})->with(storeAdminListingRoutes());

test('a store listing ships the id of every row', function () {
    // The column renders `item.id`, so a listing that selected its columns by hand and left the
    // key out would render a blank cell rather than fail.
    StorePackage::factory()->count(3)->create();

    $this->get(route('admin.store.package.index'))
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            $rows = $page->toArray()['props']['packages']['data'];

            expect($rows)->toHaveCount(3);

            foreach ($rows as $row) {
                expect($row['id'] ?? null)->toBeInt();
            }
        });
});

test('sorting by id actually reorders the listing', function () {
    // Accepting the parameter is not the same as honouring it.
    $packages = StorePackage::factory()->count(3)->create();

    $this->get(route('admin.store.package.index', ['sort' => '-id']))
        ->assertInertia(fn ($page) => $page->where(
            'packages.data.0.id',
            $packages->max('id'),
        ));

    $this->get(route('admin.store.package.index', ['sort' => 'id']))
        ->assertInertia(fn ($page) => $page->where(
            'packages.data.0.id',
            $packages->min('id'),
        ));
});
