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
            ->with(['user:id,name,username', 'country:id,iso_code,flag,name', 'customForm' => function ($q) use ($maxRoleWeightOfStaffMember) {
                $q->select(['id', 'title', 'status', 'slug', 'min_role_weight_to_view_submission'])
                    ->where('min_role_weight_to_view_submission', '<=', $maxRoleWeightOfStaffMember)
                    ->orWhereNull('min_role_weight_to_view_submission');
            }])
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

    public function destroy(Request $request, CustomFormSubmission $submission)
    {
        $this->authorize('delete', $submission);

        $submission->delete();

        return redirect()->route('admin.custom-form-submission.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Custom Form Submission deleted successfully')]]);
    }
}
