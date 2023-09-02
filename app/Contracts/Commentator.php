<?php

namespace App\Contracts;

interface Commentator
{
    /**
     * Check if a comment for a specific model needs to be approved.
     */
    public function needsCommentApproval(mixed $model): bool;
}
