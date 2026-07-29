<?php

namespace Tests\Feature\Store;

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
        $this->baseCurrency();
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

    // --- Default navbar -------------------------------------------------------------------------

    /**
     * The navbar is JSON-encoded into window._customnav by a Blade component rather than shipped as
     * an Inertia prop, so it is read off the rendered page.
     *
     * @return array<int, array<string, mixed>>
     */
    private function defaultNavbarLeft(): array
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        preg_match('/window\._customnav = (.*?);\s*\n/', $html, $matches);

        $this->assertNotEmpty($matches, 'The navbar payload was not found on the page.');

        return json_decode(html_entity_decode($matches[1], ENT_QUOTES), true)['data']['left'];
    }

    public function test_store_is_the_first_navbar_item_after_the_logo()
    {
        $left = $this->defaultNavbarLeft();

        $this->assertSame('App Logo', $left[0]['name']);
        $this->assertSame('Store', $left[1]['name'], 'Store leads the navbar.');
        $this->assertSame('Statistics', $left[2]['name'], 'Statistics follows it.');
    }

    public function test_the_navbar_store_item_points_at_the_storefront()
    {
        $this->assertSame('store.index', $this->defaultNavbarLeft()[1]['route']);
    }

    public function test_the_navbar_store_item_points_at_the_root_when_the_store_owns_it()
    {
        // store.index only 301s to `/` in this case, so linking there directly saves the hop.
        $this->setHomepage('store');

        $this->assertSame('home', $this->defaultNavbarLeft()[1]['route']);
    }

    public function test_a_dashboard_item_appears_only_when_the_store_owns_the_root()
    {
        $this->assertNotContains('Dashboard', collect($this->defaultNavbarLeft())->pluck('name')->all());

        $this->setHomepage('store');

        $left = $this->defaultNavbarLeft();

        $this->assertSame('Dashboard', $left[2]['name'], 'Otherwise nothing points at the community homepage.');
        $this->assertSame('home.dashboard', $left[2]['route']);
    }

    public function test_the_navbar_has_no_store_item_when_the_module_is_disabled()
    {
        config(['store.enabled' => false]);

        $names = collect($this->defaultNavbarLeft())->pluck('name')->all();

        $this->assertNotContains('Store', $names);
        $this->assertSame('Statistics', $names[1]);
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
