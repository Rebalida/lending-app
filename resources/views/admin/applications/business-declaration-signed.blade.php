{{-- resources/views/admin/applications/business-declaration-signed.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between print:hidden">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Signed Business Declaration — {{ $application->application_number }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Signed on {{ $application->business_declaration_signed_at->format('d M Y \a\t g:i A') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.applications.show', $application) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300
                          rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    ← Back
                </a>
                <button onclick="window.print()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                               text-sm font-semibold rounded-md hover:bg-indigo-700 transition">
                    Print / Save as PDF
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            nav, header, aside, footer, .print\:hidden { display: none !important; }
            @page { size: A4; margin: 20mm 15mm; }
            .print-card { page-break-inside: avoid; }
        }
    </style>

    @php
        $declaration = $application->guarantor_data['business_declaration'] ?? [];
    @endphp

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden print-card">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 text-center">
                    <h1 class="text-lg font-bold text-gray-900 uppercase">Business Purpose Declaration</h1>
                    <p class="text-xs text-gray-500 mt-1">(Individual borrowers only)</p>
                </div>
                <div class="p-6 space-y-5 text-sm text-gray-700">

                    <p class="italic text-gray-600">
                        <strong>Instructions to Borrower:</strong> Only sign this declaration if the loan funds
                        will be used wholly or predominantly for business and/or investment purposes which is
                        not investment in residential property.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Borrower Name</p>
                            <p class="text-sm text-gray-900 mt-0.5">
                                {{ $application->borrowerInformation?->company_name ?? $application->user->name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Loan Purpose</p>
                            <p class="text-sm text-gray-900 mt-0.5">
                                {{ ucwords(str_replace('_', ' ', $application->loan_purpose ?? '—')) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Amount</p>
                            <p class="text-sm text-gray-900 mt-0.5">
                                ${{ number_format($application->loan_amount, 2) }}
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-5">
                        <h2 class="text-sm font-bold text-gray-900 mb-3">Declaration of Purpose</h2>
                        <p>
                            I/We declare that the credit to be provided to me/us by <strong>AHA Money</strong>
                            is to be applied wholly or predominantly for:
                        </p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>business purposes; or</li>
                            <li>investment purposes other than investment in residential property.</li>
                        </ul>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <p class="text-xs text-amber-800 font-semibold uppercase mb-1">Important Notice</p>
                        <p class="text-xs text-amber-700">
                            By signing this declaration the borrower may lose protection under the
                            <strong>National Credit Code</strong>.
                        </p>
                    </div>

                </div>
            </div>

            {{-- Signature Block --}}
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden print-card">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-3">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Client Signature</h2>
                </div>
                <div class="p-6 space-y-4">
                    @if(!empty($declaration['signature']))
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Signature</p>
                            <div class="border border-gray-200 rounded-lg bg-gray-50 p-2 inline-block">
                                <img src="{{ $declaration['signature'] }}"
                                     alt="Client Signature" class="h-24 object-contain">
                            </div>
                        </div>
                    @endif
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Date Signed</p>
                            <p class="text-sm text-gray-900 mt-0.5">
                                {{ isset($declaration['signed_at'])
                                    ? \Carbon\Carbon::parse($declaration['signed_at'])->format('d M Y \a\t g:i A')
                                    : '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">IP Address</p>
                            <p class="text-sm text-gray-900 mt-0.5">{{ $declaration['signed_ip'] ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>