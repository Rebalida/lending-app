{{-- resources/views/applications/pdf/submission.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Application {{ $application->application_number }} — Submission Confirmation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #1a1a1a;
        }

        /* ── Page header / footer ── */
        .page-header {
            background: #1e1b4b;
            color: white;
            padding: 24px 32px;
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .page-header .meta {
            font-size: 10px;
            color: #c7d2fe;
            margin-top: 4px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f3f4f6;
            border-top: 1px solid #e5e7eb;
            padding: 8px 32px;
            font-size: 9px;
            color: #6b7280;
            text-align: center;
        }

        /* ── Sections ── */
        .section {
            margin: 0 32px 20px 32px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e1b4b;
            border-bottom: 2px solid #6366f1;
            padding-bottom: 4px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Tables ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 11px;
        }

        th, td {
            padding: 6px 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #f9fafb;
            font-weight: bold;
            color: #374151;
            width: 35%;
        }

        thead th {
            background-color: #ede9fe;
            color: #1e1b4b;
            width: auto;
        }

        .total-row td, .total-row th {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        .net-positive { color: #065f46; font-weight: bold; }
        .net-negative { color: #7f1d1d; font-weight: bold; }

        /* ── Status badge ── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-submitted { background: #dbeafe; color: #1e40af; }
        .badge-draft     { background: #e5e7eb; color: #374151; }
        .badge-approved  { background: #d1fae5; color: #065f46; }

        /* ── Confirmation banner ── */
        .confirmation-banner {
            margin: 0 32px 20px 32px;
            background: #f0fdf4;
            border: 2px solid #16a34a;
            border-radius: 6px;
            padding: 12px 16px;
        }

        .confirmation-banner h2 {
            font-size: 13px;
            color: #15803d;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .confirmation-banner p {
            font-size: 10px;
            color: #166534;
        }

        /* ── Signature block ── */
        .signature-section {
            margin: 0 32px 20px 32px;
            border: 1px solid #6366f1;
            border-radius: 6px;
            overflow: hidden;
        }

        .signature-header {
            background: #1e1b4b;
            color: white;
            padding: 8px 16px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .signature-body {
            padding: 16px;
        }

        .signature-meta {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }

        .signature-meta-cell {
            display: table-cell;
            width: 33%;
            padding: 0 8px 0 0;
            vertical-align: top;
        }

        .sig-label {
            font-size: 9px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .sig-value {
            font-size: 11px;
            color: #1a1a1a;
            font-weight: bold;
        }

        .signature-image-wrap {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            background: #fff;
            padding: 8px;
            margin-bottom: 12px;
            text-align: center;
        }

        .signature-image-wrap img {
            max-height: 100px;
            max-width: 100%;
        }

        .declaration-text {
            font-size: 10px;
            color: #374151;
            line-height: 1.7;
            background: #f9fafb;
            border-left: 3px solid #6366f1;
            padding: 8px 12px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

{{-- ── Page header ── --}}
<div class="page-header">
    <h1>{{ config('app.name') }} — Loan Application Submission</h1>
    <div class="meta">
        Application #{{ $application->application_number }} &nbsp;|&nbsp;
        Generated: {{ $generatedAt->format('d F Y H:i') }} &nbsp;|&nbsp;
        Status: {{ ucwords(str_replace('_', ' ', $application->status)) }}
    </div>
</div>

{{-- ── Submission confirmation banner ── --}}
<div class="confirmation-banner">
    <h2>Application Submitted Successfully</h2>
    <p>
        Thank you for submitting your loan application. This document is your confirmation copy.
        Please retain it for your records. Our team will review your application and be in touch within 24–48 business hours.
    </p>
</div>

{{-- ── Loan Details ── --}}
<div class="section">
    <div class="section-title">Loan Details</div>
    <table>
        <tr><th>Application Number</th><td>{{ $application->application_number }}</td></tr>
        <tr><th>Loan Amount</th><td>${{ number_format($application->loan_amount, 2) }}</td></tr>
        <tr><th>Term</th><td>{{ $application->term_months }} months</td></tr>
        <tr><th>Purpose</th><td>{{ ucwords(str_replace('_', ' ', $application->loan_purpose)) }}</td></tr>
        @if($application->loan_purpose_details)
        <tr><th>Purpose Details</th><td>{{ $application->loan_purpose_details }}</td></tr>
        @endif
        @if($application->security_type)
        <tr><th>Security Type</th><td>{{ ucwords(str_replace('_', ' ', $application->security_type)) }}</td></tr>
        @endif
        <tr>
            <th>Submitted At</th>
            <td>{{ $application->submitted_at?->format('d M Y H:i') ?? now()->format('d M Y H:i') }}</td>
        </tr>
        <tr><th>Submission IP</th><td>{{ $application->submission_ip ?? 'N/A' }}</td></tr>
    </table>
</div>

{{-- ── Borrower Information ── --}}
@if($application->borrowerInformation)
@php $b = $application->borrowerInformation; @endphp
<div class="section">
    <div class="section-title">Borrower Information</div>
    <table>
        <tr><th>Borrower Name</th><td>{{ $b->borrower_name }}</td></tr>
        <tr><th>Borrower Type</th><td>{{ $b->borrower_type_label }}</td></tr>
        @if($b->abn)
        <tr><th>ABN</th><td>{{ $b->formatted_abn }}</td></tr>
        @endif
        @if($b->nature_of_business)
        <tr><th>Nature of Business</th><td>{{ $b->nature_of_business }}</td></tr>
        @endif
        @if($b->years_in_business !== null)
        <tr><th>Years in Business</th><td>{{ $b->years_in_business }} years</td></tr>
        @endif
    </table>
</div>
@endif

{{-- ── Directors / Trustees ── --}}
@if($application->borrowerInformation &&
    in_array($application->borrowerInformation->borrower_type, ['company', 'trust']) &&
    $application->borrowerDirectors->count() > 0)
@php $dirLabel = $application->borrowerInformation->borrower_type === 'trust' ? 'Trustees' : 'Directors'; @endphp
<div class="section">
    <div class="section-title">{{ $dirLabel }}</div>
    <table>
        <thead>
            <tr>
                <th>Name</th><th>Email</th><th>Phone</th><th>DOB</th><th>Ownership</th><th>Guarantor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($application->borrowerDirectors as $director)
            <tr>
                <td>{{ $director->full_name }}</td>
                <td>{{ $director->email ?? '—' }}</td>
                <td>{{ $director->phone ?? '—' }}</td>
                <td>{{ $director->date_of_birth?->format('d M Y') ?? '—' }}</td>
                <td>{{ $director->ownership_percentage !== null ? $director->ownership_percentage.'%' : '—' }}</td>
                <td>{{ $director->is_guarantor ? 'Yes' : 'No' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Personal Details ── --}}
@if($application->personalDetails)
@php $pd = $application->personalDetails; @endphp
<div class="section">
    <div class="section-title">Personal Details</div>
    <table>
        <tr>
            <th>Full Name</th>
            <td>
                {{ $pd->user->first_name }}
                {{ $pd->user->middle_name ?? '' }}
                {{ $pd->user->last_name }}
                {{ $pd->user->name_extension ?? '' }}
            </td>
        </tr>
        <tr><th>Email</th><td>{{ $pd->user->email }}</td></tr>
        <tr><th>Mobile Phone</th><td>{{ $pd->mobile_phone }}</td></tr>
        @if($pd->date_of_birth)
        <tr>
            <th>Date of Birth</th>
            <td>{{ $pd->date_of_birth->format('d M Y') }}{{ $pd->age ? ' ('.$pd->age.' yrs)' : '' }}</td>
        </tr>
        @endif
        @if($pd->gender)
        <tr><th>Gender</th><td>{{ ucwords(str_replace('_', ' ', $pd->gender)) }}</td></tr>
        @endif
        @if($pd->citizenship_status)
        <tr><th>Citizenship</th><td>{{ ucwords(str_replace('_', ' ', $pd->citizenship_status)) }}</td></tr>
        @endif
        <tr><th>Marital Status</th><td>{{ ucwords(str_replace('_', ' ', $pd->marital_status)) }}</td></tr>
        <tr><th>Number of Dependants</th><td>{{ $pd->number_of_dependants }}</td></tr>
        @if(in_array($pd->marital_status, ['married', 'defacto']) && $pd->spouse_name)
        <tr><th>Spouse / Partner Name</th><td>{{ $pd->spouse_name }}</td></tr>
        @endif
        @if(in_array($pd->marital_status, ['married', 'defacto']) && $pd->spouse_income !== null)
        <tr><th>Spouse / Partner Income</th><td>${{ number_format($pd->spouse_income, 2) }} p.a.</td></tr>
        @endif
    </table>
</div>
@endif

{{-- ── Residential History ── --}}
@if($application->residentialAddresses->count() > 0)
<div class="section">
    <div class="section-title">Residential History</div>
    @foreach($application->residentialAddresses->sortBy('address_type') as $address)
    <table>
        <tr><th>Type</th><td>{{ ucwords(str_replace('_', ' ', $address->address_type)) }}</td></tr>
        <tr><th>Address</th><td>{{ $address->full_address }}</td></tr>
        <tr>
            <th>Period</th>
            <td>
                {{ $address->start_date->format('M Y') }} –
                {{ $address->end_date ? $address->end_date->format('M Y') : 'Present' }}
                ({{ $address->months_at_address }} months)
            </td>
        </tr>
        <tr><th>Residential Status</th><td>{{ ucfirst($address->residential_status) }}</td></tr>
    </table>
    @endforeach
</div>
@endif

{{-- ── Employment & Income ── --}}
@if($application->employmentDetails->count() > 0)
<div class="section">
    <div class="section-title">Employment &amp; Income</div>
    @foreach($application->employmentDetails as $employment)
    <table>
        <tr><th>Employment Type</th><td>{{ ucwords(str_replace('_', ' ', $employment->employment_type)) }}</td></tr>
        <tr><th>Employer / Business</th><td>{{ $employment->employer_business_name }}</td></tr>
        <tr><th>Position</th><td>{{ $employment->position }}</td></tr>
        <tr><th>Annual Income</th><td>${{ number_format($employment->getAnnualIncome(), 2) }}</td></tr>
        <tr><th>Monthly Income</th><td>${{ number_format($employment->getMonthlyIncome(), 2) }}</td></tr>
    </table>
    @endforeach
</div>
@endif

{{-- ── Living Expenses ── --}}
@if($application->livingExpenses->count() > 0)
<div class="section">
    <div class="section-title">Living Expenses</div>
    <table>
        <thead>
            <tr><th>Category</th><th>Expense</th><th>Amount</th><th>Frequency</th></tr>
        </thead>
        <tbody>
            @foreach($application->livingExpenses as $expense)
            <tr>
                <td>{{ ucwords(str_replace('_', ' ', $expense->expense_category)) }}</td>
                <td>{{ $expense->expense_name }}</td>
                <td>${{ number_format($expense->client_declared_amount, 2) }}</td>
                <td>{{ ucfirst($expense->frequency) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2"><strong>Total Monthly Expenses</strong></td>
                <td><strong>${{ number_format($application->getTotalLivingExpensesMonthly(), 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- ── Director Assets & Liabilities ── --}}
@if($application->directorAssets->count() > 0 || $application->directorLiabilities->count() > 0)
<div class="section">
    <div class="section-title">Director Assets &amp; Liabilities</div>

    @if($application->directorAssets->count() > 0)
    <p style="font-size:10px;font-weight:bold;color:#374151;margin-bottom:4px;">Assets</p>
    <table>
        <thead>
            <tr><th>Type</th><th>Description</th><th>Value</th></tr>
        </thead>
        <tbody>
            @foreach($application->directorAssets as $asset)
            <tr>
                <td>{{ $asset->asset_type_label }}</td>
                <td>{{ $asset->description ?? '—' }}</td>
                <td>${{ number_format($asset->estimated_value, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2"><strong>Total Assets</strong></td>
                <td><strong>${{ number_format($application->directorAssets->sum('estimated_value'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if($application->directorLiabilities->count() > 0)
    <p style="font-size:10px;font-weight:bold;color:#374151;margin-bottom:4px;margin-top:8px;">Liabilities</p>
    <table>
        <thead>
            <tr><th>Type</th><th>Lender</th><th>Balance</th></tr>
        </thead>
        <tbody>
            @foreach($application->directorLiabilities as $liability)
            <tr>
                <td>{{ $liability->liability_type_label }}</td>
                <td>{{ $liability->lender_name ?? '—' }}</td>
                <td>${{ number_format($liability->outstanding_balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2"><strong>Total Liabilities</strong></td>
                <td><strong>${{ number_format($application->directorLiabilities->sum('outstanding_balance'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    @php
        $dalNet = $application->directorAssets->sum('estimated_value')
                - $application->directorLiabilities->sum('outstanding_balance');
    @endphp
    <table>
        <tr class="total-row">
            <th>Net Position</th>
            <td class="{{ $dalNet >= 0 ? 'net-positive' : 'net-negative' }}">${{ number_format($dalNet, 2) }}</td>
        </tr>
    </table>
    @endif
</div>
@endif

{{-- ── Company Assets & Liabilities ── --}}
@if($application->borrowerInformation?->borrower_type === 'company' &&
    ($application->companyAssets->count() > 0 || $application->companyLiabilities->count() > 0))
<div class="section">
    <div class="section-title">Company Assets &amp; Liabilities</div>

    @if($application->companyAssets->count() > 0)
    <p style="font-size:10px;font-weight:bold;color:#374151;margin-bottom:4px;">Assets</p>
    <table>
        <thead><tr><th>Asset Name</th><th>Notes</th><th>Value</th></tr></thead>
        <tbody>
            @foreach($application->companyAssets as $asset)
            <tr>
                <td>{{ $asset->asset_name }}</td>
                <td>{{ $asset->notes ?? '—' }}</td>
                <td>${{ number_format($asset->value, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2"><strong>Total Assets</strong></td>
                <td><strong>${{ number_format($application->companyAssets->sum('value'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if($application->companyLiabilities->count() > 0)
    <p style="font-size:10px;font-weight:bold;color:#374151;margin-bottom:4px;margin-top:8px;">Liabilities</p>
    <table>
        <thead><tr><th>Liability Name</th><th>Notes</th><th>Value</th></tr></thead>
        <tbody>
            @foreach($application->companyLiabilities as $liability)
            <tr>
                <td>{{ $liability->liability_name }}</td>
                <td>{{ $liability->notes ?? '—' }}</td>
                <td>${{ number_format($liability->value, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2"><strong>Total Liabilities</strong></td>
                <td><strong>${{ number_format($application->companyLiabilities->sum('value'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @php
        $calNet = $application->companyAssets->sum('value')
                - $application->companyLiabilities->sum('value');
    @endphp
    <table>
        <tr class="total-row">
            <th>Net Position</th>
            <td class="{{ $calNet >= 0 ? 'net-positive' : 'net-negative' }}">${{ number_format($calNet, 2) }}</td>
        </tr>
    </table>
    @endif
</div>
@endif

{{-- ── Accountant Details ── --}}
@if($application->borrowerInformation?->borrower_type === 'company' && $application->accountantDetail)
@php $acct = $application->accountantDetail; @endphp
<div class="section">
    <div class="section-title">Accountant Details</div>
    <table>
        <tr><th>Accountant Name</th><td>{{ $acct->accountant_name }}</td></tr>
        <tr><th>Email</th><td>{{ $acct->accountant_email ?? '—' }}</td></tr>
        <tr><th>Phone</th><td>{{ $acct->accountant_phone ?? '—' }}</td></tr>
        @if($acct->years_with_accountant !== null)
        <tr><th>Years with Accountant</th><td>{{ $acct->years_with_accountant }} years</td></tr>
        @endif
    </table>
</div>
@endif

{{-- ── Declaration & Signature ── --}}
@if($declaration)
<div class="signature-section">
    <div class="signature-header">Declaration &amp; Electronic Signature</div>
    <div class="signature-body">

        {{-- Meta row: signatory, date, IP ── --}}
        <div class="signature-meta">
            <div class="signature-meta-cell">
                <div class="sig-label">Signatory</div>
                <div class="sig-value">{{ $declaration->signatory_name ?? $application->user->name }}</div>
            </div>
            <div class="signature-meta-cell">
                <div class="sig-label">Signed At</div>
                <div class="sig-value">
                    {{ ($declaration->signature_timestamp ?? $declaration->agreed_at)?->format('d M Y H:i:s') ?? '—' }}
                </div>
            </div>
            <div class="signature-meta-cell">
                <div class="sig-label">IP Address</div>
                <div class="sig-value">{{ $declaration->agreement_ip ?? $application->signature_ip ?? 'N/A' }}</div>
            </div>
        </div>
        @if($declaration->signatory_position)
        <div class="signature-meta">
            <div class="signature-meta-cell">
                <div class="sig-label">Position / Title</div>
                <div class="sig-value">{{ $declaration->signatory_position }}</div>
            </div>
        </div>
        @endif

        {{-- Signature image ── --}}
        @if($declaration->signature_data && str_starts_with($declaration->signature_data, 'data:image'))
        <div class="signature-image-wrap">
            <img src="{{ $declaration->signature_data }}" alt="Electronic signature">
        </div>
        @endif

        {{-- Declaration text ── --}}
        <div class="declaration-text">
            {{ $declaration->declaration_text }}
        </div>

    </div>
</div>
@endif

{{-- ── Footer ── --}}
<div class="footer">
    This is a confidential document generated by {{ config('app.name') }} on {{ $generatedAt->format('d F Y H:i') }}.
    Application #{{ $application->application_number }} &nbsp;|&nbsp; Retain for your records.
</div>

</body>
</html>