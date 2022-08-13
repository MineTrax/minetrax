<?php

return [
	/*
    |--------------------------------------------------------------------------
    |Enable Powered By Text References
    |--------------------------------------------------------------------------
    |
    | Hide powered by MineTrax references in footer and other places.
    |
    */
	"show_powered_by" => env("SHOW_POWERED_BY", true),

	/*
    |--------------------------------------------------------------------------
    | Show Home Button
    |--------------------------------------------------------------------------
    |
    | hide or show the home button in navbar.
    |
    */
	"show_home_button" => env("SHOW_HOME_BUTTON", false),

	/*
    |--------------------------------------------------------------------------
    | Use Legacy FTP Driver
    |--------------------------------------------------------------------------
    |
    | If enabled, while querying server for stats the fetcher will use legacy FTP driver.
    | This is useful in-game your gameserver is hosted in panel with only FTP support and
    | normal FTP driver is not working.
    | Eg: Multicraft FTP Server, FileZilla FTP Server
    |
    */
    "use_legacy_ftp_driver" => env("USE_LEGACY_FTP_DRIVER", false),

	/*
    |--------------------------------------------------------------------------
    | Mark user verified when a player account is linked
    |--------------------------------------------------------------------------
    |
    | If true, this feature automatically mark user's verified_at field when a user link some
    | player account to their profile. This will also automatically mark user not verified when
    | he removes linked player.
    |
    */
	"mark_user_verified_on_account_link" => env("MARK_USER_VERIFYED_ON_ACCOUNT_LINK", true),
];
