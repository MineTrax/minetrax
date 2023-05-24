<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
//        $this->authorize('view');
        return Inertia::render('Admin/Dashboard', []);
    }
}
