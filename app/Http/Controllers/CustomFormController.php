<?php

namespace App\Http\Controllers;

use App\Models\CustomForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CustomFormController extends Controller
{
    public function index()
    {
        $forms = [];

        return Inertia::render('CustomForm/IndexCustomForm', [
            'forms' => $forms,
        ]);
    }

    public function show(CustomForm $customForm)
    {
        // Check if form can be viewed by given user/visitor.
        $this->authorize('viewPublic', $customForm);

        $canSubmitForm = Gate::allows('submit', $customForm);

        return Inertia::render('CustomForm/ShowCustomForm', [
            'customForm' => $customForm->append('description_html'),
            'currentUserCanSubmit' => $canSubmitForm,
        ]);
    }

    public function submit(Request $request, CustomForm $customForm)
    {
        $this->authorize('submit', $customForm);

        $customForm->submissions()->create([
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'data' => $request->all(),
        ]);

        return redirect()->route('custom-form.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Submit Successful!'), 'body' => __('Form has been submitted successfully.')]]);
    }
}
