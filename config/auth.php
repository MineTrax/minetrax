<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'users'
        ],

        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_resets'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

    /*
    |--------------------------------------------------------------------------
    | Show Random Avatar for User
    |--------------------------------------------------------------------------
    |
    | If enabled, This feature will show random avatar/profile photo for users
    | who don't have set any profile picture yet.
    |
    */
    'random_user_avatars' => env('RANDOM_USER_AVATARS', true),


    /*
    |--------------------------------------------------------------------------
    | Allow login via any provider
    |--------------------------------------------------------------------------
    |
    | If enabled, this feature will allow user to login via any provider
    | even if the user is not registered with that provider.
    |
    */
    'any_provider_social_auth' => env('ALLOW_ANY_PROVIDER_SOCIAL_AUTH', false),

    /*
    |--------------------------------------------------------------------------
    | Disable Email/Password Authentication
    |--------------------------------------------------------------------------
    |
    | If enabled, this feature will disable email/password authentication.
    | This means users can only login via social providers.
    |
    */
    'disable_email_password_auth' => env('DISABLE_EMAIL_PASSWORD_AUTH', false),

    /*
    |--------------------------------------------------------------------------
    | Max Profile Photo Size (KB)
    |--------------------------------------------------------------------------
    |
    | Maximum allowed size for profile photo in kilobytes.
    |
    */
    'max_profile_photo_size_kb' => env('MAX_USER_PROFILE_PHOTO_SIZE_KB', 512),

    /*
    |--------------------------------------------------------------------------
    | Max Profile Cover Photo Size (KB)
    |--------------------------------------------------------------------------
    |
    | Maximum allowed size for user's cover photo in kilobytes.
    |
    */
    'max_cover_photo_size_kb' => env('MAX_USER_COVER_PHOTO_SIZE_KB', 1024),

    /*
    |--------------------------------------------------------------------------
    | Enforce Two Factor Authentication for Staff Members
    |--------------------------------------------------------------------------
    |
    | If enabled, this feature will enforce two factor authentication for staff members.
    | Without enabling 2FA, staff members won't be able to access the admin panel.
    |
    */
    'enforce_2fa_for_staff' => env('ENFORCE_2FA_FOR_STAFF', false),


    /*
    |--------------------------------------------------------------------------
    | Force Lowercase Username
    |--------------------------------------------------------------------------
    |
    | If enabled, all usernames will be converted to lowercase during
    | registration and login processes. This ensures consistency in
    | username handling throughout the application.
    |
    */
    'force_lowercase_username' => env('FORCE_LOWERCASE_USERNAME', true),
];
