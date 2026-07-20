<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Notifications\Admin\LoanDeedNotification;
use App\Support\LoanDeedData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class LoanDeedController extends Controller
{
    /**
     * Show the admin loan deed editor.
     * Pre-fills from application data via LoanDeedData.
     */
    public function show(Application $application): View
    {
        abort_if(
            $application->status !== Application::STATUS_APPROVED,
            403,
            'Loan deed is only available for approved applications.'
        );

        $deedData = LoanDeedData::for($application);

        return view('admin.applications.loan-deed', compact('application', 'deedData'));
    }

    /**
     * Save the loan deed data and stamp loan_deed_requested_at.
     */
    public function store(Request $request, Application $application): RedirectResponse
    {
        abort_if(
            $application->status !== Application::STATUS_APPROVED,
            403
        );

        abort_if($application->isLoanDeedSigned(), 403, 'Loan deed already signed.');

        $validated = $request->validate([
            // Parties
            'borrower_name'       => 'required|string|max:255',
            'borrower_abn'        => 'nullable|string|max:50',
            'borrower_acn'        => 'nullable|string|max:50',
            'borrower_address'    => 'required|string|max:500',
            'borrower_email'      => 'required|email|max:255',
            'borrower_phone'      => 'nullable|string|max:50',
            'guarantor_name'      => 'nullable|string|max:255',
            'guarantor_email'     => 'nullable|email|max:255',
            'guarantor_address'   => 'nullable|string|max:500',

            // Financial table
            'principal_sum'          => 'required|string|max:50',
            'annual_percentage_rate' => 'required|string|max:50',
            'total_interest'         => 'nullable|string|max:50',
            'repayment_cycle'        => 'required|string|max:50',
            'total_repayments'       => 'nullable|string|max:50',
            'amount_per_repayment'   => 'nullable|string|max:50',
            'total_repayment_amount' => 'nullable|string|max:50',
            'first_repayment_date'   => 'nullable|string|max:50',

            // Fees
            'application_fee'            => 'nullable|string|max:50',
            'security_search_fee'        => 'nullable|string|max:50',
            'legal_fee'                  => 'nullable|string|max:50',
            'security_registration_fee'  => 'nullable|string|max:50',
            'valuation_fee'              => 'nullable|string|max:50',
            'monthly_account_fee'        => 'nullable|string|max:50',
            'annual_review_fee'          => 'nullable|string|max:50',
            'establishment_fee'          => 'nullable|string|max:50',
            'exit_fee'                   => 'nullable|string|max:100',
            'break_cost'                 => 'nullable|string|max:100',

            // Schedule values
            'commencement_date'     => 'nullable|string|max:255',
            'repayment_date'        => 'nullable|string|max:255',
            'disclosure_date'       => 'nullable|string|max:50',
            'interest_rate'         => 'required|string|max:50',
            'default_rate'          => 'nullable|string|max:50',
            'lower_rate'            => 'nullable|string|max:50',
            'loan_purpose'          => 'required|string|max:255',
            'permitted_encumbrance' => 'nullable|string|max:500',
            'secured_land'          => 'nullable|string|max:500',

            // Schedule 2 — repayment schedule
            'repayment_schedule'          => 'nullable|array',
            'repayment_schedule.*.date'   => 'nullable|string|max:50',
            'repayment_schedule.*.amount' => 'nullable|string|max:50',

            // Witness (optional)
            'witness_name'       => 'nullable|string|max:255',
            'witness_occupation' => 'nullable|string|max:255',
            'witness_signature'  => 'nullable|string',
        ]);

        // Drop empty repayment schedule rows
        $validated['repayment_schedule'] = array_values(array_filter(
            $validated['repayment_schedule'] ?? [],
            fn ($row) => !empty($row['date']) || !empty($row['amount'])
        ));

        // Preserve non-form keys already persisted (directors snapshot, signatures)
        $existing = $application->loan_deed_data ?? [];

        $application->update([
            'loan_deed_data'         => array_merge($existing, $validated),
            'loan_deed_requested_at' => $application->loan_deed_requested_at ?? now(),
        ]);

        ActivityLog::logActivity(
            'loan_deed_saved',
            'Loan deed prepared by admin',
            $application
        );

        return back()->with('success', 'Loan deed saved successfully.');
    }

    /**
     * Send the loan deed link to the client and stamp loan_deed_request_url.
     */
    public function send(Application $application): RedirectResponse
    {
        abort_if(! $application->hasLoanDeedData(), 403, 'Save the loan deed before sending.');
        abort_if($application->isLoanDeedSigned(), 403, 'Loan deed already signed.');
        abort_if(
            $application->requiresGuarantor() && ! $application->isGuarantorFormSigned(),
            403,
            'The guarantor form must be signed before the loan deed can be sent.'
        );

        $signedUrl = URL::signedRoute(
            'applications.loan-deed.client.show',
            ['application' => $application->id],
        );

        $application->update([
            'loan_deed_request_url' => $signedUrl,
        ]);

        $application->user->notify(new LoanDeedNotification($application, $signedUrl));

        ActivityLog::logActivity(
            'loan_deed_sent',
            'Loan deed link sent to client',
            $application
        );

        return back()->with('success', 'Loan deed sent to client successfully.');
    }

    /**
     * View the signed loan deed (read-only HTML render).
     */
    public function viewSigned(Application $application): View
    {
        abort_if(! $application->isLoanDeedSigned(), 404);

        $deedData = LoanDeedData::for($application);

        return view('admin.applications.loan-deed-signed', compact('application', 'deedData'));
    }

    /**
     * Download the signed loan deed as a PDF.
     * Rendered on demand from persisted data — never from request input.
     */
    public function downloadPdf(Application $application): Response
    {
        abort_if(! $application->isLoanDeedSigned(), 404);
 
        $deedData = LoanDeedData::for($application);
 
        $pdf = Pdf::loadView('admin.applications.pdf.loan-deed', [
            'application' => $application,
            'deedData'    => $deedData,
            'generatedAt' => now(),
        ]);
 
        $pdf->setPaper('a4', 'portrait');
 
        ActivityLog::logActivity(
            'document_generated',
            'Loan deed PDF downloaded',
            $application,
            null,
            ['doc_type' => 'loan_deed', 'doc_label' => 'Loan Deed PDF']
        );
 
        return $pdf->download('loan-deed-' . $application->application_number . '.pdf');
    }
}
