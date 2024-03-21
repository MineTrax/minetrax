<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRecruitmentRequest;
use App\Http\Requests\UpdateRecruitmentRequest;
use App\Models\Recruitment;
use App\Models\Role;
use App\Queries\Filters\FilterMultipleFields;
use Arr;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Str;

class RecruitmentController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Recruitment::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $recruitments = QueryBuilder::for(Recruitment::class)
            ->allowedFilters([
                'id',
                'title',
                'slug',
                'status',
                'is_notify_staff_on_submission',
                'is_allow_messages_from_users',
                'related_role_id',
                'created_at',
                'created_by',
                AllowedFilter::custom('q', new FilterMultipleFields(['id', 'title', 'slug', 'description'])),
            ])
            ->allowedSorts([
                'id',
                'title',
                'slug',
                'status',
                'is_notify_staff_on_submission',
                'is_allow_messages_from_users',
                'related_role_id',
                'created_at',
                'open_submissions_count',
                'closed_submissions_count',
            ])
            ->withCount([
                'openSubmissions',
                'closedSubmissions',
            ])
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Recruitment/IndexRecruitment', [
            'recruitments' => $recruitments,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Recruitment::class);

        $roles = Role::orderBy('weight', 'desc')->pluck('display_name', 'id');

        return Inertia::render('Admin/Recruitment/CreateRecruitment', [
            'roles' => $roles,
        ]);
    }

    public function show(Recruitment $recruitment)
    {
        $this->authorize('view', $recruitment);

        $submissions = $recruitment->submissions()->latest()->get();

        $data = [];
        foreach ($recruitment->fields as $field) {
            $data[$field['name']] = [
                'label' => $field['label'],
                'type' => $field['type'],
                'data' => [],
            ];

            switch ($field['type']) {
                case 'text':
                case 'textarea':
                case 'email':
                case 'number':
                case 'password':
                case 'tel':
                case 'url':
                    $data[$field['name']]['data'] = $submissions->reduce(function ($carry, $submission) use ($field) {
                        $found = Arr::first($submission->data, function ($value) use ($field) {
                            return $value['name'] === $field['name'];
                        });

                        $carry[] = $found ? Str::limit($found['data'], 100) : null;

                        return $carry;
                    }, []);
                    break;
                case 'select':
                case 'radio':
                case 'multiselect':
                    $data[$field['name']]['data'] = $submissions->reduce(function ($carry, $submission) use ($field) {
                        $found = Arr::first($submission->data, function ($value) use ($field) {
                            return $value['name'] === $field['name'];
                        });

                        if ($found) {
                            if ($field['type'] === 'multiselect') {
                                foreach ($found['data'] as $value) {
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
                    $data[$field['name']]['data'] = $submissions->reduce(function ($carry, $submission) use ($field) {
                        $found = Arr::first($submission->data, function ($value) use ($field) {
                            return $value['name'] === $field['name'];
                        });

                        if ($found) {
                            $checked = $found['data'] == 1 ? __('Yes') : __('No');
                            $carry[$checked] = isset($carry[$checked]) ? $carry[$checked] + 1 : 1;
                        }

                        return $carry;
                    }, []);
                    break;
                case 'datetime-local':
                case 'date':
                    // group submissions count by month year
                    $data[$field['name']]['data'] = $submissions->reduce(function ($carry, $submission) use ($field) {
                        $found = Arr::first($submission->data, function ($value) use ($field) {
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
                    $data[$field['name']]['data'] = $submissions->reduce(function ($carry, $submission) use ($field) {
                        $found = Arr::first($submission->data, function ($value) use ($field) {
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
                    $data[$field['name']]['data'] = $submissions->reduce(function ($carry, $submission) use ($field) {
                        $found = Arr::first($submission->data, function ($value) use ($field) {
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
                    $data[$field['name']]['data'] = $submissions->reduce(function ($carry, $submission) use ($field) {
                        $found = Arr::first($submission->data, function ($value) use ($field) {
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

        $submissionCountByStatus = $submissions->countBy('status.value');

        return Inertia::render('Admin/Recruitment/ShowRecruitment', [
            'recruitment' => $recruitment
                ->load(['creator', 'updater', 'relatedRole'])
                ->loadCount([
                    'openSubmissions',
                    'closedSubmissions',
                ]),
            'intel' => $data,
            'submissionCountByStatus' => $submissionCountByStatus,
        ]);
    }

    public function store(CreateRecruitmentRequest $request)
    {
        Recruitment::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'status' => $request->status,
            'max_submission_per_user' => $request->max_submission_per_user,
            'submission_cooldown_in_seconds' => $request->submission_cooldown_in_seconds,
            'is_allow_only_player_linked_users' => $request->is_allow_only_player_linked_users,
            'is_allow_only_verified_users' => $request->is_allow_only_verified_users,
            'is_allow_messages_from_users' => $request->is_allow_messages_from_users,
            'min_role_weight_to_view_submission' => $request->min_role_weight_to_view_submission,
            'min_role_weight_to_vote_on_submission' => $request->min_role_weight_to_vote_on_submission,
            'min_role_weight_to_act_on_submission' => $request->min_role_weight_to_act_on_submission,
            'is_notify_staff_on_submission' => $request->is_notify_staff_on_submission,
            'related_role_id' => $request->related_role_id,
            'fields' => $request->fields,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.recruitment.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Recruitment Form is created successfully')]]);
    }

    public function edit(Recruitment $recruitment)
    {
        $this->authorize('update', $recruitment);

        $roles = Role::orderBy('weight', 'desc')->pluck('display_name', 'id');

        return Inertia::render('Admin/Recruitment/EditRecruitment', [
            'recruitment' => $recruitment,
            'roles' => $roles,
        ]);
    }

    public function update(UpdateRecruitmentRequest $request, Recruitment $recruitment)
    {
        $this->authorize('update', $recruitment);

        $recruitment->title = $request->title;
        $recruitment->slug = $request->slug;
        $recruitment->description = $request->description;
        $recruitment->status = $request->status;
        $recruitment->max_submission_per_user = $request->max_submission_per_user;
        $recruitment->submission_cooldown_in_seconds = $request->submission_cooldown_in_seconds;
        $recruitment->is_allow_only_player_linked_users = $request->is_allow_only_player_linked_users;
        $recruitment->is_allow_only_verified_users = $request->is_allow_only_verified_users;
        $recruitment->is_allow_messages_from_users = $request->is_allow_messages_from_users;
        $recruitment->min_role_weight_to_view_submission = $request->min_role_weight_to_view_submission;
        $recruitment->min_role_weight_to_vote_on_submission = $request->min_role_weight_to_vote_on_submission;
        $recruitment->min_role_weight_to_act_on_submission = $request->min_role_weight_to_act_on_submission;
        $recruitment->is_notify_staff_on_submission = $request->is_notify_staff_on_submission;
        $recruitment->related_role_id = $request->related_role_id;
        $recruitment->fields = $request->fields;
        $recruitment->updated_by = $request->user()->id;
        $recruitment->save();

        return redirect()->route('admin.recruitment.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Recruitment Form updated successfully')]]);
    }

    public function destroy(Recruitment $recruitment)
    {
        $this->authorize('delete', $recruitment);

        $recruitment->delete();

        return redirect()->route('admin.recruitment.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Recruitment Form has been deleted permanently')]]);
    }
}
