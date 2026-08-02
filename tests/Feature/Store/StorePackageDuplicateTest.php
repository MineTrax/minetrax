<?php

use App\Enums\StoreCommandTrigger;
use App\Enums\StorePackageRequirementMode;
use App\Models\Server;
use App\Models\StoreCategory;
use App\Models\StoreCommand;
use App\Models\StorePackage;
use App\Models\StoreVariable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
});

/**
 * A package with something hanging off every relation duplication has to carry.
 */
function duplicableStorePackage(array $overrides = []): StorePackage
{
    $category = StoreCategory::factory()->create();

    $package = StorePackage::factory()->create(array_merge([
        'name' => 'Gold Rank',
        'slug' => 'gold-rank',
        'store_category_id' => $category->id,
        'price' => 1500,
        'discount_bp' => 1000,
        'is_enabled' => true,
        'is_visible' => true,
        'is_featured' => true,
        'sold_count' => 42,
        'expiry_duration_days' => 30,
        'required_packages_mode' => StorePackageRequirementMode::ANY,
    ], $overrides));

    $server = Server::factory()->create();
    $command = StoreCommand::factory()->forOwner($package)->create([
        'trigger' => StoreCommandTrigger::PURCHASE,
        'command' => 'lp user {PLAYER_USERNAME} parent add gold',
        'delay_seconds' => 15,
        'is_repeat_per_quantity' => true,
        'is_run_on_all_servers' => false,
    ]);
    $command->servers()->sync([$server->id]);

    $package->prices()->create(['currency_code' => 'JPY', 'price' => 2000]);

    $prerequisite = StorePackage::factory()->create(['name' => 'Silver Rank', 'slug' => 'silver-rank']);
    $package->requiredPackages()->sync([$prerequisite->id]);

    $variable = StoreVariable::factory()->create();
    $package->variables()->sync([$variable->id => ['sort_order' => 3]]);

    return $package->fresh();
}

test('guest and non staff cannot duplicate', function () {
    $package = duplicableStorePackage();

    $this->post(route('admin.store.package.duplicate', $package->id))->assertStatus(302);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.store.package.duplicate', $package->id))->assertStatus(302);
});

test('duplicating needs the create permission not just update', function () {
    // It writes a new package, so read-and-update staff must not be able to.
    $staff = User::factory()->create();
    $staff->assignRole('moderator');
    $staff->givePermissionTo(['read store_packages', 'update store_packages']);
    $package = duplicableStorePackage();

    $this->actingAs($staff)->post(route('admin.store.package.duplicate', $package->id))->assertStatus(403);

    expect(StorePackage::where('slug', 'gold-rank-copy')->exists())->toBeFalse();
});

test('duplicating is unavailable when the module is disabled', function () {
    $package = duplicableStorePackage();
    config(['store.enabled' => false]);

    // Superadmin bypasses the policy gate, so a permissioned non-superadmin proves the gate.
    $staff = User::factory()->create();
    $staff->assignRole('admin');

    $this->actingAs($staff)->post(route('admin.store.package.duplicate', $package->id))->assertStatus(403);
});

test('admin can duplicate a package', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = duplicableStorePackage();

    $this->post(route('admin.store.package.duplicate', $package->id))
        ->assertRedirect(route('admin.store.package.edit', StorePackage::latest('id')->first()->id));

    $copy = StorePackage::where('slug', 'gold-rank-copy')->firstOrFail();
    expect($copy->name)->toBe('Gold Rank (Copy)');
    expect($copy->price)->toBe(1500);
    expect($copy->discount_bp)->toBe(1000);
    expect($copy->store_category_id)->toBe($package->store_category_id);
    expect($copy->expiry_duration_days)->toBe(30);
    expect($copy->is_featured)->toBeTrue();
    expect($copy->required_packages_mode)->toEqual(StorePackageRequirementMode::ANY);
    expect($copy->created_by)->toBe(1);
});

test('the copy starts disabled', function () {
    // Otherwise duplicating puts a second identical package on the storefront immediately.
    $this->actingAs(User::whereId(1)->first());
    $package = duplicableStorePackage();

    $this->post(route('admin.store.package.duplicate', $package->id));

    expect(StorePackage::where('slug', 'gold-rank-copy')->firstOrFail()->is_enabled)->toBeFalse();
});

test('the copy has sold nothing', function () {
    // A copied sold_count would show a sold-out badge and eat the new package's purchase limits.
    $this->actingAs(User::whereId(1)->first());
    $package = duplicableStorePackage();

    $this->post(route('admin.store.package.duplicate', $package->id));

    expect(StorePackage::where('slug', 'gold-rank-copy')->firstOrFail()->sold_count)->toBe(0);
});

