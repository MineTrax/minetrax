<?php

namespace App\Policies;

use App\Enums\RecruitmentFormStatus;
use App\Models\Recruitment;
use App\Models\User;

class RecruitmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('read recruitments')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Recruitment $recruitment): bool
    {
        if ($user->can('read recruitments')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('create recruitments')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Recruitment $recruitment): bool
    {
        if ($user->can('update recruitments')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Recruitment $recruitment): bool
    {
        if ($user->can('delete recruitments')) {
            return true;
        }

        return false;
    }

    public function viewPublic(?User $user, Recruitment $recruitment)
    {
        $invalidStatus = in_array($recruitment->status->value, [RecruitmentFormStatus::ARCHIVED, RecruitmentFormStatus::DRAFT]);
        if ($invalidStatus) {
            return false;
        }

        return true;
    }

    public function submit(User $user, Recruitment $recruitment)
    {
        // Bail if form is not active
        $isActive = $recruitment->status->value === RecruitmentFormStatus::ACTIVE;
        if (! $isActive) {
            return false;
        }

        // Bail if max_submission_per_user is reached
        $maxSubmissionPerUser = $recruitment->max_submission_per_user;
        if ($maxSubmissionPerUser) {
            $currentUserSubmissionCount = $recruitment->submissions()->where('user_id', $user->id)->count();
            if ($currentUserSubmissionCount >= $maxSubmissionPerUser) {
                return false;
            }
        }

        // Bail if in cooldown
        $cooldownSeconds = $recruitment->submission_cooldown_in_seconds;
        if ($cooldownSeconds) {
            $lastSubmission = $recruitment->submissions()->where('user_id', $user->id)->latest()->first();
            if ($lastSubmission) {
                $secondsSinceLastSubmission = now()->diffInSeconds($lastSubmission->updated_at);
                if ($secondsSinceLastSubmission < $cooldownSeconds) {
                    return false;
                }
            }
        }

        // Bail if submission is for verified users only and user is not verified
        $verifiedOnly = $recruitment->is_allow_only_verified_users;
        if ($verifiedOnly && ! $user->verified_at) {
            return false;
        }

        // Bail if submission is for linked users only and user has no linked player
        $linkedOnly = $recruitment->is_allow_only_player_linked_users;
        if ($linkedOnly) {
            $userHasLinkedPlayer = $user->players()->exists();
            if (! $userHasLinkedPlayer) {
                return false;
            }
        }

        return true;
    }
}
