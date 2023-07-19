<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Jetstream\DeleteUser;
use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use App\Notifications\UserYouAreBanned;
use App\Notifications\UserYouAreMuted;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Queries\Filters\FilterMultipleFields;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100)
            $perPage = 100;

        $users = QueryBuilder::for(User::class)->with('country:id,name,iso_code')
            ->allowedFilters([
                'id',
                'name',
                'email',
                'username',
                'created_at',
                'updated_at',
                'country_id',
                'last_login_at',
                AllowedFilter::custom('q', new FilterMultipleFields(['name', 'email', 'username']))
            ])
            ->allowedSorts(['id', 'name', 'email', 'username', 'created_at', 'updated_at', 'country_id', 'last_login_at'])
            ->defaultSort('id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/User/IndexUser', [
            'users' => $users,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    // @TODO
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return Inertia::render('Admin/User/ShowUser', [
            'user' => $user->load('badges'),
        ]);
    }

    public function ban(User $user, Request $request)
    {
        $this->authorize('ban', $user);

        if ($user->id === $request->user()->id) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'danger', 'title' => __('You cannot ban yourself')]]);
        }

        $user->banned_at = now();
        $user->save();
        $user->notify(new UserYouAreBanned($request->user()));

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('User Banned Successfully')]]);
    }

    public function unban(User $user, Request $request)
    {
        $this->authorize('ban', $user);

        if ($user->id === $request->user()->id) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'danger', 'title' => __('You cannot unban yourself')]]);
        }

        $user->banned_at = null;
        $user->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('User UnBanned Successfully')]]);
    }

    public function mute(User $user, Request $request)
    {
        $this->authorize('mute', $user);

        if ($user->id === $request->user()->id) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'danger', 'title' => __('You cannot mute yourself')]]);
        }

        $user->muted_at = now();
        $user->save();
        $user->notify(new UserYouAreMuted($request->user()));

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('User Muted Successfully')]]);
    }

    public function unmute(User $user, Request $request)
    {
        $this->authorize('mute', $user);

        if ($user->id === $request->user()->id) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'danger', 'title' => __('You cannot unmute yourself')]]);
        }

        $user->muted_at = null;
        $user->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('User UnMuted Successfully')]]);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $rolesList = Role::query()->pluck('display_name', 'name');
        $badgesList = Badge::query()->get(['name', 'id']);
        $countryList = Country::get(['name', 'id']);

        return Inertia::render('Admin/User/EditUser', [
            'userData' => $user->load(['badges', 'country:id,name']),
            'rolesList' => $rolesList,
            'badgesList' => $badgesList,
            'countryList' => $countryList,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['required', 'string', 'alpha_dash', 'max:30', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'image', 'max:1024'],
            'dob' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:m,f,o'],
            'about' => ['nullable', 'string', 'max:255'],
            's_discord_username' => ['nullable', 'string'],
            's_steam_profile_url' => ['nullable', 'url'],
            's_twitter_url' => ['nullable', 'url'],
            's_youtube_url' => ['nullable', 'url'],
            's_facebook_url' => ['nullable', 'url'],
            's_twitch_url' => ['nullable', 'url'],
            's_website_url' => ['nullable', 'url'],
            'cover_image_url' => ['nullable', 'url'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'show_yob' => ['required', 'boolean'],
            'show_gender' => ['required', 'boolean'],
            'profile_photo_source' => ['nullable', 'in:gravatar,linked_player'],
            'verified' => ['required', 'boolean'],
            'badges' => ['sometimes', 'nullable', 'array', 'exists:badges,id'],
            'country_id' => ['required', 'exists:countries,id'],
            'password' => ['sometimes', 'nullable', 'string', Password::min(8)->uncompromised()],
        ]);

        $social_links = [
            's_discord_username' => $request->s_discord_username,
            's_steam_profile_url' => $request->s_steam_profile_url,
            's_twitter_url' => $request->s_twitter_url,
            's_youtube_url' => $request->s_youtube_url,
            's_facebook_url' => $request->s_facebook_url,
            's_twitch_url' => $request->s_twitch_url,
            's_website_url' => $request->s_website_url,
        ];

        $settings = [
            'show_yob' => $request->show_yob,
            'show_gender' => $request->show_gender,
            'profile_photo_source' => $request->profile_photo_source,
        ];

        // Update the User Detail
        $user->name = $request->name;
        $user->dob = $request->dob;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->gender = $request->gender;
        $user->social_links = $social_links;
        $user->settings = $settings;
        $user->about = $request->about ?? null;
        $user->country_id = $request->country_id;

        // if verified_at was null and now verified is true then mark it as verified & vice versa
        if ($request->verified && !$user->verified_at) {
            $user->verified_at = now();
        } else if (!$request->verified && $user->verified_at) {
            $user->verified_at = null;
        }

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Sync the role only if user has ability to assign roles
        if ($request->user()->can('assign roles')) {
            $user->syncRoles([$request->role]);
        }

        // Sync Badges
        if ($request->user()->can('assign badges')) {
            $user->badges()->sync($request->badges);
        }

        // Redirect to listing page
        return redirect()->route('admin.user.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('User updated successfully')]]);
    }

    public function destroy(Request $request, User $user, DeleteUser $deleteUser)
    {
        $this->authorize('delete', $user);

        if ($user->id === $request->user()->id) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'danger', 'title' => __('You cannot delete yourself')]]);
        }

        $deleteUser->delete($user);

        return redirect()->route('admin.user.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('User deleted successfully')]]);
    }
}
