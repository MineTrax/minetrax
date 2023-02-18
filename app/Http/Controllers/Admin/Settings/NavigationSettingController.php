<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\CustomPage;
use App\Settings\GeneralSettings;
use App\Settings\NavigationSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NavigationSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:update settings']);
    }

    public function show(NavigationSettings $settings, GeneralSettings $generalSettings)
    {
        // All public routes which we want to show in the navigation
        $availableNavItems = [
            [
                'type' => 'dropdown',
                'name' => 'Dropdown',
                'title' => 'Dropdown',
                'key' => 'dropdown',
                'children' => [],
                'authenticated' => false,
            ],
            [
                'type' => 'component',
                'name' => 'App Logo',
                'title' => 'App Logo',
                'component' => 'AppLogoMark',
                'key' => 'component-app-icon',
                'authenticated' => false,
            ],
            [
                'type' => 'component',
                'name' => 'Profile Dropdown',
                'title' => 'Profile Dropdown',
                'component' => 'ProfileDropdown',
                'key' => 'component-user-profile',
                'authenticated' => true,
            ],
            [
                'type' => 'component',
                'name' => 'Search Box',
                'title' => 'Search Box',
                'component' => 'NavbarSearch',
                'key' => 'component-search',
                'authenticated' => false,
            ],
            [
                'type' => 'component',
                'name' => 'Notification Bell',
                'title' => 'Notification Bell',
                'component' => 'NotificationDropdown',
                'key' => 'component-notification-dropdown',
                'authenticated' => true,
            ],
            [
                'type' => 'component',
                'name' => 'Theme Switcher',
                'title' => 'Theme Switcher',
                'component' => 'LightDarkSelector',
                'key' => 'component-theme-switcher',
                'authenticated' => false,
            ],
            [
                'type' => 'route',
                'name' => 'Home',
                'title' => 'Home',
                'route' => 'home',
                'key' => 'route-home',
                'authenticated' => false,
            ],
            [
                'type' => 'route',
                'name' => 'Statistics',
                'title' => 'Statistics',
                'route' => 'player.index',
                'key' => 'route-stats',
                'authenticated' => false,
            ],
            [
                'type' => 'route',
                'name' => 'Polls',
                'title' => 'Polls',
                'route' => 'poll.index',
                'key' => 'route-polls',
                'authenticated' => false,
            ],
            [
                'type' => 'route',
                'name' => 'News',
                'title' => 'News',
                'route' => 'news.index',
                'key' => 'route-news',
                'authenticated' => false,
            ],
            [
                'type' => 'route',
                'name' => 'Staff Members',
                'title' => 'Staff Members',
                'route' => 'staff.index',
                'key' => 'route-staff-members',
                'authenticated' => false,
            ],
            [
                'type' => 'route',
                'name' => 'Login',
                'title' => 'Login',
                'route' => 'login',
                'key' => 'route-login',
                'authenticated' => false,
                'guestonly' => true,
            ],
            [
                'type' => 'route',
                'name' => 'Register',
                'title' => 'Register',
                'route' => 'register',
                'key' => 'route-register',
                'authenticated' => false,
                'guestonly' => true,
            ],
            [
                'type' => 'route',
                'name' => 'Edit Profile',
                'title' => 'Edit Profile',
                'route' => 'profile.show',
                'key' => 'route-edit-profile',
                'authenticated' => true,
            ],
            [
            'type' => 'route',
            'name' => 'Linked Players',
            'title' => 'Linked Players',
            'route' => 'linked-player.list',
            'key' => 'route-linked-players',
            'authenticated' => true,
            ],
            [
                'type' => 'route',
                'name' => 'Features',
                'title' => 'Features',
                'route' => 'features.list',
                'key' => 'route-features',
                'authenticated' => false,
            ],
        ];

        // Custom pages
        $customPageItems = CustomPage::select(['id', 'title', 'path'])->get();
        foreach ($customPageItems as $item) {
            $availableNavItems[] = [
                'type' => 'custom-page',
                'name' => $item->title,
                'title' => $item->title,
                'id' => $item->id,
                'route' => 'custom-page.show',
                'route_params' => [
                    'path' => $item->path,
                ],
                'key' => 'custom-page-' . $item->id,
            ];
        }

        return Inertia::render('Admin/Setting/NavigationSetting', [
            'settings' => $settings->toArray(),
            'generalSettings' => $generalSettings->toArray(),
            'availableNavItems' => $availableNavItems,
        ]);
    }

    public function update(Request $request, NavigationSettings $settings, GeneralSettings $generalSettings)
    {
        $request->validate([
            'enable_custom_navbar' => 'required|boolean',
            'custom_navbar_data' => 'nullable|array',
            'enable_sticky_header_menu' =>  'required|boolean',
        ]);

        $navbarData = $request->input('custom_navbar_data');

        // Check in array if we have a dropdown type inside of a dropdown type then we give error
        foreach ($navbarData as $sideList) {
            foreach ($sideList as $item) {
                if ($item['type'] === 'dropdown') {
                    foreach ($item['children'] as $child) {
                        if ($child['type'] === 'dropdown' || $child['type'] === 'component') {
                            return redirect()->back()
                                ->with(['toast' => ['type' => 'error', 'title' => __('You can not add a dropdown or component inside of a dropdown')]]);
                        }
                    }
                }
            }
        }

        $settings->enable_custom_navbar = $request->input('enable_custom_navbar');
        $settings->custom_navbar_data = $navbarData;


        $generalSettings->enable_sticky_header_menu = $request->input('enable_sticky_header_menu');
        $generalSettings->save();

        $settings->save();
        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Navigation Settings Updated Successfully')]]);
    }
}
