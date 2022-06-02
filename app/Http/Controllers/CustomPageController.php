<?php

namespace App\Http\Controllers;

use App\Models\CustomPage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomPageController extends Controller
{
    public function show(CustomPage $customPage)
    {
        if (!$customPage->is_visible) {
            if (!request()->user() || !request()->user()->is_staff) {
                abort(404);
            }
        }

        if ($customPage->is_redirect) {
            return Inertia::location($customPage->redirect_url);
        }

        return Inertia::render('CustomPage/ShowCustomPage', [
            'customPage' => $customPage->append('body_html')
        ]);
    }
}
