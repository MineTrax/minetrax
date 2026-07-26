<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server Side Rendering
    |--------------------------------------------------------------------------
    |
    | These options configures if and how Inertia uses Server Side Rendering
    | to pre-render the initial visits made to your application's pages.
    |
    | Do note that enabling these options will NOT automatically make SSR work,
    | as a separate rendering service needs to be available. To learn more,
    | please visit https://inertiajs.com/server-side-rendering
    |
    */

    'ssr' => [

        'enabled' => false,

        'url' => 'http://127.0.0.1:13714/render',

    ],

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | Where page components live on disk. Inertia v3 reads this block; the
    | `testing` block below is the v1/v2 shape and is no longer consulted by
    | the view finder, so without this the finder looks in Laravel's default
    | resources/js/pages and never resolves a single page in this app.
    |
    | `ensure_pages_exist` stays false at runtime — a path quirk should not
    | turn a working page into a 500 — but the testing assertion below uses
    | the same finder, so component() assertions now work.
    |
    */

    'pages' => [

        'ensure_pages_exist' => false,

        'paths' => array_unique([

            resource_path(config('app.theme').'/js/Pages'),
            resource_path('default/js/Pages'),

        ]),

        'extensions' => [

            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    |
    | The values described here are used to locate Inertia components on the
    | filesystem. For instance, when using `assertInertia`, the assertion
    | attempts to locate the component as a file relative to any of the
    | paths AND with any of the extensions specified here.
    |
    */

    'testing' => [

        'ensure_pages_exist' => true,

        'page_paths' => array_unique([

            resource_path(config('app.theme').'/js/Pages'),
            resource_path('default/js/Pages'),

        ]),

        'page_extensions' => [

            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Vite Prefetch
    |--------------------------------------------------------------------------
    |
    | This option configures if Vite should prefetch assets for Inertia pages.
    |
    */
    'vite_prefetch' => env('VITE_PREFETCH', true),

];
