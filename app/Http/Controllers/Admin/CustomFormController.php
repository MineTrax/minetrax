<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCustomFormRequest;
use App\Http\Requests\UpdateCustomFormRequest;
use App\Models\CustomForm;
use App\Queries\Filters\FilterMultipleFields;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomFormController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', CustomForm::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $customForms = QueryBuilder::for(CustomForm::class)
            ->allowedFilters([
                'id',
                'title',
                'slug',
                'status',
                'can_create_submission',
                'min_role_weight_to_view_submission',
                'max_submission_per_user',
                'is_notify_staff_on_submission',
                'is_visible_in_listing',
                'created_at',
                'created_by',
                AllowedFilter::custom('q', new FilterMultipleFields(['id', 'title', 'slug', 'description', 'min_role_weight_to_view_submission'])),
            ])
            ->allowedSorts(['id', 'title', 'slug', 'status', 'max_submission_per_user', 'can_create_submission', 'min_role_weight_to_view_submission', 'is_notify_staff_on_submission', 'is_visible_in_listing', 'created_at', 'submissions_count'])
            ->withCount('submissions')
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/CustomForm/IndexCustomForm', [
            'customForms' => $customForms,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', CustomForm::class);

        return Inertia::render('Admin/CustomForm/CreateCustomForm');
    }

    public function store(CreateCustomFormRequest $request)
    {
        CustomForm::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'status' => $request->status,
            'can_create_submission' => $request->can_create_submission,
            'max_submission_per_user' => $request->max_submission_per_user,
            'min_role_weight_to_view_submission' => $request->min_role_weight_to_view_submission,
            'is_notify_staff_on_submission' => $request->is_notify_staff_on_submission,
            'is_visible_in_listing' => $request->is_visible_in_listing,
            'fields' => $request->fields,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.custom-form.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Custom Form is created successfully')]]);
    }

    public function edit(CustomForm $customForm)
    {
        $this->authorize('update', $customForm);

        return Inertia::render('Admin/CustomForm/EditCustomForm', [
            'customForm' => $customForm,
        ]);
    }

    public function update(UpdateCustomFormRequest $request, CustomForm $customForm)
    {
        $this->authorize('update', $customForm);

        $customForm->title = $request->title;
        $customForm->slug = $request->slug;
        $customForm->description = $request->description;
        $customForm->status = $request->status;
        $customForm->can_create_submission = $request->can_create_submission;
        $customForm->max_submission_per_user = $request->max_submission_per_user;
        $customForm->min_role_weight_to_view_submission = $request->min_role_weight_to_view_submission;
        $customForm->is_notify_staff_on_submission = $request->is_notify_staff_on_submission;
        $customForm->is_visible_in_listing = $request->is_visible_in_listing;
        $customForm->fields = $request->fields;
        $customForm->updated_by = $request->user()->id;
        $customForm->save();

        return redirect()->route('admin.custom-form.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Custom Form updated successfully')]]);
    }

    public function destroy(CustomForm $customForm)
    {
        $this->authorize('delete', $customForm);

        $customForm->delete();

        return redirect()->route('admin.custom-form.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Custom Form has been deleted permanently')]]);
    }
}
