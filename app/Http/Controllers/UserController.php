<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    public function showProfile(User $user): \Inertia\Response
    {
        $user->loadCount('posts')->load(['country', 'players:id,uuid,username,position,rating,last_seen_at,rank_id,country_id'])
            ->makeHidden(['email', 'dob', 'gender', 'updated_at', 'provider_id', 'provider_name', 'two_factor_confirmed_at', 'settings']);

        return Inertia::render('User/ShowUser', [
            'profileUser' => $user->load('badges:id,name,shortname,sort_order'),
        ]);
    }

    public function indexStaff(): \Inertia\Response
    {
        $staffsWithRole = User::with(['roles'])
            ->whereHas('roles', function ($query) {
                $query->where('is_staff', true)
                    ->where('is_hidden_from_staff_list', false)
                    ->orderByDesc('weight');
            })
            ->select(['id', 'name', 'username', 'profile_photo_path', 'verified_at'])
            ->get();

        return Inertia::render('User/IndexStaff', [
            'staffs' => $staffsWithRole,
        ]);
    }
}
