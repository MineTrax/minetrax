<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    public function showProfile(User $user)
    {
        $user->loadCount('posts');
        $user->load([
            'country',
            'badges:id,name,shortname,sort_order',
            'players:id,uuid,username,skin_texture_id,position,rating,last_seen_at,rank_id,country_id'
        ]);

        $hiddenAttributes = [
            'email',
            'dob',
            'gender',
            'locale',
            'love_reacter_id',
            'user_setup_status',
            'email_verified_at',
            'last_login_at',
            'discord_user_id',
            'discord_private_channel_id',
            'updated_at',
            'provider_id',
            'provider_name',
            'two_factor_confirmed_at',
            'settings',
        ];

        if (request()->user()?->can('view', $user)) {
            $hiddenAttributes = array_diff($hiddenAttributes, ['email', 'email_verified_at', 'last_login_at', 'discord_user_id']);
            $user->load('socialAccounts');
        }

        $user->makeHidden($hiddenAttributes);

        return Inertia::render('User/ShowUser', [
            'profileUser' => $user,
        ]);
    }

    public function indexStaff(): \Inertia\Response
    {
        $staffsWithRole = User::with(['roles'])
            ->whereHas('roles', function ($query) {
                $query->where('is_staff', true)
                    ->where('is_hidden_from_staff_list', false);
            })
            ->select(['id', 'name', 'username', 'profile_photo_path', 'cover_image_path', 'verified_at'])
            ->get();

        $staffsWithRole = $staffsWithRole->sortByDesc(function ($staff, $key) {
            return $staff->roles->sortBy('weight')->first()->weight;
        })->values()->all();

        return Inertia::render('User/IndexStaff', [
            'staffs' => $staffsWithRole,
        ]);
    }
}
