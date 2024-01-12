<?php

namespace App\Policies;

use App\Models\CustomFormSubmission;
use App\Models\User;

class CustomFormSubmissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('read custom_form_submissions')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CustomFormSubmission $customFormSubmission): bool
    {
        // Check for custom form role weight.
        $roleWeight = $customFormSubmission->customForm->min_role_weight_to_view_submission;
        $userRoleWeight = $user->maxRoleWeight();
        if ($roleWeight && $userRoleWeight < $roleWeight) {
            return false;
        }

        if ($user->can('read custom_form_submissions')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomFormSubmission $customFormSubmission): bool
    {
        if ($user->can('delete custom_form_submissions')) {
            return true;
        }

        return false;
    }
}
