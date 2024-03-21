<?php

namespace App\Policies;

use App\Enums\RecruitmentSubmissionStatus;
use App\Models\RecruitmentSubmission;
use App\Models\User;

class RecruitmentSubmissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('read recruitment_submissions')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RecruitmentSubmission $recruitmentSubmission): bool
    {
        if ($recruitmentSubmission->user_id === $user->id) {
            return true;
        }

        $roleWeight = $recruitmentSubmission->recruitment->min_role_weight_to_view_submission;
        $userRoleWeight = $user->maxRoleWeight();
        if ($roleWeight && $userRoleWeight < $roleWeight) {
            return false;
        }

        if ($user->can('read recruitment_submissions')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RecruitmentSubmission $recruitmentSubmission): bool
    {
        if (in_array($recruitmentSubmission->status, [
            RecruitmentSubmissionStatus::PENDING,
            RecruitmentSubmissionStatus::ONHOLD,
            RecruitmentSubmissionStatus::INPROGRESS,
        ])) {
            return false;
        }

        if ($user->can('delete recruitment_submissions')) {
            return true;
        }

        return false;
    }

    public function actOn(User $user, RecruitmentSubmission $recruitmentSubmission): bool
    {
        if (in_array($recruitmentSubmission->status, [
            RecruitmentSubmissionStatus::WITHDRAWN,
            RecruitmentSubmissionStatus::REJECTED,
            RecruitmentSubmissionStatus::APPROVED,
        ])) {
            return false;
        }

        $roleWeight = $recruitmentSubmission->recruitment->min_role_weight_to_act_on_submission;
        $userRoleWeight = $user->maxRoleWeight();
        if ($roleWeight && $userRoleWeight < $roleWeight) {
            return false;
        }

        if ($user->can('acton recruitment_submissions')) {
            return true;
        }

        return false;
    }

    public function withdraw(User $user, RecruitmentSubmission $recruitmentSubmission): bool
    {
        if (in_array($recruitmentSubmission->status, [
            RecruitmentSubmissionStatus::WITHDRAWN,
            RecruitmentSubmissionStatus::REJECTED,
            RecruitmentSubmissionStatus::APPROVED,
        ])) {
            return false;
        }

        if ($user->id === $recruitmentSubmission->user_id) {
            return true;
        }

        return false;
    }

    public function sendMessage(User $user, RecruitmentSubmission $recruitmentSubmission): bool
    {
        if (in_array($recruitmentSubmission->status, [
            RecruitmentSubmissionStatus::WITHDRAWN,
            RecruitmentSubmissionStatus::REJECTED,
            RecruitmentSubmissionStatus::APPROVED,
        ])) {
            return false;
        }

        if (! $recruitmentSubmission->recruitment->is_allow_messages_from_users) {
            return false;
        }

        if ($user->id === $recruitmentSubmission->user_id) {
            return true;
        }

        return false;
    }
}
