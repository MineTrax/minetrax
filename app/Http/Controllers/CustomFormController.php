<?php

namespace App\Http\Controllers;

use App\Models\CustomForm;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomFormController extends Controller
{
    public function index()
    {

    }

    public function show(CustomForm $customForm)
    {
        // Check if form can be viewed by given user/visitor.
        $this->authorize('viewPublic', $customForm);

        return Inertia::render('CustomForm/ShowCustomForm', [
            'customForm' => $customForm->append('description_html'),
        ]);
    }

    public function submit(Request $request)
    {
    }
}
