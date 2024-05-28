<?php

namespace App\Policies;

use App\Enums\CommandQueueStatus;
use App\Models\CommandQueue;
use App\Models\User;

class CommandQueuePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->can('read command_queues')) {
            return true;
        }

        return false;
    }

    public function view(User $user, CommandQueue $commandQueue): bool
    {
        if ($user->can('read command_queues')) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->can('create command_queues')) {
            return true;
        }

        return false;
    }

    public function delete(User $user, CommandQueue $commandQueue): bool
    {
        if ($commandQueue->status->value === CommandQueueStatus::RUNNING) {
            return false;
        }

        if ($user->can('delete command_queues')) {
            return true;
        }

        return false;
    }

    public function retry(User $user, CommandQueue $commandQueue): bool
    {
        if (
            $user->can('create command_queues') &&
            in_array($commandQueue->status->value, [CommandQueueStatus::FAILED, CommandQueueStatus::CANCELLED])
        ) {
            return true;
        }

        return false;
    }
}
