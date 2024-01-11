<?php

namespace App\View\Components;

use App\Models\CustomPage;
use App\Settings\NavigationSettings;
use Illuminate\View\Component;

class PhpVarsToJsTransformer extends Component
{
    const DEFAULT_NAV_LEFT = [
        [
            'type' => 'component',
            'name' => 'App Logo',
            'title' => 'App Logo',
            'component' => 'AppLogoMark',
            'key' => 'component-app-icon-01',
            'authenticated' => false,
        ],
        [
            'type' => 'route',
            'name' => 'Statistics',
            'title' => 'Statistics',
            'route' => 'player.index',
            'key' => 'route-stats-01',
            'authenticated' => false,
        ],
        [
            'type' => 'route',
            'name' => 'Polls',
            'title' => 'Polls',
            'route' => 'poll.index',
            'key' => 'route-polls-01',
            'authenticated' => false,
        ],
    ];

    const DEFAULT_NAV_RIGHT = [
        [
            'type' => 'component',
            'name' => 'Search Box',
            'title' => 'Search Box',
            'component' => 'NavbarSearch',
            'key' => 'component-search-01',
            'authenticated' => false,
        ],
        [
            'type' => 'component',
            'name' => 'Notification Bell',
            'title' => 'Notification Bell',
            'component' => 'NotificationDropdown',
            'key' => 'component-notification-dropdown-01',
            'authenticated' => true,
        ],
        [
            'type' => 'component',
            'name' => 'Profile Dropdown',
            'title' => 'Profile Dropdown',
            'component' => 'ProfileDropdown',
            'key' => 'component-user-profile-01',
            'authenticated' => true,
        ],
        [
            'type' => 'route',
            'name' => 'Login',
            'title' => 'Login',
            'route' => 'login',
            'key' => 'route-login-01',
            'authenticated' => false,
            'guestonly' => true,
        ],
        [
            'type' => 'route',
            'name' => 'Register',
            'title' => 'Register',
            'route' => 'register',
            'key' => 'route-register-01',
            'authenticated' => false,
            'guestonly' => true,
        ],
        [
            'type' => 'component',
            'name' => 'Theme Switcher',
            'title' => 'Theme Switcher',
            'component' => 'LightDarkSelector',
            'key' => 'component-theme-switcher-01',
            'authenticated' => false,
        ],
    ];

    public function render()
    {
        $useWebsockets = config('broadcasting.default') == 'pusher' || config('broadcasting.default') == 'ably';
        $useWebsockets = $useWebsockets && config('broadcasting.connections.'.config('broadcasting.default').'.key');

        $pusher = [
            'USE_WEBSOCKETS' => $useWebsockets,
            'VITE_PUSHER_APP_KEY' => config('broadcasting.connections.pusher.key'),
            'VITE_PUSHER_HOST' => config('broadcasting.connections.pusher._pusher_host'),
            'VITE_PUSHER_PORT' => config('broadcasting.connections.pusher._pusher_port'),
            'VITE_PUSHER_SCHEME' => config('broadcasting.connections.pusher._pusher_scheme'),
            'VITE_PUSHER_APP_CLUSTER' => config('broadcasting.connections.pusher._pusher_app_cluster'),
        ];

        $navbarSettings = app(NavigationSettings::class);
        $navbar = $this->generateCustomNavbarData($navbarSettings);
        $footer = $navbarSettings->enable_custom_footer ? $navbarSettings->custom_footer_data : null;

        return view('components.php-vars-to-js-transformer', [
            'pusher' => $pusher,
            'customnav' => $navbar,
            'customfooter' => $footer,
        ]);
    }

    private function generateCustomNavbarData($navbarSettings)
    {
        $customNavbarEnabled = $navbarSettings->enable_custom_navbar;

        // If custom navbar is disabled, generate default navbar
        if (! $customNavbarEnabled) {
            $customPagesInNavbar = CustomPage::visible()->navbar()->select(['id', 'title', 'path', 'is_in_navbar', 'is_visible', 'is_open_in_new_tab'])->get();

            $leftNavbar = self::DEFAULT_NAV_LEFT;
            $dropdownList = [
                'type' => 'dropdown',
                'name' => 'Dropdown',
                'title' => 'Others',
                'key' => 'dropdown-others-01',
                'children' => [
                    [
                        'type' => 'route',
                        'name' => 'News',
                        'title' => 'News',
                        'route' => 'news.index',
                        'key' => 'route-news-01',
                        'authenticated' => false,
                    ],
                    [
                        'type' => 'route',
                        'name' => 'Staff Members',
                        'title' => 'Staff Members',
                        'route' => 'staff.index',
                        'key' => 'route-staff-members-01',
                        'authenticated' => false,
                    ],
                    [
                        'type' => 'route',
                        'name' => 'Downloads',
                        'title' => 'Downloads',
                        'route' => 'download.index',
                        'key' => 'route-downloads-01',
                        'authenticated' => false,
                    ],
                    [
                        'type' => 'route',
                        'name' => 'Custom Forms',
                        'title' => 'Forms',
                        'route' => 'custom-form.index',
                        'key' => 'route-custom-forms-01',
                        'authenticated' => false,
                    ],
                ],
                'authenticated' => false,
            ];

            foreach ($customPagesInNavbar as $page) {
                $dropdownList['children'][] = [
                    'type' => 'custom-page',
                    'name' => $page->title,
                    'title' => $page->title,
                    'id' => $page->id,
                    'route' => 'custom-page.show',
                    'route_params' => [
                        'path' => $page->path,
                    ],
                    'is_open_in_new_tab' => $page->is_open_in_new_tab,
                    'key' => 'custom-page-'.$page->id.'-01',
                ];
            }
            $leftNavbar[] = $dropdownList;

            $navbarData = [
                'left' => $leftNavbar,
                'middle' => [],
                'right' => self::DEFAULT_NAV_RIGHT,
            ];
        } else {
            $navbarData = $navbarSettings->custom_navbar_data;
        }

        return [
            'enabled' => $customNavbarEnabled,
            'data' => $navbarData,
        ];
    }
}
