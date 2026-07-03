<?php

namespace App\Notifications\Application;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Application Submitted - ' . $this->application->application_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for your enquiry. Your assessment has been passed to our system and one of our assessors will contact you within 48 hours.')
            ->action('View Your Application', route('applications.show', $this->application))
            ->line('If you have any questions, please contact our support team.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'application_id' => $this->application->id,
            'application_number' => $this->application->application_number,
            'message' => 'Your loan application has been submitted successfully.',
            'action_url' => route('applications.show', $this->application),
            'submitted_at' => $this->application->submitted_at,
        ];
    }
}