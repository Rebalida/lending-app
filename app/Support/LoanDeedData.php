<?php

namespace App\Support;

use App\Models\Application;

/**
 * Single source of truth for the Loan Deed data payload.
 *
 * Merges prefill defaults derived from the Application and its relations
 * with the persisted `loan_deed_data` JSON column (persisted values win).
 * Every renderer (admin editor defaults, client review page, admin signed
 * view, DomPDF template) consumes this class — the deed is never rendered
 * from request input.
 */
class LoanDeedData
{
    public static function for(Application $application): array
    {
        $application->loadMissing([
            'user',
            'personalDetails',
            'borrowerInformation',
            'borrowerDirectors',
            'residentialAddresses',
        ]);

        return array_merge(
            self::defaults($application),
            $application->loan_deed_data ?? []
        );
    }

    private static function defaults(Application $application): array
    {
        $borrower  = $application->borrowerInformation;
        $personal  = $application->personalDetails;
        $directors = $application->borrowerDirectors;

        $currentAddress = $application->residentialAddresses
            ->sortByDesc('start_date')
            ->first();

        $guarantorDirector = $directors->firstWhere('is_guarantor', true);
        $guarantorData     = $application->guarantor_data ?? [];

        return [
            // Parties — Borrower
            'borrower_name'       => $borrower?->borrower_name ?? $personal?->full_name ?? $application->user->name,
            'borrower_abn'        => $borrower?->abn ?? '',
            'borrower_acn'        => '', // no ACN column exists — admin-editable
            'borrower_address'    => $currentAddress?->full_address ?? '',
            'borrower_email'      => $application->user->email,
            'borrower_phone'      => $personal?->mobile_phone ?? '',

            // Parties — Guarantor
            'guarantor_name'      => $guarantorDirector?->full_name
                ?? ($guarantorData['guarantor_full_name'] ?? ''),
            'guarantor_email'     => $guarantorDirector?->email
                ?? ($guarantorData['guarantor_email'] ?? ''),
            'guarantor_address'   => $guarantorData['guarantor_residential'] ?? '',

            // Directors (execution page)
            'directors'           => $directors->map(fn ($d) => [
                'full_name' => $d->full_name,
                'email'     => $d->email,
            ])->values()->all(),

            // Financial table
            'principal_sum'       => '$' . number_format((float) $application->loan_amount, 2),
            'annual_percentage_rate' => '',
            'total_interest'      => '',
            'repayment_cycle'     => 'Weekly',
            'total_repayments'    => (string) ($application->term_weeks ?? ''),
            'amount_per_repayment' => '',
            'total_repayment_amount' => '',
            'first_repayment_date' => '',

            // Fees (editable amounts; fixed ones are hardcoded in the template)
            'application_fee'     => '',
            'security_search_fee' => '',
            'legal_fee'           => '',
            'security_registration_fee' => '',
            'valuation_fee'       => '',
            'monthly_account_fee' => '',
            'annual_review_fee'   => '',
            'establishment_fee'   => '',
            'exit_fee'            => '',
            'break_cost'          => '',

            // Schedule values
            'commencement_date'   => 'the date Lender advanced or advances the Principal Sum to Borrowers',
            'repayment_date'      => '',
            'disclosure_date'     => '',
            'interest_rate'       => '',
            'default_rate'        => '',
            'lower_rate'          => '',
            'loan_purpose'        => ucwords(str_replace('_', ' ', $application->loan_purpose ?? '')),
            'permitted_encumbrance' => '',
            'secured_land'        => '',

            // Schedule 2 — repayment schedule rows [{date, amount}, ...]
            'repayment_schedule'  => [],

            // Signatures
            'witness_name'        => '',
            'witness_occupation'  => '',
            'witness_signature'   => '',
            'client_signature'    => '',
            'signed_at'           => '',
            'signed_ip'           => '',
        ];
    }
}
