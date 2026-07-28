<?php

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Communication;
use App\Models\ActivityLog;
use App\Notifications\Admin\DirectorBankConnectionEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;

class EmailDirectorsController extends Controller
{
    public function send(Application $application): JsonResponse
    {
        $directors = $application->borrowerDirectors()
            ->whereNotNull('email')
            ->get();

        if ($directors->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No directors with an email address found.',
            ], 422);
        }

        $subject = 'Bank Statement Connection Required';
        $message = 'Please connect your bank account. [Dummy message for now]';

        foreach ($directors as $director) {
        Notification::route('mail', $director->email)
            ->notify(new DirectorBankConnectionEmail($application, $director->full_name));

            Communication::create([
                'application_id' => $application->id,
                'user_id'        => auth()->id(),
                'type'           => 'email_out',
                'direction'      => 'outbound',
                'from_address'   => config('mail.from.address'),
                'to_address'     => $director->email,
                'subject'        => $subject,
                'body'           => $message,
                'status'         => 'sent',
                'sent_at'        => now(),
            ]);
        }

        ActivityLog::logActivity('email_sent', 'Bank connection email sent to directors', $application);

        return response()->json([
            'success' => true,
            'message' => 'Emails sent to ' . $directors->count() . ' director(s).',
        ]);
    }
}