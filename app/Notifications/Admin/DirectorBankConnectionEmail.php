<?php

namespace App\Notifications\Admin;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DirectorBankConnectionEmail extends Notification
{
    use Queueable;

    public function __construct(
        public Application $application,
        public string $directorName,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Bank Statement Connection Required')
            ->greeting('Hello!')
            ->line('Dear ' . $this->directorName . ',')
            ->line('We require you to connect your bank account as part of the loan application process.')
            ->line('Application: ' . $this->application->application_number)
            ->action('Connect Bank Account', route('applications.show', $this->application))
            ->line('If you have any questions, please don\'t hesitate to contact us.');

        return $mail;
    }
}