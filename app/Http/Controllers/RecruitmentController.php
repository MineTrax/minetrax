<?php

namespace App\Http\Controllers;

use App\Enums\RecruitmentFormStatus;
use App\Enums\RecruitmentSubmissionStatus;
use App\Events\RecruitmentSubmissionCreated;
use App\Models\Recruitment;
use App\Queries\Filters\FilterMultipleFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RecruitmentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'title',
            'slug',
            'status',
            'is_allow_only_verified_users',
            'is_allow_only_player_linked_users',
            'created_at',
            'updated_at',
        ];
        $recruitments = QueryBuilder::for(Recruitment::class)
            ->whereIn('status', [RecruitmentFormStatus::ACTIVE, RecruitmentFormStatus::DISABLED])
            ->select($fields)
            ->allowedFilters([
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['title', 'description'])),
            ])
            ->allowedSorts($fields)
            ->defaultSort('-updated_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Recruitment/IndexRecruitment', [
            'recruitments' => $recruitments,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function show(Recruitment $recruitment, Request $request)
    {
        // Check if recruitment form can be viewed by given user/visitor.
        $this->authorize('viewPublic', $recruitment);

        $user = $request->user();

        $canSubmitForm = $user && Gate::allows('submit', $recruitment);
        $userVerified = $user && $user->verified_at;
        $userHasLinkedPlayer = $user && $user->players()->exists();
        $userSubmissionsCount = $user ? $recruitment->submissions()->where('user_id', $user->id)->count() : 0;
        $userLastSubmission = $user ? $recruitment->submissions()->where('user_id', $user->id)->latest()->first() : null;
        $secondsSinceLastSubmission = null;
        if ($userLastSubmission) {
            $secondsSinceLastSubmission = now()->diffInSeconds($userLastSubmission->updated_at);
        }

        $lastActiveSubmission = null;
        if ($userSubmissionsCount > 0) {
            $lastActiveSubmission = $recruitment->submissions()->where('user_id', $user->id)->whereIn('status', [
                RecruitmentSubmissionStatus::PENDING,
                RecruitmentSubmissionStatus::ONHOLD,
                RecruitmentSubmissionStatus::INPROGRESS,
            ])->latest()->first();
        }
        $userLastApprovedSubmission = null;
        if ($userSubmissionsCount > 0) {
            $userLastApprovedSubmission = $recruitment->submissions()
                ->where('user_id', $user->id)
                ->where('status', RecruitmentSubmissionStatus::APPROVED)->latest()->first();
        }

        return Inertia::render('Recruitment/ShowRecruitment', [
            'recruitment' => $recruitment->append('description_html'),
            'currentUserCanSubmit' => $canSubmitForm,

            'userVerified' => $userVerified,
            'userHasLinkedPlayer' => $userHasLinkedPlayer,
            'userSubmissionsCount' => $userSubmissionsCount,
            'secondsSinceLastSubmission' => $secondsSinceLastSubmission,
            'userLastActiveSubmission' => $lastActiveSubmission,
            'userLastApprovedSubmission' => $userLastApprovedSubmission,
        ]);
    }

    public function submit(Request $request, Recruitment $recruitment)
    {
        $this->authorize('submit', $recruitment);

        // Map form fields to its keys
        $data = collect($recruitment->fields)->map(function ($field) use ($request) {
            $requestData = $request->all();
            $field['data'] = $requestData[$field['name']] ?? null;

            return $field;
        })->toArray();

        $submission = $recruitment->submissions()->create([
            'user_id' => $request->user()?->id,
            'status' => RecruitmentSubmissionStatus::PENDING,
            'data' => $data,
        ]);
        RecruitmentSubmissionCreated::dispatch($submission);

        return redirect()->route('recruitment-submission.show', [
            'recruitment' => $recruitment->slug,
            'submission' => $submission->id,
        ])
            ->with(['toast' => ['type' => 'success', 'title' => __('Applied Successfully!'), 'body' => __('Application has been submitted successfully.')]]);
    }
}
