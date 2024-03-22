<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommentType;
use App\Enums\RecruitmentFormStatus;
use App\Enums\RecruitmentSubmissionStatus;
use App\Events\RecruitmentSubmissionCommentCreated;
use App\Events\RecruitmentSubmissionStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Recruitment;
use App\Models\RecruitmentSubmission;
use App\Queries\Filters\FilterMultipleFields;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RecruitmentSubmissionController extends Controller
{
    public function indexOpen(Request $request)
    {
        $this->authorize('viewAny', RecruitmentSubmission::class);
        $request->validate([
            'form' => 'sometimes|nullable|array',
            'form.*' => 'sometimes|nullable|integer|exists:recruitments,id',
        ]);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        // Only those forms which this staff member has access to.
        $maxRoleWeightOfStaffMember = $request->user()->maxRoleWeight();
        $recruitments = Recruitment::select(['id', 'title'])
            ->where('status', '!=', RecruitmentFormStatus::DRAFT)
            ->where(function ($q) use ($maxRoleWeightOfStaffMember) {
                $q->where('min_role_weight_to_view_submission', '<=', $maxRoleWeightOfStaffMember)
                    ->orWhereNull('min_role_weight_to_view_submission');
            })
            ->orderByDesc('id')
            ->get()->pluck('title', 'id');

        $fields = [
            'id',
            'recruitment_id',
            'status',
            'user_id',
            'created_at',
        ];
        $selectedRecruitments = $request->query('forms') ?? null;
        $submissions = QueryBuilder::for(RecruitmentSubmission::class)
            ->whereIn('status', [
                RecruitmentSubmissionStatus::PENDING,
                RecruitmentSubmissionStatus::INPROGRESS,
                RecruitmentSubmissionStatus::ONHOLD,
            ])
            ->when($selectedRecruitments, function ($query, $selectedRecruitments) {
                $query->whereIn('recruitment_id', $selectedRecruitments);
            })
            ->whereIn('recruitment_id', $recruitments->keys())
            ->with(['user:id,name,username', 'recruitment'])
            ->select($fields)
            ->allowedFilters([
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['data', 'status'])),
            ])
            ->allowedSorts($fields)
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/RecruitmentSubmission/IndexRecruitmentSubmission', [
            'submissions' => $submissions,
            'closed' => false,
            'forms' => $recruitments,
            'filters' => request()->all(['perPage', 'sort', 'filter', 'forms']),
        ]);
    }

    public function indexClosed(Request $request)
    {
        $this->authorize('viewAny', RecruitmentSubmission::class);
        $request->validate([
            'form' => 'sometimes|nullable|array',
            'form.*' => 'sometimes|nullable|integer|exists:recruitments,id',
        ]);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        // Only those forms which this staff member has access to.
        $maxRoleWeightOfStaffMember = $request->user()->maxRoleWeight();
        $recruitments = Recruitment::select(['id', 'title'])
            ->where('status', '!=', RecruitmentFormStatus::DRAFT)
            ->where(function ($q) use ($maxRoleWeightOfStaffMember) {
                $q->where('min_role_weight_to_view_submission', '<=', $maxRoleWeightOfStaffMember)
                    ->orWhereNull('min_role_weight_to_view_submission');
            })
            ->orderByDesc('id')
            ->get()->pluck('title', 'id');

        $fields = [
            'id',
            'recruitment_id',
            'status',
            'user_id',
            'created_at',
        ];
        $selectedRecruitments = $request->query('forms') ?? null;
        $submissions = QueryBuilder::for(RecruitmentSubmission::class)
            ->whereIn('status', [
                RecruitmentSubmissionStatus::APPROVED,
                RecruitmentSubmissionStatus::REJECTED,
                RecruitmentSubmissionStatus::WITHDRAWN,
            ])
            ->when($selectedRecruitments, function ($query, $selectedRecruitments) {
                $query->whereIn('recruitment_id', $selectedRecruitments);
            })
            ->whereIn('recruitment_id', $recruitments->keys())
            ->with(['user:id,name,username', 'recruitment'])
            ->select($fields)
            ->allowedFilters([
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['data', 'status'])),
            ])
            ->allowedSorts($fields)
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/RecruitmentSubmission/IndexRecruitmentSubmission', [
            'submissions' => $submissions,
            'closed' => true,
            'forms' => $recruitments,
            'filters' => request()->all(['perPage', 'sort', 'filter', 'forms']),
        ]);
    }

    public function show(Request $request, RecruitmentSubmission $submission)
    {
        $this->authorize('view', $submission);

        $submission->load([
            'user:id,name,username',
            'recruitment',
            'lastActor:id,name,username',
        ]);
        $submission['i_can_act'] = $request->user()->can('actOn', $submission)
                                            && in_array($submission->status, [
                                                RecruitmentSubmissionStatus::PENDING,
                                                RecruitmentSubmissionStatus::INPROGRESS,
                                                RecruitmentSubmissionStatus::ONHOLD,
                                            ]);
        $submission['i_can_delete'] = $request->user()->can('delete', $submission)
                                            && in_array($submission->status, [
                                                RecruitmentSubmissionStatus::APPROVED,
                                                RecruitmentSubmissionStatus::REJECTED,
                                                RecruitmentSubmissionStatus::WITHDRAWN,
                                            ]);
        $submission['i_can_send_message'] = in_array($submission->status, [
            RecruitmentSubmissionStatus::PENDING,
            RecruitmentSubmissionStatus::INPROGRESS,
            RecruitmentSubmissionStatus::ONHOLD,
        ]);

        return Inertia::render('Admin/RecruitmentSubmission/ShowRecruitmentSubmission', [
            'submission' => $submission,
        ]);
    }

    public function destroy(Request $request, RecruitmentSubmission $submission)
    {
        $this->authorize('delete', $submission);

        $submission->delete();

        return redirect()->route('admin.recruitment-submission.index-closed')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Recruitment Submission deleted successfully')]]);
    }

    public function act(Request $request, RecruitmentSubmission $submission)
    {
        $this->authorize('actOn', $submission);

        $request->validate([
            'action' => 'required|in:'.implode(',', [
                RecruitmentSubmissionStatus::INPROGRESS,
                RecruitmentSubmissionStatus::ONHOLD,
                RecruitmentSubmissionStatus::APPROVED,
                RecruitmentSubmissionStatus::REJECTED,
            ]),
            'reason' => 'nullable|required_if:action,rejected|max:2000|string',
        ]);

        $previousStatus = $submission->status;
        $submission->update([
            'status' => $request->action,
            'last_act_by' => $request->user()->id,
            'last_act_at' => now(),
            'last_act_reason' => $request->reason ?? null,
        ]);

        // Create special comment for this action.
        $submission->comment($request->action, CommentType::RECRUITMENT_ACTION);

        // Fire event
        RecruitmentSubmissionStatusChanged::dispatch($submission, $request->user(), $previousStatus);

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Action Successful'), 'body' => __('Recruitment Submission action has been completed successfully')]]);
    }

    public function indexMessages(Request $request, RecruitmentSubmission $submission)
    {
        $this->authorize('view', $submission);

        $afterId = $request->after;
        $messages = $submission->comments()
            ->with('commentator:id,username,name,profile_photo_path,verified_at,settings')
            ->orderBy('id');

        if ($afterId) {
            $messages->where('id', '>', $afterId);
        }

        return $messages->get();
    }

    public function postMessage(Request $request, RecruitmentSubmission $submission)
    {
        $request->validate([
            'message' => 'required|max:2000',
            'type' => ['required', 'in:'.implode(',', [CommentType::RECRUITMENT_STAFF_WHISPER, CommentType::RECRUITMENT_STAFF_MESSAGE])],
        ]);

        $comment = $submission->comment($request->message, $request->type);

        // Fire event
        RecruitmentSubmissionCommentCreated::dispatch($comment, $submission, $request->user());

        return response()->json([
            'status' => 'ok',
            'data' => $comment->load('commentator:id,username,name,profile_photo_path,verified_at,settings'),
        ]);
    }

    public function deleteMessage(RecruitmentSubmission $submission, Comment $message)
    {
        $this->authorize('deleteForRecruitmentSubmission', $message);

        $message->delete();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Message Successfully'), 'body' => __('Message has been deleted successfully')]]);
    }
}
