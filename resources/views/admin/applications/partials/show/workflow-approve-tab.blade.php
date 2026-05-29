{{-- resources/views/admin/applications/partials/show/workflow-approve-tab.blade.php --}}
@php use App\Models\Application; @endphp

<div class="space-y-6">
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-1">Approval Workflow</h3>
        <p class="text-sm text-gray-600 mb-6">
            Complete each step below to guide the application through the approval process. Each step must be completed before the next becomes available.
        </p>

        {{-- ── Step 1: Approval Letter ──────────────────────────────────────── --}}
        <div class="space-y-4">
            {{-- Step 1 --}}
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
                            Use the Approve button above to send the conditional approval letter to the client.
                        </p>

                        @if($application->approval_letter_sent_at)
                            <p class="text-xs text-green-700 ml-10 font-medium">
                                ✓ Sent {{ $application->approval_letter_sent_at->format('M d, Y \a\t g:i A') }}
                            </p>
                        @else
                            <p class="text-xs text-yellow-700 ml-10 font-medium">
                                Awaiting approval letter to be sent via Approve button
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Step 2: Send Guarantor Form ──────────────────────────────────── --}}
            <div class="border-l-4 {{ $application->approval_letter_sent_at ? 'border-indigo-500' : 'border-gray-300' }} pl-6 py-4
                        {{ !$application->approval_letter_sent_at ? 'opacity-50' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($application->guarantor_form_requested_at)
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-green-700">Step 2: Guarantor Form Sent</h4>
                            @else
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $application->approval_letter_sent_at ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    <span class="text-sm font-bold {{ $application->approval_letter_sent_at ? 'text-indigo-600' : 'text-gray-400' }}">2</span>
                                </div>
                                <h4 class="text-sm font-semibold {{ $application->approval_letter_sent_at ? 'text-gray-900' : 'text-gray-500' }}">
                                    Step 2: Send Guarantor Application Form
                                </h4>
                            @endif
                        </div>
                        
                        <p class="text-xs {{ $application->approval_letter_sent_at ? 'text-gray-600' : 'text-gray-500' }} ml-10 mb-3">
                            Send the guarantor application form to the client for completion.
                        </p>

                        @if($application->guarantor_form_requested_at)
                            <p class="text-xs text-green-700 ml-10 font-medium">
                                ✓ Sent {{ $application->guarantor_form_requested_at->format('M d, Y \a\t g:i A') }}
                            </p>
                            @if($application->guarantor_form_request_url)
                                <p class="text-xs text-gray-600 ml-10 mt-1">
                                    Link: <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $application->guarantor_form_request_url }}</code>
                                </p>
                            @endif
                        @elseif($application->approval_letter_sent_at)
                            <div class="ml-10">
                                <button type="button"
                                        class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white
                                               text-xs font-semibold rounded-md hover:bg-indigo-700 transition
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        disabled>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Complete Step 1 First
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Step 3: Review Guarantor Submission ───────────────────────────── --}}
            <div class="border-l-4 {{ $application->guarantor_form_completed_at ? 'border-indigo-500' : 'border-gray-300' }} pl-6 py-4
                        {{ !$application->guarantor_form_completed_at ? 'opacity-50' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($application->guarantor_form_completed_at)
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-green-700">Step 3: Guarantor Form Reviewed</h4>
                            @else
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $application->guarantor_form_requested_at ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    <span class="text-sm font-bold {{ $application->guarantor_form_requested_at ? 'text-indigo-600' : 'text-gray-400' }}">3</span>
                                </div>
                                <h4 class="text-sm font-semibold {{ $application->guarantor_form_requested_at ? 'text-gray-900' : 'text-gray-500' }}">
                                    Step 3: Review Guarantor Application
                                </h4>
                            @endif
                        </div>
                        
                        <p class="text-xs {{ $application->guarantor_form_requested_at ? 'text-gray-600' : 'text-gray-500' }} ml-10 mb-3">
                            Review the completed guarantor application submitted by the client.
                        </p>

                        @if($application->guarantor_form_completed_at)
                            <p class="text-xs text-green-700 ml-10 font-medium">
                                ✓ Reviewed {{ $application->guarantor_form_completed_at->format('M d, Y \a\t g:i A') }}
                            </p>
                        @elseif($application->guarantor_form_requested_at)
                            <div class="ml-10">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            bg-yellow-100 text-yellow-800">
                                    Awaiting client submission
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Step 4: Send for Signature ─────────────────────────────────────── --}}
            <div class="border-l-4 {{ $application->guarantor_form_completed_at ? 'border-indigo-500' : 'border-gray-300' }} pl-6 py-4
                        {{ !$application->guarantor_form_completed_at ? 'opacity-50' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($application->guarantor_form_signed_at)
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-green-700">Step 4: Guarantor Form Signed</h4>
                            @else
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $application->guarantor_form_completed_at ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    <span class="text-sm font-bold {{ $application->guarantor_form_completed_at ? 'text-indigo-600' : 'text-gray-400' }}">4</span>
                                </div>
                                <h4 class="text-sm font-semibold {{ $application->guarantor_form_completed_at ? 'text-gray-900' : 'text-gray-500' }}">
                                    Step 4: Send Guarantor Form for Signature
                                </h4>
                            @endif
                        </div>
                        
                        <p class="text-xs {{ $application->guarantor_form_completed_at ? 'text-gray-600' : 'text-gray-500' }} ml-10 mb-3">
                            Generate prefilled PDF and send to guarantor for signature.
                        </p>

                        @if($application->guarantor_form_signed_at)
                            <p class="text-xs text-green-700 ml-10 font-medium">
                                ✓ Signed {{ $application->guarantor_form_signed_at->format('M d, Y \a\t g:i A') }}
                            </p>
                        @elseif($application->guarantor_form_completed_at)
                            <div class="ml-10">
                                <button type="button"
                                        class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white
                                               text-xs font-semibold rounded-md hover:bg-indigo-700 transition
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Generate & Send for Signature
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Step 5: Send Business Declaration ──────────────────────────────── --}}
            <div class="border-l-4 {{ $application->guarantor_form_signed_at ? 'border-indigo-500' : 'border-gray-300' }} pl-6 py-4
                        {{ !$application->guarantor_form_signed_at ? 'opacity-50' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($application->business_declaration_signed_at)
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-green-700">Step 5: Business Declaration Signed</h4>
                            @else
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $application->guarantor_form_signed_at ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    <span class="text-sm font-bold {{ $application->guarantor_form_signed_at ? 'text-indigo-600' : 'text-gray-400' }}">5</span>
                                </div>
                                <h4 class="text-sm font-semibold {{ $application->guarantor_form_signed_at ? 'text-gray-900' : 'text-gray-500' }}">
                                    Step 5: Send Business Declaration
                                </h4>
                            @endif
                        </div>
                        
                        <p class="text-xs {{ $application->guarantor_form_signed_at ? 'text-gray-600' : 'text-gray-500' }} ml-10 mb-3">
                            Generate and send business declaration form for completion and signature.
                        </p>

                        @if($application->business_declaration_signed_at)
                            <p class="text-xs text-green-700 ml-10 font-medium">
                                ✓ Signed {{ $application->business_declaration_signed_at->format('M d, Y \a\t g:i A') }}
                            </p>
                        @elseif($application->guarantor_form_signed_at)
                            <div class="ml-10">
                                <button type="button"
                                        class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white
                                               text-xs font-semibold rounded-md hover:bg-indigo-700 transition
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Generate & Send Declaration
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Step 6: Mark as Settled ────────────────────────────────────────── --}}
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
                                <h4 class="text-sm font-semibold text-green-700">Step 6: Marked as Settled</h4>
                            @else
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $application->business_declaration_signed_at ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    <span class="text-sm font-bold {{ $application->business_declaration_signed_at ? 'text-indigo-600' : 'text-gray-400' }}">6</span>
                                </div>
                                <h4 class="text-sm font-semibold {{ $application->business_declaration_signed_at ? 'text-gray-900' : 'text-gray-500' }}">
                                    Step 6: Mark Application as Settled
                                </h4>
                            @endif
                        </div>
                        
                        <p class="text-xs {{ $application->business_declaration_signed_at ? 'text-gray-600' : 'text-gray-500' }} ml-10 mb-3">
                            Complete the approval process by marking the application as settled and ready for funding.
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
                                            class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 text-white
                                                   text-xs font-semibold rounded-md hover:bg-green-700 transition
                                                   focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Mark as Settled
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
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <p class="text-xs text-gray-600 mb-1">Steps Complete</p>
            <p class="text-2xl font-bold text-gray-900">
                {{ 
                    collect([
                        $application->approval_letter_sent_at,
                        $application->guarantor_form_requested_at,
                        $application->guarantor_form_completed_at,
                        $application->guarantor_form_signed_at,
                        $application->business_declaration_signed_at,
                        $application->status === Application::STATUS_SETTLED ? true : null,
                    ])->filter()->count()
                }}
                <span class="text-sm font-normal text-gray-600">/ 6</span>
            </p>
        </div>
        
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <p class="text-xs text-gray-600 mb-1">Current Status</p>
            <p class="text-sm font-bold text-gray-900">{{ Application::statusLabel($application->status) }}</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <p class="text-xs text-gray-600 mb-1">Last Updated</p>
            <p class="text-sm font-medium text-gray-900">{{ $application->updated_at->diffForHumans() }}</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <p class="text-xs text-gray-600 mb-1">Approved Since</p>
            <p class="text-sm font-medium text-gray-900">
                {{ $application->approval_letter_sent_at 
                    ? $application->approval_letter_sent_at->diffForHumans()
                    : 'Not yet' }}
            </p>
        </div>
    </div>
</div>