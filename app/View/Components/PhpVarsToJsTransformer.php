<?php

namespace App\View\Components;

use App\Models\CustomPage;
use App\Settings\NavigationSettings;
use Illuminate\View\Component;

class PhpVarsToJsTransformer extends Component
{
    public function render()
    {
        $useWebsockets = config("broadcasting.default") == "pusher" || config("broadcasting.default") == "ably";
        $useWebsockets = $useWebsockets && config("broadcasting.connections." . config("broadcasting.default") . ".key");

        $pusher = [
            "USE_WEBSOCKETS" => $useWebsockets,
            "VITE_PUSHER_APP_KEY" => config("broadcasting.connections.pusher.key"),
            "VITE_PUSHER_HOST" => config("broadcasting.connections.pusher._pusher_host"),
            "VITE_PUSHER_PORT" => config("broadcasting.connections.pusher._pusher_port"),
            "VITE_PUSHER_SCHEME" => config("broadcasting.connections.pusher._pusher_scheme"),
            "VITE_PUSHER_APP_CLUSTER" => config("broadcasting.connections.pusher._pusher_app_cluster"),
        ];

        $navbar = $this->generateCustomNavbarData();

        return view('components.php-vars-to-js-transformer', [
            'pusher' => $pusher,
            'customnav' => $navbar
        ]);
    }

    private function generateCustomNavbarData()
    {
        $navbarSettings = app(NavigationSettings::class);
        $customNavbarEnabled = $navbarSettings->enable_custom_navbar;
        return [
            'enabled' => $customNavbarEnabled,
            'data' => $customNavbarEnabled ? $navbarSettings->custom_navbar_data : [],
        ];
    }
}
