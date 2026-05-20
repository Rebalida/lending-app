<?php
// app/Actions/Application/GenerateSubmissionPdf.php

namespace App\Actions\Application;

use App\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class GenerateSubmissionPdf
{
    /**
     * Generate the client-facing submission confirmation PDF.
     *
     * Loads all relationships needed by the view in a single eager-load call,
     * then renders and returns a downloadable PDF response.
     *
     * @param  Application  $application  The submitted application.
     * @return Response                   A PDF download response.
     */
    public function handle(Application $application): Response
    {
        $application->loadMissing([
            'user',
            'personalDetails.user',
            'borrowerInformation',
            'borrowerDirectors',
            'residentialAddresses',
            'employmentDetails',
            'livingExpenses',
            'directorAssets',
            'directorLiabilities',
            'companyAssets',
            'companyLiabilities',
            'accountantDetail',
            'declarations',
        ]);

        // Grab the final submission declaration — this contains the signature
        $declaration = $application->declarations
            ->where('declaration_type', 'final_submission')
            ->where('is_agreed', true)
            ->first();

        $pdf = Pdf::loadView('applications.pdf.submission', [
            'application' => $application,
            'declaration' => $declaration,
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('a4', 'portrait');

        $filename = 'loan-application-' . $application->application_number . '.pdf';

        return $pdf->download($filename);
    }
}