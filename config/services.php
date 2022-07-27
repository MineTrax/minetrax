<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'github' => [
        'oauth_enabled' => env('GITHUB_OAUTH_ENABLED'),
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_AUTH_REDIRECT'),
    ],

    'google' => [
        'oauth_enabled' => env('GOOGLE_OAUTH_ENABLED'),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_AUTH_REDIRECT'),
    ],

    'twitter' => [
        'oauth_enabled' => env('TWITTER_OAUTH_ENABLED'),
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => env('TWITTER_AUTH_REDIRECT'),
    ],

    'facebook' => [
        'oauth_enabled' => env('FACEBOOK_OAUTH_ENABLED'),
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_AUTH_REDIRECT'),
    ],

    'discord' => [
        'oauth_enabled' => env('DISCORD_OAUTH_ENABLED'),
        'client_id' => env('DISCORD_CLIENT_ID'),
        'client_secret' => env('DISCORD_CLIENT_SECRET'),
        'redirect' => env('DISCORD_REDIRECT_URI'),

        // optional
        'allow_gif_avatars' => (bool)env('DISCORD_AVATAR_GIF', true),
        'avatar_default_extension' => env('DISCORD_EXTENSION_DEFAULT', 'jpg'), // only pick from jpg, png, webp
    ],
];
