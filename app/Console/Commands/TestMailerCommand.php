<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test:send {email : The email address to send test mail to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test if mail configuration is working correctly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $this->info('Testing mail configuration...');
        $this->newLine();

        // Display current mail configuration
        $this->table(
            ['Setting', 'Value'],
            [
                ['MAIL_MAILER', config('mail.default')],
                ['MAIL_HOST', config('mail.mailers.smtp.host')],
                ['MAIL_PORT', config('mail.mailers.smtp.port')],
                ['MAIL_USERNAME', config('mail.mailers.smtp.username') ? '(set)' : '(not set)'],
                ['MAIL_PASSWORD', config('mail.mailers.smtp.password') ? '(set)' : '(not set)'],
                ['MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption') ?: 'null'],
                ['MAIL_FROM_ADDRESS', config('mail.from.address')],
                ['MAIL_FROM_NAME', config('mail.from.name')],
            ]
        );

        $this->newLine();
        $this->info("Sending test email to: {$email}");

        try {
            Mail::raw('This is a test email from MineTrax to verify your mail configuration is working correctly. If you received this email, your mail settings are configured properly!', function ($message) use ($email) {
                $message->to($email)
                    ->subject(config('app.name') . ' Test Email - ' . now()->format('Y-m-d H:i:s'));
            });

            $this->newLine();
            $this->info('✓ Test email sent successfully!');
            $this->info("Please check the inbox of: {$email}");
            $this->warn('Note: Also check your spam/junk folder if you don\'t see the email.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('✗ Failed to send test email!');
            $this->newLine();
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();

            if ($this->option('verbose')) {
                $this->error('Stack Trace:');
                $this->line($e->getTraceAsString());
            } else {
                $this->warn('Tip: Run with -v flag for more details: php artisan mail:test ' . $email . ' -v');
            }

            return Command::FAILURE;
        }
    }
}
