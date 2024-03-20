<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRecruitmentRequest;
use App\Http\Requests\UpdateRecruitmentRequest;
use App\Models\Recruitment;
use App\Models\Role;
use App\Queries\Filters\FilterMultipleFields;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

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
