<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mail:test {email? : Recipient address, defaults to MAIL_TO_ADDRESS}';

    /**
     * The console command description.
     */
    protected $description = 'Send a test email to verify mail configuration.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?? config('mail.to.address');

        if (! $email) {
            $this->components->error('No recipient. Pass an email argument or set MAIL_TO_ADDRESS.');

            return self::FAILURE;
        }

        $this->components->info('Sending test email.');

        $this->components->task("To {$email}", function () use ($email) {
            Mail::raw('Test email from '.config('app.name').'.', function ($message) use ($email) {
                $message->to($email)->subject('Test Email');
            });
        });

        $this->newLine();

        return self::SUCCESS;
    }
}
