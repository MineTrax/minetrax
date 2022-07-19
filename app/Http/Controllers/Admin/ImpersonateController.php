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
        $this->middleware(['auth', 'verified', 'forbid-banned-user', 'redirect-uncompleted-user']);
    }

    public function take($user, Request $request)
    {
        $currentUser = $request->user();
        // Only if user is not already impersonating
        if ($currentUser->isImpersonating()) {
            return redirect()->back()->with(['toast' => ['type' => 'danger', 'title' => __('Already Impersonating!'), 'body' => __('You are already impersonating a user. Please leave current impersonation first.')]]);
        }

        $userObject = User::where('id', $user)->firstOrFail();
        $this->authorize('impersonate', $userObject);

        auth('web')->loginUsingId($user);
        session()->put('impersonated_by', $currentUser->id);

        return redirect()->route('home');
    }

    public function leave(Request $request)
    {
        $currentUser = $request->user();
        // Only if user is impersonating
        if (!$currentUser->isImpersonating()) {
            return redirect()->home();
        }

        $impersonatedBy = session()->get('impersonated_by');
        session()->remove('impersonated_by');

        auth()->loginUsingId($impersonatedBy);

        return redirect()->route('admin.user.index');
    }
}
