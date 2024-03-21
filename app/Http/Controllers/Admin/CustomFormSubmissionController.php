<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CustomFormStatus;
use App\Http\Controllers\Controller;
use App\Models\CustomForm;
use App\Models\CustomFormSubmission;
use App\Queries\Filters\FilterMultipleFields;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomFormSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', CustomFormSubmission::class);
        $request->validate([
            'forms' => 'sometimes|nullable|array',
            'forms.*' => 'sometimes|nullable|integer|exists:custom_forms,id',
        ]);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        // Only those forms which this staff member has access to.
        $maxRoleWeightOfStaffMember = $request->user()->maxRoleWeight();
        $customForms = CustomForm::select(['id', 'title'])
            ->where('status', '!=', CustomFormStatus::DRAFT)
            ->where(function ($q) use ($maxRoleWeightOfStaffMember) {
                $q->where('min_role_weight_to_view_submission', '<=', $maxRoleWeightOfStaffMember)
                    ->orWhereNull('min_role_weight_to_view_submission');
            })
            ->orderByDesc('id')
            ->get()->pluck('title', 'id');

        $fields = [
            'id',
            'custom_form_id',
            'user_id',
            'created_at',
            'country_id',
        ];
        $selectedForms = $request->query('forms') ?? null;
        $submissions = QueryBuilder::for(CustomFormSubmission::class)
            ->when($selectedForms, function ($query, $selectedForms) {
                $query->whereIn('custom_form_id', $selectedForms);
            })
            ->whereIn('custom_form_id', $customForms->keys())
            ->with(['user:id,name,username', 'country:id,iso_code,flag,name', 'customForm'])
            ->select($fields)
            ->allowedFilters([
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['data'])),
            ])
            ->allowedSorts($fields)
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/CustomFormSubmission/IndexCustomFormSubmission', [
            'submissions' => $submissions,
            'archived' => false, // This is used to show the archived badge in the UI.
            'forms' => $customForms,
            'filters' => request()->all(['perPage', 'sort', 'filter', 'forms']),
        ]);
    }

    public function indexArchived(Request $request)
    {
        $this->authorize('viewAny', CustomFormSubmission::class);
        $request->validate([
            'forms' => 'sometimes|nullable|array',
            'forms.*' => 'sometimes|nullable|integer|exists:custom_forms,id',
        ]);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        // Only those forms which this staff member has access to.
        $maxRoleWeightOfStaffMember = $request->user()->maxRoleWeight();
        $customForms = CustomForm::select(['id', 'title'])
            ->where('status', '!=', CustomFormStatus::DRAFT)
            ->where(function ($q) use ($maxRoleWeightOfStaffMember) {
                $q->where('min_role_weight_to_view_submission', '<=', $maxRoleWeightOfStaffMember)
                    ->orWhereNull('min_role_weight_to_view_submission');
            })
            ->orderByDesc('id')
            ->get()->pluck('title', 'id');

        $fields = [
            'id',
            'custom_form_id',
            'user_id',
            'created_at',
            'country_id',
            'deleted_at',
        ];
        $selectedForms = $request->query('forms') ?? null;
        $submissions = QueryBuilder::for(CustomFormSubmission::class)
            ->onlyTrashed()
            ->when($selectedForms, function ($query, $selectedForms) {
                $query->whereIn('custom_form_id', $selectedForms);
            })
            ->whereIn('custom_form_id', $customForms->keys())
            ->with(['user:id,name,username', 'country:id,iso_code,flag,name', 'customForm'])
            ->select($fields)
            ->allowedFilters([
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['data'])),
            ])
            ->allowedSorts($fields)
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/CustomFormSubmission/IndexCustomFormSubmission', [
            'archived' => true, // This is used to show the archived badge in the UI.
            'submissions' => $submissions,
            'forms' => $customForms,
            'filters' => request()->all(['perPage', 'sort', 'filter', 'forms']),
        ]);
    }

    public function show(Request $request, CustomFormSubmission $submission)
    {
        $this->authorize('view', $submission);

        $submission->load(['user:id,name,username', 'country:id,iso_code,flag,name', 'customForm:id,title,status,slug']);

        return Inertia::render('Admin/CustomFormSubmission/ShowCustomFormSubmission', [
            'submission' => $submission,
        ]);
    }

    public function archive(Request $request, CustomFormSubmission $submission)
    {
        $this->authorize('delete', $submission);

        $submission->delete();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Archived Successfully'), 'body' => __('Custom Form Submission archived successfully')]]);
    }

    public function restore(Request $request, CustomFormSubmission $submission)
    {
        $this->authorize('forceDelete', $submission);

        $submission->restore();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Restored Successfully'), 'body' => __('Custom Form Submission restored successfully')]]);
    }

    public function destroy(Request $request, CustomFormSubmission $submission)
    {
        $this->authorize('forceDelete', $submission);

        $submission->forceDelete();

        if (url()->previous() == route('admin.custom-form-submission.show', $submission->id)) {
            return redirect()->route('admin.custom-form-submission.index')
                ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Custom Form Submission deleted successfully')]]);
        }

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Custom Form Submission deleted successfully')]]);
    }
}
