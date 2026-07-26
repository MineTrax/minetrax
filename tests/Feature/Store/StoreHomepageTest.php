<?php

namespace Tests\Feature\Store;

use App\Models\StoreCurrency;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreHomepageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);
        StoreCurrency::factory()->base()->create();
    }

    private function setHomepage(string $route): void
    {
        $settings = app(GeneralSettings::class);
        $settings->homepage_route = $route;
        $settings->save();
    }

    public function test_the_default_homepage_is_the_community_dashboard()
    {
        $this->assertEquals('dashboard', app(GeneralSettings::class)->homepage_route);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboard'));
    }

    public function test_the_flag_makes_the_store_the_homepage()
    {
        $this->setHomepage('store');

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Store/IndexStore'));
    }

    /**
     * The load-bearing case: switching the module off must not leave the site's front page
     * pointing at something that now denies every visitor.
     */
    public function test_disabling_the_module_falls_the_homepage_back_to_the_dashboard()
    {
        $this->setHomepage('store');
        config(['store.enabled' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboard'));
    }

    public function test_the_dashboard_stays_reachable_when_the_store_owns_the_root()
    {
        $this->setHomepage('store');

        $this->get(route('home.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboard'));
    }

    public function test_the_dashboard_route_works_with_the_flag_off_too()
    {
        $this->get(route('home.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboard'));
    }

    public function test_the_store_url_redirects_to_the_root_when_the_flag_is_on()
    {
        // One canonical URL for the storefront, and every existing route('store.index') link
        // keeps working.
        $this->setHomepage('store');

        $this->get(route('store.index'))
            ->assertStatus(301)
            ->assertRedirect(route('home'));
    }

    public function test_the_store_url_renders_normally_when_the_flag_is_off()
    {
        $this->get(route('store.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Store/IndexStore'));
    }

    public function test_the_store_url_does_not_redirect_when_the_module_is_disabled()
    {
        // Redirecting to a dashboard would be a confusing answer to "where is the store?".
        $this->setHomepage('store');
        config(['store.enabled' => false]);

        $this->get(route('store.index'))->assertForbidden();
    }

    public function test_the_shared_prop_tells_the_frontend_the_store_owns_the_root()
    {
        $this->setHomepage('store');

        $this->get(route('home'))
            ->assertInertia(fn ($page) => $page->where('store.isHomepage', true));

        $this->setHomepage('dashboard');

        $this->get(route('home'))
            ->assertInertia(fn ($page) => $page->where('store.isHomepage', false));
    }

    // --- Admin setting ------------------------------------------------------------------------

    public function test_an_admin_can_switch_the_homepage_to_the_store()
    {
        $this->actingAs(User::whereId(1)->first())
            ->post(route('admin.setting.general.update'), $this->generalPayload(['homepage_route' => 'store']))
            ->assertSessionHasNoErrors();

        $this->assertEquals('store', app(GeneralSettings::class)->refresh()->homepage_route);
    }

    public function test_the_store_option_is_not_offered_while_the_module_is_disabled()
    {
        config(['store.enabled' => false]);

        $this->actingAs(User::whereId(1)->first())
            ->get(route('admin.setting.general.show'))
            ->assertInertia(function ($page) {
                $options = $page->toArray()['props']['homepageOptions'];

                $this->assertArrayHasKey('dashboard', $options);
                $this->assertArrayNotHasKey('store', $options);
            });
    }

    /**
     * The module could be switched off between the form rendering and the submit, so the value is
     * re-checked server-side rather than trusted.
     */
    public function test_selecting_the_store_while_the_module_is_disabled_falls_back()
    {
        config(['store.enabled' => false]);

        $this->actingAs(User::whereId(1)->first())
            ->post(route('admin.setting.general.update'), $this->generalPayload(['homepage_route' => 'store']));

        $this->assertEquals('dashboard', app(GeneralSettings::class)->refresh()->homepage_route);
    }

    public function test_an_unknown_homepage_value_is_rejected()
    {
        $this->actingAs(User::whereId(1)->first())
            ->post(route('admin.setting.general.update'), $this->generalPayload(['homepage_route' => 'somewhere-else']))
            ->assertSessionHasErrors('homepage_route');
    }

    /**
     * @return array<string, mixed>
     */
    private function generalPayload(array $overrides = []): array
    {
        $settings = app(GeneralSettings::class);

        return array_merge($settings->toArray(), [
            'site_name' => $settings->site_name ?: 'MineTrax',
            'homepage_route' => 'dashboard',
        ], $overrides);
    }
}
