<?php

namespace App\Http\Controllers;

use App\Enums\CommentType;
use App\Enums\RecruitmentSubmissionStatus;
use App\Events\RecruitmentSubmissionCommentCreated;
use App\Events\RecruitmentSubmissionStatusChanged;
use App\Models\Recruitment;
use App\Models\RecruitmentSubmission;
use App\Queries\Filters\FilterMultipleFields;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RecruitmentSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'form' => 'sometimes|nullable|array',
            'form.*' => 'sometimes|nullable|integer|exists:recruitments,id',
        ]);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'recruitment_id',
            'status',
            'user_id',
            'created_at',
            'updated_at',
        ];
        $selectedRecruitments = $request->query('forms') ?? null;
        $submissions = QueryBuilder::for(RecruitmentSubmission::class)
            ->where('user_id', $request->user()->id)
            ->when($selectedRecruitments, function ($query, $selectedRecruitments) {
                $query->whereIn('recruitment_id', $selectedRecruitments);
            })
            ->with(['recruitment:id,title,status,slug'])
            ->select($fields)
            ->allowedFilters([
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['data', 'status'])),
            ])
            ->allowedSorts($fields)
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('RecruitmentSubmission/IndexRecruitmentSubmission', [
            'submissions' => $submissions,
            'filters' => request()->all(['perPage', 'sort', 'filter', 'forms']),
        ]);
    }

    public function show(Request $request, Recruitment $recruitment, RecruitmentSubmission $submission)
    {
        $this->authorize('view', $submission);
        if ($submission->user_id != $request->user()->id) {
            return abort(403);
        }

        $submission->load([
            'user:id,name,username',
            'recruitment:id,title,status,slug,is_allow_messages_from_users,submission_cooldown_in_seconds',
            'lastActor:id,name,username',
        ]);
        $submission['i_can_withdraw'] = $request->user()->can('withdraw', $submission)
                                                 && in_array($submission->status, [
                                                     RecruitmentSubmissionStatus::PENDING,
                                                     RecruitmentSubmissionStatus::INPROGRESS,
                                                     RecruitmentSubmissionStatus::ONHOLD,
                                                 ]);
        $submission['i_can_send_message'] = $request->user()->can('sendMessage', $submission)
                                                 && $submission->recruitment->is_allow_messages_from_users
                                                 && in_array($submission->status, [
                                                     RecruitmentSubmissionStatus::PENDING,
                                                     RecruitmentSubmissionStatus::INPROGRESS,
                                                     RecruitmentSubmissionStatus::ONHOLD,
                                                 ]);

        return Inertia::render('RecruitmentSubmission/ShowRecruitmentSubmission', [
            'submission' => $submission,
        ]);
    }

    public function withdraw(Request $request, Recruitment $recruitment, RecruitmentSubmission $submission)
    {
        $this->authorize('withdraw', $submission);

        $request->validate([
            'reason' => 'required|max:2000|string',
        ]);

        $previousStatus = $submission->status;
        $submission->update([
            'status' => RecruitmentSubmissionStatus::WITHDRAWN,
            'last_act_by' => $request->user()->id,
            'last_act_at' => now(),
            'last_act_reason' => $request->reason ?? null,
        ]);

        // Create special comment for this action.
        $submission->comment(RecruitmentSubmissionStatus::WITHDRAWN, CommentType::RECRUITMENT_ACTION);

        // Fire event
        RecruitmentSubmissionStatusChanged::dispatch($submission, $request->user(), $previousStatus);

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Withdraw Successful'), 'body' => __('Recruitment Submission withdrawn successfully')]]);
    }

    public function indexMessages(Request $request, Recruitment $recruitment, RecruitmentSubmission $submission)
    {
        $this->authorize('view', $submission);

        $afterId = $request->after;
        $messages = $submission->comments()
            ->whereIn('type', [
                CommentType::RECRUITMENT_APPLICANT_MESSAGE,
                CommentType::RECRUITMENT_STAFF_MESSAGE,
                CommentType::RECRUITMENT_ACTION,
            ])
            ->with('commentator:id,username,name,profile_photo_path,verified_at')
            ->orderBy('id');

        if ($afterId) {
            $messages->where('id', '>', $afterId);
        }

        return $messages->get();
    }

    public function postMessage(Request $request, Recruitment $recruitment, RecruitmentSubmission $submission)
    {
        $this->authorize('sendMessage', $submission);

        $request->validate([
            'message' => 'required|max:2000',
        ]);

        $comment = $submission->comment($request->message, CommentType::RECRUITMENT_APPLICANT_MESSAGE);

        // Fire event
        RecruitmentSubmissionCommentCreated::dispatch($comment, $submission, $request->user());

        return response()->json([
            'status' => 'ok',
            'data' => $comment->load('commentator:id,username,name,profile_photo_path,verified_at'),
        ]);
    }
}
