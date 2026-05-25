{{-- resources/views/admin/applications/partials/show/quick-actions.blade.php --}}

@php
    $isLocked = in_array($application->status, ['approved', 'declined']);

    $unreadEmail = $application->communications()->emails()->whereNull('read_at')->where('direction', 'inbound')->count();
    $unreadSms   = $application->communications()->sms()->whereNull('read_at')->where('direction', 'inbound')->count();
    $unreadComms = $unreadEmail + $unreadSms;

    $submissionPdfExists = $application->status !== 'draft'
        && file_exists(public_path('submissions/loan-application-' . $application->application_number . '.pdf'));
@endphp

<div class="bg-white shadow-sm border border-gray-200 sm:rounded-lg">
    <div class="px-5 py-3">
        <div class="flex flex-wrap items-end gap-2 w-full">

            {{-- ── Change Status ───────────────────────────────────────────── --}}
            <form method="POST"
                  action="{{ route('admin.applications.updateStatus', $application) }}"
                  class="flex items-center gap-2"
                  data-loading-form>
                @csrf
                @method('PATCH')
                <div class="flex flex-col">
                    <label for="status-select" class="block text-xs font-medium text-gray-500 mb-1">
                        Change Status
                    </label>
                    <div class="flex items-center gap-2">
                        <select id="status-select"
                                name="status"
                                required
                                class="block py-1.5 px-2.5 border border-gray-300 bg-white rounded-md shadow-sm
                                       focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="submitted"             {{ $application->status === 'submitted'             ? 'selected' : '' }}>Submitted</option>
                            <option value="wip"                   {{ $application->status === 'wip'                   ? 'selected' : '' }}>Work In Progress</option>
                            <option value="outstanding_document"  {{ $application->status === 'outstanding_document'  ? 'selected' : '' }}>Outstanding Document</option>
                            <option value="waiting_for_signature" {{ $application->status === 'waiting_for_signature' ? 'selected' : '' }}>Waiting for Signature</option>
                            <option value="deferred"              {{ $application->status === 'deferred'              ? 'selected' : '' }}>Deferred</option>
                            <option value="approved"              {{ $application->status === 'approved'              ? 'selected' : '' }}>Approved</option>
                            <option value="declined"              {{ $application->status === 'declined'              ? 'selected' : '' }}>Declined</option>
                        </select>
                        <button type="submit"
                                class="loading-btn inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600
                                       border border-transparent rounded-md text-xs font-semibold text-white
                                       hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500
                                       focus:ring-offset-2 disabled:opacity-60 transition">
                            <svg class="btn-spinner hidden animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span class="btn-label">Update</span>
                        </button>
                    </div>
                </div>
            </form>

            <div class="hidden sm:block h-8 w-px bg-gray-200 mx-1"></div>

            {{-- ── Assign To ───────────────────────────────────────────────── --}}
            @if(auth()->user()->hasRole('admin') && !$isLocked)
                <form method="POST"
                      action="{{ route('admin.applications.assign', $application) }}"
                      class="flex items-center gap-2"
                      data-loading-form>
                    @csrf
                    @method('PATCH')
                    <div class="flex flex-col">
                        <label for="assigned-select" class="block text-xs font-medium text-gray-500 mb-1">
                            Assign To
                        </label>
                        <div class="flex items-center gap-2">
                            <select id="assigned-select"
                                    name="assigned_to"
                                    required
                                    class="block py-1.5 px-2.5 border border-gray-300 bg-white rounded-md shadow-sm
                                           focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">Select Assessor</option>
                                @foreach(\App\Models\User::role(['admin', 'assessor'])->get() as $assessor)
                                    <option value="{{ $assessor->id }}"
                                            {{ $application->assigned_to == $assessor->id ? 'selected' : '' }}>
                                        {{ $assessor->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="loading-btn inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600
                                           border border-transparent rounded-md text-xs font-semibold text-white
                                           hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500
                                           focus:ring-offset-2 disabled:opacity-60 transition">
                                <svg class="btn-spinner hidden animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span class="btn-label">Assign</span>
                            </button>
                        </div>
                    </div>
                </form>
            @elseif(auth()->user()->hasRole('admin') && $isLocked)
                <div class="flex flex-col">
                    <span class="block text-xs font-medium text-gray-500 mb-1">Assigned To</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-100 border border-gray-200
                                 rounded-md text-sm text-gray-600">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        {{ $application->assignedTo->name ?? 'Unassigned' }}
                    </span>
                </div>
            @else
                {{-- Assessor read-only --}}
                <div class="flex flex-col">
                    <span class="block text-xs font-medium text-gray-500 mb-1">Assigned To</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5
                                 {{ $application->assigned_to === auth()->id() ? 'bg-green-50 border-green-200 text-green-800' : 'bg-indigo-50 border-indigo-200 text-indigo-800' }}
                                 border rounded-md text-sm">
                        {{ $application->assigned_to === auth()->id() ? 'You (' . auth()->user()->name . ')' : ($application->assignedTo->name ?? 'Unassigned') }}
                    </span>
                </div>
            @endif

            <div class="hidden sm:block h-8 w-px bg-gray-200 mx-1"></div>

            {{-- ── Export PDF ──────────────────────────────────────────────── --}}
            <a href="{{ route('admin.applications.exportPdf', $application) }}"
               id="export-pdf-btn"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300
                      rounded-md text-xs font-medium text-gray-700 hover:bg-gray-50
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
               aria-label="Export application as PDF">
                <svg id="export-pdf-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <svg id="export-pdf-spinner" class="hidden animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span id="export-pdf-label">Export PDF</span>
            </a>

            {{-- ── Approve / Decline ───────────────────────────────────────── --}}
            @if (!$isLocked)
                <div class="hidden sm:block h-8 w-px bg-gray-200 mx-1"></div>

                <button type="button"
                        id="btn-approve"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 border border-transparent
                               rounded-md text-xs font-semibold text-white hover:bg-green-700
                               focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition"
                        aria-haspopup="dialog"
                        aria-controls="adl-modal">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Approve
                </button>

                <button type="button"
                        id="btn-decline"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 border border-transparent
                               rounded-md text-xs font-semibold text-white hover:bg-red-700
                               focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition"
                        aria-haspopup="dialog"
                        aria-controls="adl-modal">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Decline
                </button>
            @endif

            <div class="hidden sm:block h-8 w-px bg-gray-200 mx-1"></div>

            {{-- ── More Actions Dropdown ───────────────────────────────────── --}}
            <div class="relative" id="more-actions-wrap">
                <button type="button"
                        id="more-actions-btn"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="more-actions-menu"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300
                               rounded-md text-xs font-medium text-gray-700 hover:bg-gray-50
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    More actions
                    <svg id="more-actions-chevron" class="w-3.5 h-3.5 transition-transform duration-150"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                    @if($unreadComms > 0)
                        <span class="inline-flex items-center justify-center min-w-[1.1rem] h-4 px-1
                                     rounded-full bg-red-500 text-white text-[10px] font-bold leading-none"
                              aria-label="{{ $unreadComms }} unread messages">
                            {{ $unreadComms }}
                        </span>
                    @endif
                </button>

                <div id="more-actions-menu"
                     role="menu"
                     aria-labelledby="more-actions-btn"
                     class="hidden absolute right-0 top-full mt-1.5 w-56 bg-white border border-gray-200
                            rounded-lg z-50 overflow-hidden py-1">

                    {{-- Contact Client --}}
                    <button type="button"
                            role="menuitem"
                            id="comm-open-btn-proxy"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700
                                   hover:bg-gray-50 focus:outline-none focus:bg-gray-50 text-left">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Contact Client
                        @if($unreadComms > 0)
                            <span class="ml-auto inline-flex items-center justify-center min-w-[1.1rem] h-4 px-1
                                         rounded-full bg-red-500 text-white text-[10px] font-bold leading-none">
                                {{ $unreadComms }}
                            </span>
                        @endif
                    </button>

                    {{-- Create Task --}}
                    @if (!$isLocked)
                        <button type="button"
                                role="menuitem"
                                id="open-create-task-proxy"
                                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700
                                       hover:bg-gray-50 focus:outline-none focus:bg-gray-50 text-left">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                                         M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            Create Task
                        </button>
                    @endif

                    {{-- Return to Client --}}
                    @if (in_array($application->status, ['submitted', 'wip']))
                        <button type="button"
                                role="menuitem"
                                id="open-return-proxy"
                                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700
                                       hover:bg-gray-50 focus:outline-none focus:bg-gray-50 text-left">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            Return to Client
                        </button>
                    @endif

                    {{-- Expense Calculator --}}
                    @if (!$isLocked)
                        <button type="button"
                                role="menuitem"
                                id="open-expense-proxy"
                                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700
                                       hover:bg-gray-50 focus:outline-none focus:bg-gray-50 text-left">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 002 2v10a2 2 0 002 2z"/>
                            </svg>
                            Expense Calculator
                        </button>
                    @endif

                    {{-- Download Submission PDF --}}
                    @if($submissionPdfExists)
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="/submissions/loan-application-{{ $application->application_number }}.pdf"
                           download="loan-application-{{ $application->application_number }}.pdf"
                           role="menuitem"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-500
                                  hover:bg-gray-50 focus:outline-none focus:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download Submission PDF
                        </a>
                    @endif

                </div>
            </div>

            <div>
                @include('admin.partials.communication.ad-hoc-communication-modal', ['application' => $application])
            </div>

        </div>
    </div>
</div>

{{-- ── Modals (hidden, trigger buttons suppressed) ────────────────────────── --}}

{{-- Communication off-canvas — real trigger button is hidden, proxy fires it --}}
<div class="hidden" aria-hidden="true">
    @include('admin.partials.communication.communication-modal')
</div>

{{-- Create Task modal — real open button is hidden, proxy fires it --}}
<div class="hidden" aria-hidden="true" id="create-task-trigger-wrap">
    @include('admin.applications.partials.show.create-task')
</div>

{{-- Return to Client modal — Alpine controls visibility, proxy clicks the button --}}
<div class="hidden" aria-hidden="true" id="return-trigger-wrap">
    @include('admin.applications.partials.show.returnedToClient')
</div>

{{-- Expense calculator hidden hook (modal rendered elsewhere on the page) --}}
<button type="button"
        id="open-expense-calculator"
        data-expense-modal-open
        class="hidden"
        aria-hidden="true"
        tabindex="-1">
</button>

<script>
(() => {
    const wrap    = document.getElementById('more-actions-wrap');
    const btn     = document.getElementById('more-actions-btn');
    const menu    = document.getElementById('more-actions-menu');
    const chevron = document.getElementById('more-actions-chevron');

    if (!btn || !menu) return;

    function openMenu() {
        menu.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
        chevron.style.transform = 'rotate(180deg)';
        menu.querySelector('[role="menuitem"]')?.focus();
    }

    function closeMenu() {
        menu.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
        chevron.style.transform = '';
    }

    btn.addEventListener('click', () =>
        menu.classList.contains('hidden') ? openMenu() : closeMenu()
    );

    document.addEventListener('click', e => {
        if (!wrap.contains(e.target)) closeMenu();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !menu.classList.contains('hidden')) {
            closeMenu();
            btn.focus();
        }
    });

    menu.addEventListener('keydown', e => {
        const items = [...menu.querySelectorAll('[role="menuitem"]')];
        const idx   = items.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') { e.preventDefault(); items[(idx + 1) % items.length]?.focus(); }
        if (e.key === 'ArrowUp')   { e.preventDefault(); items[(idx - 1 + items.length) % items.length]?.focus(); }
    });

    // Unhide the modal wrapper divs so their JS and Alpine work,
    // but keep the trigger buttons out of tab order / layout flow.
    ['create-task-trigger-wrap', 'return-trigger-wrap'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.removeAttribute('aria-hidden');
        el.classList.remove('hidden');
        // Hide only the visible trigger button inside each wrapper
        el.querySelectorAll('button[aria-controls], button[aria-haspopup]').forEach(triggerBtn => {
            triggerBtn.classList.add('hidden');
            triggerBtn.setAttribute('aria-hidden', 'true');
            triggerBtn.setAttribute('tabindex', '-1');
        });
    });

    // Unhide comm modal root too so its JS initialises
    const commRoot = document.querySelector('#comm-modal-root');
    if (commRoot) {
        commRoot.closest('.hidden')?.classList.remove('hidden');
        // Hide only the trigger button
        document.getElementById('comm-open-btn')?.classList.add('sr-only');
    }

    // Proxy → real trigger mapping
    const proxies = {
        'comm-open-btn-proxy':    () => document.getElementById('comm-open-btn')?.click(),
        'open-create-task-proxy': () => document.getElementById('open-create-task-modal')?.click(),
        'open-return-proxy':      () => document.querySelector('#return-trigger-wrap [aria-controls="return-modal"]')?.click(),
        'open-expense-proxy':     () => document.getElementById('open-expense-calculator')?.click(),
    };

    Object.entries(proxies).forEach(([id, fn]) => {
        document.getElementById(id)?.addEventListener('click', () => {
            closeMenu();
            fn();
        });
    });
})();
</script>