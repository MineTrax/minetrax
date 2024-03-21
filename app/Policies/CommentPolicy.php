<?php

namespace App\Policies;

use App\Enums\CommentType;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(User $user, Comment $comment)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        // @TODO: Check for ban or mute
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update(User $user, Comment $comment)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete(User $user, Comment $comment)
    {
        if ($user->can('delete comments')) {
            return true;
        }

        return $user->id === $comment->user_id;
    }

    public function deleteForRecruitmentSubmission(User $user, Comment $comment)
    {
        if (in_array($comment->type, [CommentType::RECRUITMENT_STAFF_WHISPER, CommentType::RECRUITMENT_STAFF_MESSAGE, CommentType::RECRUITMENT_APPLICANT_MESSAGE]) && $user->can('delete recruitment_submission_messages')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return mixed
     */
    public function restore(User $user, Comment $comment)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Comment $comment)
    {
        //
    }
}
