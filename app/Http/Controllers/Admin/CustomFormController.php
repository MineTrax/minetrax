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
use Arr;
use Str;

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

    public function show(CustomForm $customForm)
    {
        $this->authorize('view', $customForm);

        $submissions = $customForm->submissions()->latest()->get();

        $data = [];
        foreach($customForm->fields as $field) {
            $data[$field['name']] = [
                'label' => $field['label'],
                'type' => $field['type'],
                'data' => [],
            ];

            switch($field['type']) {
                case 'text':
                case 'textarea':
                case 'email':
                case 'number':
                case 'password':
                case 'tel':
                case 'url':
                    $data[$field['name']]['data'] = $submissions->reduce(function($carry, $submission) use ($field) {
                        $found = Arr::first($submission->data, function($value) use ($field) {
                            return $value['name'] === $field['name'];
                        });

                        $carry[] = $found ? Str::limit($found['data'], 100) : null;
                        return $carry;
                    }, []);
                    break;
                case 'select':
                case 'radio':
                case 'multiselect':
                    $data[$field['name']]['data'] = $submissions->reduce(function($carry, $submission) use ($field) {
                        $found = Arr::first($submission->data, function($value) use ($field) {
                            return $value['name'] === $field['name'];
                        });

                        if ($found) {
                            if ($field['type'] === 'multiselect') {
                                foreach($found['data'] as $value) {
                                    $carry[$value] = isset($carry[$value]) ? $carry[$value] + 1 : 1;
                                }
                            } else {
                                $carry[$found['data']] = isset($carry[$found['data']]) ? $carry[$found['data']] + 1 : 1;
                            }
                        }
                        return $carry;
                    }, []);
                    break;
                case 'checkbox':
                    $data[$field['name']]['data'] = $submissions->reduce(function($carry, $submission) use ($field) {
                        $found = Arr::first($submission->data, function($value) use ($field) {
                            return $value['name'] === $field['name'];
                        });

                        if ($found) {
                            $checked = $found['data'] == 1 ? __("Yes") : __("No");
                            $carry[$checked] = isset($carry[$checked]) ? $carry[$checked] + 1 : 1;
                        }
                        return $carry;
                    }, []);
                    break;
                case 'datetime-local':
                case 'date':
                    // group submissions count by month year
                    $data[$field['name']]['data'] = $submissions->reduce(function($carry, $submission) use ($field) {
                        $found = Arr::first($submission->data, function($value) use ($field) {
                            return $value['name'] === $field['name'];
                        });

                        if ($found) {
                            $date = \Carbon\Carbon::parse($found['data']);
                            $key = $date->format('Y-m');
                            $carry[$key] = isset($carry[$key]) ? $carry[$key] + 1 : 1;
                        }
                        return $carry;
                    }, []);
                    break;
                case 'time':
                    // group submissions count by hour
                    $data[$field['name']]['data'] = $submissions->reduce(function($carry, $submission) use ($field) {
                        $found = Arr::first($submission->data, function($value) use ($field) {
                            return $value['name'] === $field['name'];
                        });

                        if ($found) {
                            $date = \Carbon\Carbon::parse($found['data']);
                            $key = $date->format('H');
                            $carry[$key] = isset($carry[$key]) ? $carry[$key] + 1 : 1;
                        }
                        return $carry;
                    }, []);
                    break;
                case 'month':
                    // group submissions count by month
                    $data[$field['name']]['data'] = $submissions->reduce(function($carry, $submission) use ($field) {
                        $found = Arr::first($submission->data, function($value) use ($field) {
                            return $value['name'] === $field['name'];
                        });

                        if ($found) {
                            $date = \Carbon\Carbon::parse($found['data']);
                            $key = $date->format('F');
                            $carry[$key] = isset($carry[$key]) ? $carry[$key] + 1 : 1;
                        }
                        return $carry;
                    }, []);
                    break;
                case 'week':
                    // group submissions count by week-year
                    $data[$field['name']]['data'] = $submissions->reduce(function($carry, $submission) use ($field) {
                        $found = Arr::first($submission->data, function($value) use ($field) {
                            return $value['name'] === $field['name'];
                        });

                        if ($found) {
                            $date = \Carbon\Carbon::parse($found['data']);
                            $key = $date->format('W-Y');
                            $carry[$key] = isset($carry[$key]) ? $carry[$key] + 1 : 1;
                        }
                        return $carry;
                    }, []);
            }
        }

        return Inertia::render('Admin/CustomForm/ShowCustomForm', [
            'form' => $customForm->load(['creator', 'updater']),
            'intel' => $data,
        ]);
    }
}
