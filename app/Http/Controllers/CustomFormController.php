<?php

namespace App\Http\Controllers;

use App\Enums\CustomFormStatus;
use App\Events\CustomFormSubmissionCreated;
use App\Models\CustomForm;
use App\Queries\Filters\FilterMultipleFields;
use App\Services\GeolocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomFormController extends Controller
{
    public function index(Request $request)
    {
        $isAuthenticated = (bool) $request->user();
        $isStaff = $isAuthenticated && $request->user()->isStaffMember();

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'title',
            'slug',
            'status',
            'can_create_submission',
            'max_submission_per_user',
            'created_at',
        ];
        $canSubmit = $isStaff ? ['anyone', 'auth', 'staff'] : ['anyone', 'auth'];
        $forms = QueryBuilder::for(CustomForm::class)
            ->where('is_visible_in_listing', true)
            ->whereIn('status', [CustomFormStatus::ACTIVE, CustomFormStatus::DISABLED])
            ->whereIn('can_create_submission', $canSubmit)
            ->select($fields)
            ->allowedFilters([
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['title', 'description'])),
            ])
            ->allowedSorts($fields)
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('CustomForm/IndexCustomForm', [
            'forms' => $forms,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
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

    public function submit(Request $request, CustomForm $customForm, GeolocationService $geolocationService)
    {
        $this->authorize('submit', $customForm);

        // Map form fields to its keys
        $data = collect($customForm->fields)->map(function ($field) use ($request) {
            $requestData = $request->all();
            $field['data'] = $requestData[$field['name']] ?? null;

            return $field;
        })->toArray();

        $countryId = $geolocationService->getCountryIdFromIP($request->ip());
        $submission = $customForm->submissions()->create([
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'country_id' => $countryId ?? null,
            'data' => $data,
        ]);
        CustomFormSubmissionCreated::dispatch($submission);

        return redirect()->route('home')
            ->with(['toast' => ['type' => 'success', 'title' => __('Submit Successful!'), 'body' => __('Form has been submitted successfully.')]]);
    }
}
