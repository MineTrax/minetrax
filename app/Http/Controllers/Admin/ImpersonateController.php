<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ImpersonateController extends Controller
{
    public function __construct()
    {
        // because if we apply auth:sanctum then impersonation not working
        $this->middleware(['auth', 'verified-if-enabled', 'forbid-banned-user', 'redirect-uncompleted-user']);
    }

    public function take($user, Request $request)
    {
        $currentUser = $request->user();
        // Only if user is not already impersonating
        if ($currentUser->isImpersonating()) {
            return redirect()->back()->with(['toast' => ['type' => 'danger', 'title' => __('Already Impersonating!'), 'body' => __('You are already impersonating a user. Please leave current impersonation first.')]]);
        }

        $userObject = User::where('id', $user)->firstOrFail();
        if ($currentUser->id === $userObject->id) {
            return redirect()->back()->with(['toast' => ['type' => 'danger', 'title' => __('Impersonation Failed!'), 'body' => __('You cannot impersonate yourself.')]]);
        }

        $this->authorize('impersonate', $userObject);

        auth('web')->loginUsingId($user);
        session()->put('impersonated_by', $currentUser->id);
        session()->put('password_hash_web', $userObject->getAuthPassword());
        session()->put('password_hash_sanctum', $userObject->getAuthPassword());

        return redirect()->route('home');
    }

    public function leave(Request $request)
    {
        $currentUser = $request->user();
        // Only if user is impersonating
        if (! $currentUser->isImpersonating()) {
            return redirect()->route('home');
        }

        $impersonatedBy = session()->get('impersonated_by');
        $admin = User::where('id', $impersonatedBy)->firstOrFail();

        session()->remove('impersonated_by');
        session()->put('password_hash_web', $admin->getAuthPassword());
        session()->put('password_hash_sanctum', $admin->getAuthPassword());

        auth()->loginUsingId($impersonatedBy);

        return redirect()->route('admin.user.index');
    }
}
