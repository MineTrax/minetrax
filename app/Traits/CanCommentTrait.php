<?php


namespace App\Traits;


trait CanCommentTrait
{
    public function needsCommentApproval($model): bool
    {
        return false;
    }
}
