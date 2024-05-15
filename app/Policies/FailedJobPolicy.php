<?php

namespace App\Policies;

use App\Models\FailedJob;
use App\Models\User;

class FailedJobPolicy
{
    public function viewAny(User $user)
    {
        if ($user->can('read failed_jobs')) {
            return true;
        }
    }

    public function delete(User $user, FailedJob $job)
    {
        if ($user->can('delete failed_jobs')) {
            return true;
        }
    }

    public function retry(User $user, FailedJob $job)
    {
        if ($user->can('retry failed_jobs')) {
            return true;
        }
    }
}