test('the commands come along with their server targets', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = duplicableStorePackage();
    $originalCommand = $package->commands()->firstOrFail();

    $this->post(route('admin.store.package.duplicate', $package->id));

    $copy = StorePackage::where('slug', 'gold-rank-copy')->firstOrFail();
    $copied = $copy->commands()->firstOrFail();

    expect($copy->commands)->toHaveCount(1);
    expect($copied->id)->not->toBe($originalCommand->id);
    expect($copied->command)->toBe('lp user {PLAYER_USERNAME} parent add gold');
    expect($copied->trigger)->toEqual(StoreCommandTrigger::PURCHASE);
    expect($copied->delay_seconds)->toBe(15);
    expect($copied->is_repeat_per_quantity)->toBeTrue();
    // The pivot is the point: copying the row alone would leave the copy firing nowhere.
    expect($copied->servers->pluck('id')->all())->toBe($originalCommand->servers->pluck('id')->all());
});

test('the per currency price overrides come along', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = duplicableStorePackage();

    $this->post(route('admin.store.package.duplicate', $package->id));

    $copy = StorePackage::where('slug', 'gold-rank-copy')->firstOrFail();
    expect($copy->prices)->toHaveCount(1);
    expect($copy->prices->first()->currency_code)->toBe('JPY');
    expect($copy->prices->first()->price)->toBe(2000);
});

test('the prerequisites and variables come along', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = duplicableStorePackage();

    $this->post(route('admin.store.package.duplicate', $package->id));

    $copy = StorePackage::where('slug', 'gold-rank-copy')->firstOrFail();
    expect($copy->requiredPackages->pluck('id')->all())->toBe($package->requiredPackages->pluck('id')->all());
    expect($copy->variables)->toHaveCount(1);
    // The pivot's order is what the buyer sees the inputs in, so it is part of the configuration.
    expect((int) $copy->variables->first()->pivot->sort_order)->toBe(3);
});

test('the comparison cells come along without being double encoded', function () {
    // The raw attribute is a JSON string; handing that back to an array-cast column would encode it
    // twice and the copy would read as a string rather than a table of cells.
    $this->actingAs(User::whereId(1)->first());
    $package = duplicableStorePackage(['comparison_values' => ['slots' => '10', 'support' => true]]);

    $this->post(route('admin.store.package.duplicate', $package->id));

    $copy = StorePackage::where('slug', 'gold-rank-copy')->firstOrFail();
    expect($copy->comparison_values)->toBe(['slots' => '10', 'support' => true]);
});

test('duplicating twice does not collide on the slug', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = duplicableStorePackage();

    $this->post(route('admin.store.package.duplicate', $package->id));
    $this->post(route('admin.store.package.duplicate', $package->id));

    expect(StorePackage::where('slug', 'gold-rank-copy')->exists())->toBeTrue();
    expect(StorePackage::where('slug', 'gold-rank-copy-2')->exists())->toBeTrue();
});

test('a trashed package still holding the slug does not block the copy', function () {
    // The unique index covers trashed rows, so a retired copy would otherwise fail the insert.
    $this->actingAs(User::whereId(1)->first());
    $package = duplicableStorePackage();
    StorePackage::factory()->create(['slug' => 'gold-rank-copy'])->delete();

    $this->post(route('admin.store.package.duplicate', $package->id))->assertSessionHasNoErrors();

    expect(StorePackage::where('slug', 'gold-rank-copy-2')->exists())->toBeTrue();
});

test('the package image comes along', function () {
    // A copy with no artwork is half a copy, and re-uploading it is exactly the tedium duplication
    // is meant to spare.
    Storage::fake('media');

    $this->actingAs(User::whereId(1)->first());
    $package = duplicableStorePackage();
    $original = $package->addMedia(UploadedFile::fake()->image('gold.jpg', 100, 100))->toMediaCollection('store-package');

    $this->post(route('admin.store.package.duplicate', $package->id));

    $copy = StorePackage::where('slug', 'gold-rank-copy')->firstOrFail();
    expect($copy->getFirstMedia('store-package'))->not->toBeNull();
    // Its own file, not a second row pointing at the original's — deleting one must not blank the other.
    expect($copy->getFirstMedia('store-package')->id)->not->toBe($original->id);
    expect($package->fresh()->getFirstMedia('store-package'))->not->toBeNull();
});

test('the original is untouched', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = duplicableStorePackage();

    $this->post(route('admin.store.package.duplicate', $package->id));

    $package->refresh();
    expect($package->name)->toBe('Gold Rank');
    expect($package->slug)->toBe('gold-rank');
    expect($package->sold_count)->toBe(42);
    expect($package->is_enabled)->toBeTrue();
    expect($package->commands)->toHaveCount(1);
});

test('the copy is not on the storefront until it is enabled', function () {
    // The end the disabled default exists for, asserted through the public listing.
    $this->actingAs(User::whereId(1)->first());
    $package = duplicableStorePackage();

    $this->post(route('admin.store.package.duplicate', $package->id));

    $copy = StorePackage::where('slug', 'gold-rank-copy')->firstOrFail();
    expect($copy->is_available)->toBeFalse();
    expect(StorePackage::available()->pluck('id'))->not->toContain($copy->id);
});
