<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Notifications\Admin\BusinessDeclarationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class BusinessDeclarationController extends Controller
{
    /**
     * Send a signed URL to the client to complete the business declaration.
     */
    public function send(Application $application): RedirectResponse
    {
        abort_if($application->status !== Application::STATUS_APPROVED, 403);
        abort_if(! $application->isGuarantorFormSigned(), 403, 'Guarantor form must be signed first.');
        abort_if($application->business_declaration_signed_at, 403, 'Declaration already signed.');

        $signedUrl = URL::signedRoute(
            'applications.business-declaration.show',
            ['application' => $application->id],
        );

        $application->update([
            'business_declaration_sent_at' => now(),
        ]);

        $application->user->notify(new BusinessDeclarationNotification($application, $signedUrl));

        ActivityLog::logActivity(
            'business_declaration_sent',
            'Business declaration sent to client',
            $application
        );

        return back()->with('success', 'Business declaration sent to client successfully.');
    }

    /**
     * Show the signed declaration to the admin.
     */
    public function view(Application $application): View
    {
        abort_if(! $application->business_declaration_signed_at, 404);

        return view('admin.applications.business-declaration-signed', compact('application'));
    }
}