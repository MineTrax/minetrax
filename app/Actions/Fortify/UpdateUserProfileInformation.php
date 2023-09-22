<?php

namespace App\Actions\Fortify;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  mixed  $user
     * @return void
     */
    public function update($user, array $input)
    {
        $maxProfilePhotoSize = config('auth.max_profile_photo_size_kb');
        $maxCoverPhotoSize = config('auth.max_cover_photo_size_kb');
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            // 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'image', 'max:' . $maxProfilePhotoSize],
            'cover_image' => ['nullable', 'image', 'max:' . $maxCoverPhotoSize],
            'dob' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:m,f,o'],
            'about' => ['nullable', 'string', 'max:255'],
            's_discord_username' => ['nullable', 'string'],
            's_steam_profile_url' => ['nullable', 'url'],
            's_twitter_url' => ['nullable', 'url'],
            's_youtube_url' => ['nullable', 'url'],
            's_facebook_url' => ['nullable', 'url'],
            's_twitch_url' => ['nullable', 'url'],
            's_linkedin_url' => ['nullable', 'url'],
            's_tiktok_url' => ['nullable', 'url'],
            's_website_url' => ['nullable', 'url'],
            'show_gender' => ['boolean'],
            'show_yob' => ['boolean'],
            'profile_photo_source' => ['nullable', 'in:gravatar,linked_player'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if (isset($input['cover_image'])) {
            $user->updateCoverImage($input['cover_image']);
        }

        if (array_key_exists('email', $input) && $input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $social_links = [
                's_discord_username' => $input['s_discord_username'],
                's_steam_profile_url' => $input['s_steam_profile_url'],
                's_twitter_url' => $input['s_twitter_url'],
                's_youtube_url' => $input['s_youtube_url'],
                's_facebook_url' => $input['s_facebook_url'],
                's_twitch_url' => $input['s_twitch_url'],
                's_linkedin_url' => $input['s_linkedin_url'],
                's_tiktok_url' => $input['s_tiktok_url'],
                's_website_url' => $input['s_website_url'],
            ];

            $settings = [
                'show_yob' => $input['show_yob'],
                'show_gender' => $input['show_gender'],
                'profile_photo_source' => $input['profile_photo_source'],
            ];

            $userSettings = $user->settings ?? [];
            $settings = array_merge($userSettings, $settings);

            $user->forceFill([
                'name' => $input['name'],
                // 'email' => $input['email'],
                'dob' => $input['dob'],
                'gender' => $input['gender'],
                'about' => $input['about'],
                'social_links' => $social_links,
                'settings' => $settings,
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  mixed  $user
     * @return void
     */
    protected function updateVerifiedUser($user, array $input)
    {
        $social_links = [
            's_discord_username' => $input['s_discord_username'],
            's_steam_profile_url' => $input['s_steam_profile_url'],
            's_twitter_url' => $input['s_twitter_url'],
            's_youtube_url' => $input['s_youtube_url'],
            's_facebook_url' => $input['s_facebook_url'],
            's_twitch_url' => $input['s_twitch_url'],
            's_linkedin_url' => $input['s_linkedin_url'],
            's_tiktok_url' => $input['s_tiktok_url'],
            's_website_url' => $input['s_website_url'],
        ];

        $settings = [
            'show_yob' => $input['show_yob'],
            'show_gender' => $input['show_gender'],
            'profile_photo_source' => $input['profile_photo_source'],
        ];

        $user->forceFill([
            'name' => $input['name'],
            // 'email' => $input['email'],
            'email_verified_at' => null,
            'dob' => $input['dob'],
            'gender' => $input['gender'],
            'about' => $input['about'],
            'social_links' => $social_links,
            'settings' => $settings,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
