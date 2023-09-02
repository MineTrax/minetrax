<?php

namespace App\Console\Commands;

use App\Models\User;
use Hash;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ResetUserPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:password:reset {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset password of given user with username and display new password in console.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $username = $this->argument('username');
        $user = User::where('username', $username)->first();

        if (! $user) {
            $this->error('No user found with provided username');

            return Command::FAILURE;
        }

        $randomPassword = Str::random(16);
        $user->forceFill([
            'password' => Hash::make($randomPassword),
        ])->save();

        $this->info("Reset Successful! New Password for {$username}: {$randomPassword}");

        return Command::SUCCESS;
    }
}
