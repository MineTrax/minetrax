<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\CustomPage;
use App\Settings\NavigationSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NavigationSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:update settings']);
    }

    public function show(NavigationSettings $settings)
    {
        // All public routes which we want to show in the navigation
        $availableNavItems = [
            [
                'type' => 'route',
                'name' => 'Home',
                'title' => 'Home',
                'route' => 'home',
                'key' => 'home',
            ],
            [
                'type' => 'component',
                'name' => 'Search Box',
                'title' => 'Search Box',
                'component' => 'Search',
                'key' => 'search',
            ],
            [
                'type' => 'component',
                'name' => 'App Icon',
                'title' => 'App Icon',
                'component' => 'AppIcon',
                'key' => 'app-icon',
            ],
            [
                'type' => 'component',
                'name' => 'Theme Switcher',
                'title' => 'Theme Switcher',
                'component' => 'ThemeSwitcher',
                'key' => 'theme-switcher',
            ],
            [
                'type' => 'component',
                'name' => 'Profile Section',
                'title' => 'Profile Section',
                'component' => 'ProfileSection',
                'key' => 'profile-section',
            ],
            [
                'type' => 'dropdown',
                'name' => 'Dropdown',
                'title' => 'Dropdown',
                'key' => 'dropdown',
                'children' => []
            ]
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
            'availableNavItems' => $availableNavItems,
        ]);
    }

    public function update(Request $request, NavigationSettings $settings)
    {
        $request->validate([
            'enable_custom_navbar' => 'required|boolean',
            'custom_navbar_data' => 'nullable|array',
        ]);

        $navbarData = $request->input('custom_navbar_data');

        // Check in array if we have a dropdown type inside of a dropdown type then we give error
        foreach ($navbarData as $sideList) {
            foreach ($sideList as $item) {
                if ($item['type'] === 'dropdown') {
                    foreach ($item['children'] as $child) {
                        if ($child['type'] === 'dropdown') {
                            return redirect()->back()
                                ->with(['toast' => ['type' => 'error', 'title' => __('You can not add a dropdown inside of a dropdown')]]);
                        }
                    }
                }
            }
        }


        $settings->enable_custom_navbar = $request->input('enable_custom_navbar');
        $settings->custom_navbar_data = $navbarData;
        $settings->save();
        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Navigation Settings Updated Successfully')]]);
    }
}
