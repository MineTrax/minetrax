<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserProfileController extends Controller
{
    public function postRegistrationSetup(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:30', 'alpha_dash',
                Rule::unique('users')->ignore($request->user()->id)
            ],
        ]);

        $user = $request->user();
        $user->username = $request->username;
        $user->user_setup_status = 1;
        $user->save();

        return redirect()->route('home');
    }

    public function deleteCoverImage(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->user()->deleteCoverImage();

        return back(303)->with('status', 'cover-image-deleted');
    }

    public function putUpdateNotificationPreference(Request $request)
    {
        \Validator::make($request->all(), [
            'like_on_post__email' => 'boolean',
            'comment_on_post__email' => 'boolean',
            'you_are_muted__email' => 'boolean',
            'you_are_banned__email' => 'boolean'
        ])->validateWithBag('updateNotificationPreference');

        $likeOnPost = $request->like_on_post__email ? ['database', 'mail'] : ['database'];
        $commentOnPost = $request->comment_on_post__email ? ['database', 'mail'] : ['database'];
        $youAreMuted = $request->you_are_muted__email ? ['database', 'mail'] : ['database'];
        $youAreBanned = $request->you_are_banned__email ? ['database', 'mail'] : ['database'];
        $notificationSettings = [
            'notifications' => [
                'like_on_post' => $likeOnPost,
                'comment_on_post' => $commentOnPost,
                'you_are_muted' => $youAreMuted,
                'you_are_banned' => $youAreBanned
            ]
        ];

        $user = $request->user();

        $userSettings = $user->settings ?? [];
        $user->settings = array_merge($userSettings, $notificationSettings);
        $user->save();

        return redirect()->back();
    }
}
