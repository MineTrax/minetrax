<?php

namespace App\Policies;

use App\Enums\CustomFormStatus;
use App\Models\CustomForm;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomFormPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('read custom_forms')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CustomForm $customForm): bool
    {
        if ($user->can('read custom_forms')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('create custom_forms')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CustomForm $customForm): bool
    {
        if ($user->can('update custom_forms')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomForm $customForm): bool
    {
        if ($user->can('delete custom_forms')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CustomForm $customForm): bool
    {
        if ($user->can('delete custom_forms')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CustomForm $customForm): bool
    {
        if ($user->can('delete custom_forms')) {
            return true;
        }

        return false;
    }

    public function viewPublic(?User $user, CustomForm $customForm)
    {
        $invalidStatus = in_array($customForm->status->value, [CustomFormStatus::ARCHIVED, CustomFormStatus::DRAFT]);
        if ($invalidStatus) {
            return false;
        }

        $canCreateSubmission = $customForm->can_create_submission;
        if ($canCreateSubmission === 'anyone' || $canCreateSubmission === 'auth') {
            return true;
        }

        if ($canCreateSubmission === 'staff' && $user && $user->isStaffMember()) {
            return true;
        }

        return false;
    }

    public function submit(?User $user, CustomForm $customForm)
    {
        // Bail if form is not active
        $isActive = $customForm->status->value === CustomFormStatus::ACTIVE;
        if (! $isActive) {
            return false;
        }

        // Bail if user is not logged in and can_create_submission is auth or staff
        if (in_array($customForm->can_create_submission, ['auth', 'staff']) && ! $user) {
            return false;
        }

        // Bail if max_submission_per_user is reached
        $maxSubmissionPerUser = $customForm->max_submission_per_user;
        if (in_array($customForm->can_create_submission, ['auth', 'staff']) && $maxSubmissionPerUser) {
            $currentUserSubmissionCount = $customForm->submissions()->where('user_id', $user->id)->count();
            if ($currentUserSubmissionCount >= $maxSubmissionPerUser) {
                return false;
            }
        }

        // Bail if user is not staff and can_create_submission is staff
        if ($customForm->can_create_submission === 'staff' && ! $user->isStaffMember()) {
            return false;
        }

        return true;
    }
}
