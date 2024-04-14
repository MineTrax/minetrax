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
                Rule::unique('users')->ignore($request->user()->id),
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
        $validNotificationType = ['database', 'mail', 'discord'];
        \Validator::make($request->all(), [
            'like_on_post' => 'required|array',
            'like_on_post.*' => Rule::in($validNotificationType),
            'comment_on_post' => 'required|array',
            'comment_on_post.*' => Rule::in($validNotificationType),
            'you_are_muted' => 'required|array',
            'you_are_muted.*' => Rule::in($validNotificationType),
            'you_are_banned' => 'required|array',
            'you_are_banned.*' => Rule::in($validNotificationType),
            'recruitment_submission_comment_created' => 'required|array',
            'recruitment_submission_comment_created.*' => Rule::in($validNotificationType),
            'recruitment_submission_status_changed' => 'required|array',
            'recruitment_submission_status_changed.*' => Rule::in($validNotificationType),
            'custom_form_submission_created' => 'required|array',
            'custom_form_submission_created.*' => Rule::in($validNotificationType),
            'news_commented_by_user' => 'required|array',
            'news_commented_by_user.*' => Rule::in($validNotificationType),
            'recruitment_submission_created' => 'required|array',
            'recruitment_submission_created.*' => Rule::in($validNotificationType),
        ])->validateWithBag('updateNotificationPreference');

        $likeOnPost = $request->like_on_post ?: $validNotificationType;
        $commentOnPost = $request->comment_on_post ?: $validNotificationType;
        $youAreMuted = $request->you_are_muted ?: $validNotificationType;
        $youAreBanned = $request->you_are_banned ?: $validNotificationType;
        $recruitmentSubmissionCommentCreated = $request->recruitment_submission_comment_created ?: $validNotificationType;
        $recruitmentSubmissionStatusChanged = $request->recruitment_submission_status_changed ?: $validNotificationType;
        $customFormSubmissionCreated = $request->custom_form_submission_created ?: $validNotificationType;
        $newsCommentedByUser = $request->news_commented_by_user ?: $validNotificationType;
        $recruitmentSubmissionCreated = $request->recruitment_submission_created ?: $validNotificationType;

        $notificationSettings = [
            'notifications' => [
                'like_on_post' => $likeOnPost,
                'comment_on_post' => $commentOnPost,
                'you_are_muted' => $youAreMuted,
                'you_are_banned' => $youAreBanned,
                'recruitment_submission_comment_created' => $recruitmentSubmissionCommentCreated,
                'recruitment_submission_status_changed' => $recruitmentSubmissionStatusChanged,
                'custom_form_submission_created' => $customFormSubmissionCreated,
                'news_commented_by_user' => $newsCommentedByUser,
                'recruitment_submission_created' => $recruitmentSubmissionCreated,
            ],
        ];

        $user = $request->user();

        $userSettings = $user->settings ?? [];
        $user->settings = array_merge($userSettings, $notificationSettings);
        $user->save();

        return redirect()->back();
    }
}
