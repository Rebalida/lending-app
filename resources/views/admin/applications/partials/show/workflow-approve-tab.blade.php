{{-- resources/views/admin/applications/partials/show/workflow-approve-tab.blade.php --}}
@php
    use App\Models\Application;

    $requiresGuarantor = $application->requiresGuarantor();

    // Step 4 unlocks after guarantor (if required) OR straight after Step 1 (if not required)
    $declarationUnlocked = $requiresGuarantor
        ? (bool) $application->guarantor_form_signed_at
        : (bool) $application->approval_letter_sent_at;

    // Dynamic step numbers
    $step4Num = $requiresGuarantor ? 4 : 2;
    $step5Num = $requiresGuarantor ? 5 : 3;
@endphp

<div class="space-y-6">
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-1">Approval Workflow</h3>
        <p class="text-sm text-gray-600 mb-6">
            Follow each step to complete the approval process. Each step depends on the previous one.
        </p>

        <div class="space-y-4">

            {{-- Step 1: Approval Letter ─────────────────────────────────────────── --}}
            <div class="border-l-4 border-indigo-500 pl-6 py-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($application->approval_letter_sent_at)
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-green-700">Step 1: Approval Letter Sent</h4>
                            @else
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                    <span class="text-sm font-bold text-yellow-600">⏳</span>
                                </div>
                                <h4 class="text-sm font-semibold text-gray-900">Step 1: Send Approval Letter</h4>
                            @endif
                        </div>

                        <p class="text-xs text-gray-600 ml-10 mb-3">
                            Use the Approve button in Quick Actions to send the conditional approval letter.
                        </p>

                        @if($application->approval_letter_sent_at)
                            <p class="text-xs text-green-700 ml-10 font-medium">
                                ✓ Sent {{ $application->approval_letter_sent_at->format('M d, Y \a\t g:i A') }}
                            </p>
                        @else
                            <p class="text-xs text-yellow-700 ml-10 font-medium">
                                Awaiting approval letter via Approve button
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Steps 2 & 3: Guarantor (conditional) ───────────────────────────── --}}
            @if($requiresGuarantor)

                {{-- Step 2: Guarantor Form ──────────────────────────────────────── --}}
                <div class="border-l-4 {{ $application->approval_letter_sent_at ? 'border-indigo-500' : 'border-gray-300' }} pl-6 py-4
                            {{ !$application->approval_letter_sent_at ? 'opacity-50' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                @if($application->guarantor_form_signed_at)
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-semibold text-green-700">Step 2: Guarantor Form Signed</h4>
                                @else
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $application->approval_letter_sent_at ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center">
                                        <span class="text-sm font-bold {{ $application->approval_letter_sent_at ? 'text-indigo-600' : 'text-gray-400' }}">2</span>
                                    </div>
                                    <h4 class="text-sm font-semibold {{ $application->approval_letter_sent_at ? 'text-gray-900' : 'text-gray-500' }}">
                                        Step 2: Guarantor Form
                                    </h4>
                                @endif
                            </div>

                            <p class="text-xs {{ $application->approval_letter_sent_at ? 'text-gray-600' : 'text-gray-500' }} ml-10 mb-3">
                                Send the guarantor form to the client for completion and signature.
                            </p>

                            @if($application->guarantor_form_signed_at)
                                <p class="text-xs text-green-700 ml-10 font-medium">
                                    ✓ Signed {{ $application->guarantor_form_signed_at->format('M d, Y \a\t g:i A') }}
                                </p>
                            @elseif($application->approval_letter_sent_at)
                                <div class="ml-10 flex items-center gap-3">
                                    <a href="{{ route('admin.applications.guarantor-form.show', $application) }}"
                                       class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white
                                              text-xs font-semibold rounded-md hover:bg-indigo-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        {{ $application->guarantor_form_request_url ? 'View / Resend Form' : 'Send Guarantor Form' }}
                                    </a>
                                    @if($application->guarantor_form_request_url)
                                        <span class="text-xs text-yellow-700 font-medium">⏳ Awaiting client signature</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Step 3: View Signed Guarantor Form ──────────────────────────── --}}
                <div class="border-l-4 {{ $application->guarantor_form_signed_at ? 'border-indigo-500' : 'border-gray-300' }} pl-6 py-4
                            {{ !$application->guarantor_form_signed_at ? 'opacity-50' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $application->guarantor_form_signed_at ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    <span class="text-sm font-bold {{ $application->guarantor_form_signed_at ? 'text-indigo-600' : 'text-gray-400' }}">3</span>
                                </div>
                                <h4 class="text-sm font-semibold {{ $application->guarantor_form_signed_at ? 'text-gray-900' : 'text-gray-500' }}">
                                    Step 3: View Signed Guarantor Form
                                </h4>
                            </div>

                            <p class="text-xs {{ $application->guarantor_form_signed_at ? 'text-gray-600' : 'text-gray-500' }} ml-10 mb-3">
                                Review the completed and signed guarantor form. Print or save as PDF.
                            </p>

                            @if($application->guarantor_form_signed_at)
                                <div class="ml-10">
                                    <a href="{{ route('admin.applications.guarantor-form.signed', $application) }}"
                                       class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white
                                              text-xs font-semibold rounded-md hover:bg-blue-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View Signed Form
                                    </a>
                                </div>
                            @else
                                <p class="text-xs text-gray-400 ml-10">Available once client signs the guarantor form.</p>
                            @endif
                        </div>
                    </div>
                </div>

            @else

                {{-- Guarantor Not Required Notice ──────────────────────────────── --}}
                <div class="border-l-4 border-gray-200 pl-6 py-4">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-400">Guarantor Form — Not Required</h4>
                            <p class="text-xs text-gray-400 mt-0.5">Skipped — no guarantor needed for this application.</p>
                        </div>
                    </div>
                </div>

            @endif

            {{-- Step 4 (or 2): Business Declaration ─────────────────────────────── --}}
            <div class="border-l-4 {{ $declarationUnlocked ? 'border-indigo-500' : 'border-gray-300' }} pl-6 py-4
                        {{ !$declarationUnlocked ? 'opacity-50' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($application->business_declaration_signed_at)
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-green-700">Step {{ $step4Num }}: Business Declaration Signed</h4>
                            @else
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $declarationUnlocked ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    <span class="text-sm font-bold {{ $declarationUnlocked ? 'text-indigo-600' : 'text-gray-400' }}">{{ $step4Num }}</span>
                                </div>
                                <h4 class="text-sm font-semibold {{ $declarationUnlocked ? 'text-gray-900' : 'text-gray-500' }}">
                                    Step {{ $step4Num }}: Business Declaration
                                </h4>
                            @endif
                        </div>

                        <p class="text-xs {{ $declarationUnlocked ? 'text-gray-600' : 'text-gray-500' }} ml-10 mb-3">
                            Confirm loan funds are for business/investment purposes.
                        </p>

                        @if($application->business_declaration_signed_at)
                            <p class="text-xs text-green-700 ml-10 font-medium">
                                ✓ Signed {{ $application->business_declaration_signed_at->format('M d, Y \a\t g:i A') }}
                            </p>
                        @elseif($declarationUnlocked)
                            <div class="ml-10 flex items-center gap-3">
                                <form method="POST"
                                      action="{{ route('admin.applications.business-declaration.send', $application) }}"
                                      class="inline"
                                      data-loading-form>
                                    @csrf
                                    <button type="submit"
                                            class="loading-btn inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white
                                                   text-xs font-semibold rounded-md hover:bg-indigo-700 transition">
                                        <svg class="btn-spinner hidden animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                        <span class="btn-label">Send Declaration</span>
                                    </button>
                                </form>
                                @if($application->business_declaration_sent_at)
                                    <span class="text-xs text-yellow-700 font-medium">⏳ Awaiting client signature</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Step 5 (or 3): Mark as Settled ─────────────────────────────────── --}}
            <div class="border-l-4 {{ $application->business_declaration_signed_at ? 'border-indigo-500' : 'border-gray-300' }} pl-6 py-4
                        {{ !$application->business_declaration_signed_at ? 'opacity-50' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($application->status === Application::STATUS_SETTLED)
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-green-700">Step {{ $step5Num }}: Marked as Settled</h4>
                            @else
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $application->business_declaration_signed_at ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    <span class="text-sm font-bold {{ $application->business_declaration_signed_at ? 'text-indigo-600' : 'text-gray-400' }}">{{ $step5Num }}</span>
                                </div>
                                <h4 class="text-sm font-semibold {{ $application->business_declaration_signed_at ? 'text-gray-900' : 'text-gray-500' }}">
                                    Step {{ $step5Num }}: Mark Application as Settled
                                </h4>
                            @endif
                        </div>

                        <p class="text-xs {{ $application->business_declaration_signed_at ? 'text-gray-600' : 'text-gray-500' }} ml-10 mb-3">
                            Complete the approval process and mark ready for funding.
                        </p>

                        @if($application->status === Application::STATUS_SETTLED)
                            <p class="text-xs text-green-700 ml-10 font-medium">
                                ✓ Settled {{ $application->updated_at->format('M d, Y \a\t g:i A') }}
                            </p>
                        @elseif($application->business_declaration_signed_at)
                            <div class="ml-10">
                                <form method="POST"
                                      action="{{ route('admin.applications.updateStatus', $application) }}"
                                      data-loading-form
                                      class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ Application::STATUS_SETTLED }}">
                                    <button type="submit"
                                            class="loading-btn inline-flex items-center gap-2 px-3 py-2 bg-green-600 text-white
                                                   text-xs font-semibold rounded-md hover:bg-green-700 transition">
                                        <svg class="btn-spinner hidden animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <span class="btn-label">Mark as Settled</span>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Progress Summary ──────────────────────────────────────────────────── --}}
    @php
        $totalSteps = $requiresGuarantor ? 5 : 3;
        $completed = collect([
            $application->approval_letter_sent_at,
            $requiresGuarantor ? $application->guarantor_form_signed_at : true,
            $requiresGuarantor ? $application->guarantor_form_signed_at : null,
            $application->business_declaration_signed_at,
            $application->status === Application::STATUS_SETTLED ? true : null,
        ])->filter()->count();

        // Adjust count for skipped guarantor steps
        if (! $requiresGuarantor) {
            $completed = collect([
                $application->approval_letter_sent_at,
                $application->business_declaration_signed_at,
                $application->status === Application::STATUS_SETTLED ? true : null,
            ])->filter()->count();
        }
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
            <p class="text-xs text-gray-600 mb-1">Steps Complete</p>
            <p class="text-2xl font-bold text-gray-900">
                {{ $completed }}
                <span class="text-sm font-normal text-gray-600">/ {{ $totalSteps }}</span>
            </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
            <p class="text-xs text-gray-600 mb-1">Approval Letter</p>
            <p class="text-sm font-bold text-gray-900">
                {{ $application->approval_letter_sent_at ? '✓ Sent' : '⏳ Pending' }}
            </p>
        </div>

        @if($requiresGuarantor)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
                <p class="text-xs text-gray-600 mb-1">Guarantor Form</p>
                <p class="text-sm font-bold text-gray-900">
                    {{ $application->guarantor_form_signed_at ? '✓ Signed' : '⏳ Pending' }}
                </p>
            </div>
        @endif

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
            <p class="text-xs text-gray-600 mb-1">Declaration</p>
            <p class="text-sm font-bold text-gray-900">
                {{ $application->business_declaration_signed_at ? '✓ Signed' : '⏳ Pending' }}
            </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
            <p class="text-xs text-gray-600 mb-1">Status</p>
            <p class="text-sm font-bold text-gray-900">
                {{ Application::statusLabel($application->status) }}
            </p>
        </div>
    </div>
</div>