{{-- =========================================================================
     Approval / Decline Letter Modal
     =========================================================================
     Triggered by #btn-approve and #btn-decline in quick-actions.blade.php.
     On confirm:
       1. PATCHes the new status via admin.applications.updateStatus
       2. POSTs the letter via admin.email.send
     Both requests use the existing routes — no new backend work required.
     ========================================================================= --}}

@php
    $clientName  = $application->personalDetails?->user?->first_name
                ?? $application->user->name;
    $appNumber   = $application->application_number;
    $loanAmount  = number_format($application->loan_amount, 2);
    $loanPurpose = ucwords(str_replace('_', ' ', $application->loan_purpose ?? ''));
    $termMonths  = $application->term_months;
    $fromName    = config('app.name', 'Our Team');
@endphp

{{-- ── Backdrop ─────────────────────────────────────────────────────────── --}}
<div id="adl-backdrop"
     class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-40"
     aria-hidden="true"></div>

{{-- ── Modal ────────────────────────────────────────────────────────────── --}}
<div id="adl-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="adl-title">

    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl flex flex-col max-h-[90vh]">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <div class="flex items-center gap-3">
                {{-- Icon — swapped by JS --}}
                <div id="adl-icon"
                     class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center">
                </div>
                <div>
                    <h2 id="adl-title" class="text-base font-semibold text-gray-900"></h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $appNumber }} · {{ $clientName }}
                    </p>
                </div>
            </div>
            <button type="button"
                    id="adl-close"
                    class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5 focus:outline-none
                           focus:ring-2 focus:ring-indigo-500 transition"
                    aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Notice banner --}}
        <div id="adl-notice"
             class="hidden mx-6 mt-4 rounded-lg px-4 py-3 text-sm font-medium"
             role="alert"
             aria-live="polite">
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">

            <p class="text-sm text-gray-600">
                Review and edit the letter below before sending. The status will be updated
                and the email dispatched when you click <strong>Send Letter</strong>.
            </p>

            {{-- Subject --}}
            <div>
                <label for="adl-subject"
                       class="block text-sm font-medium text-gray-700 mb-1">
                    Subject
                </label>
                <input type="text"
                       id="adl-subject"
                       maxlength="255"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Email subject…">
            </div>

            {{-- Body --}}
            <div>
                <label for="adl-body"
                       class="block text-sm font-medium text-gray-700 mb-1">
                    Message
                </label>
                <textarea id="adl-body"
                          rows="14"
                          maxlength="5000"
                          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm font-mono
                                 leading-relaxed focus:outline-none focus:ring-2 focus:ring-indigo-500
                                 focus:border-indigo-500 resize-y"
                          placeholder="Letter body…"></textarea>
                <p class="mt-1 text-xs text-gray-400 text-right">
                    <span id="adl-char-count">0</span> / 5000
                </p>
            </div>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl gap-3">
            <button type="button"
                    id="adl-cancel"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300
                           rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500
                           focus:ring-offset-2 transition">
                Cancel
            </button>

            <button type="button"
                    id="adl-send"
                    class="inline-flex items-center gap-2 px-5 py-2 border border-transparent rounded-md
                           font-semibold text-sm text-white focus:outline-none focus:ring-2
                           focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition">
                <svg id="adl-send-spinner"
                     class="hidden animate-spin h-4 w-4 text-white"
                     fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span id="adl-send-label">Send Letter</span>
            </button>
        </div>

    </div>
</div>

<script>
    (() => {
        // ── Config ────────────────────────────────────────────────────────────────
        const APP_ID     = @js($application->id);
        const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content;
        const CLIENT     = @js($clientName);
        const APP_NO     = @js($appNumber);
        const AMOUNT     = @js($loanAmount);
        const PURPOSE    = @js($loanPurpose);
        const TERM       = @js($termMonths);
        const FROM_NAME  = @js($fromName);

        const EMAIL_URL  = `/admin/applications/${APP_ID}/send-email`;
        const STATUS_URL = `/admin/applications/${APP_ID}/status`;

        // ── Letter templates ──────────────────────────────────────────────────────
        const TEMPLATES = {
            approve: {
                title:      'Send Approval Letter',
                newStatus:  'approved',
                iconBg:     'bg-green-100',
                iconColor:  'text-green-600',
                btnBg:      'bg-green-600 hover:bg-green-700 focus:ring-green-500',
                noticeBg:   'bg-green-50 text-green-800 border border-green-200',
                iconSvg: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>`,
                subject: `Loan Application ${APP_NO} — Approved`,
                body:
    `Dear ${CLIENT},

    We are pleased to inform you that your loan application (${APP_NO}) has been approved.

    Loan Details:
    • Amount:   $${AMOUNT}
    • Purpose:  ${PURPOSE}
    • Term:     ${TERM} months

    Our team will be in touch shortly with the formal offer documents and next steps to finalise your loan.

    If you have any questions in the meantime, please do not hesitate to contact us.

    Congratulations and thank you for choosing us.

    Warm regards,
    ${FROM_NAME}`,
            },

            decline: {
                title:      'Send Decline Letter',
                newStatus:  'declined',
                iconBg:     'bg-red-100',
                iconColor:  'text-red-600',
                btnBg:      'bg-red-600 hover:bg-red-700 focus:ring-red-500',
                noticeBg:   'bg-red-50 text-red-800 border border-red-200',
                iconSvg: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>`,
                subject: `Loan Application ${APP_NO} — Outcome`,
                body:
    `Dear ${CLIENT},

    Thank you for submitting your loan application (${APP_NO}) and for taking the time to provide the required information.

    After careful consideration of your application, we regret to inform you that we are unable to approve your request for financing at this time.

    This decision was reached after a thorough review of the information provided. Unfortunately we are not able to disclose the specific reasons for this outcome.

    We encourage you to continue building your financial position and welcome you to re-apply in the future should your circumstances change.

    If you have any questions or would like to discuss your options, please do not hesitate to contact our office.

    Thank you again for considering us.

    Kind regards,
    ${FROM_NAME}`,
            },
        };

        // ── Element refs ──────────────────────────────────────────────────────────
        const backdrop    = document.getElementById('adl-backdrop');
        const modal       = document.getElementById('adl-modal');
        const iconEl      = document.getElementById('adl-icon');
        const titleEl     = document.getElementById('adl-title');
        const noticeEl    = document.getElementById('adl-notice');
        const subjectEl   = document.getElementById('adl-subject');
        const bodyEl      = document.getElementById('adl-body');
        const charCount   = document.getElementById('adl-char-count');
        const sendBtn     = document.getElementById('adl-send');
        const sendSpinner = document.getElementById('adl-send-spinner');
        const sendLabel   = document.getElementById('adl-send-label');
        const closeBtn    = document.getElementById('adl-close');
        const cancelBtn   = document.getElementById('adl-cancel');

        let currentType = null; // 'approve' | 'decline'

        // ── Open ──────────────────────────────────────────────────────────────────
        function open(type) {
            const cfg = TEMPLATES[type];
            if (! cfg) return;

            currentType = type;

            // Reset notice
            hideNotice();

            // Populate header
            titleEl.textContent = cfg.title;
            iconEl.className    = `flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center ${cfg.iconBg} ${cfg.iconColor}`;
            iconEl.innerHTML    = cfg.iconSvg;

            // Populate send button colour
            sendBtn.className = sendBtn.className.replace(
                /bg-\S+ hover:bg-\S+ focus:ring-\S+/g, ''
            );
            sendBtn.classList.add(...cfg.btnBg.split(' '));

            // Populate fields
            subjectEl.value = cfg.subject;
            bodyEl.value    = cfg.body;
            updateCharCount();

            // Show
            backdrop.classList.remove('hidden');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Focus subject so admin can tweak immediately
            subjectEl.focus();
        }

        // ── Close ─────────────────────────────────────────────────────────────────
        function close() {
            modal.classList.add('hidden');
            backdrop.classList.add('hidden');
            document.body.style.overflow = '';
            currentType = null;
            setSending(false);
        }

        // ── Char counter ──────────────────────────────────────────────────────────
        function updateCharCount() {
            charCount.textContent = bodyEl.value.length;
        }

        bodyEl.addEventListener('input', updateCharCount);

        // ── Notice helpers ────────────────────────────────────────────────────────
        function showNotice(msg, isError = false) {
            noticeEl.textContent = msg;
            noticeEl.className   = `mx-6 mt-4 rounded-lg px-4 py-3 text-sm font-medium ${
                isError
                    ? 'bg-red-50 text-red-800 border border-red-200'
                    : TEMPLATES[currentType]?.noticeBg ?? 'bg-green-50 text-green-800 border border-green-200'
            }`;
            noticeEl.classList.remove('hidden');
        }

        function hideNotice() {
            noticeEl.classList.add('hidden');
            noticeEl.textContent = '';
        }

        // ── Loading state ─────────────────────────────────────────────────────────
        function setSending(active) {
            sendBtn.disabled = active;
            sendSpinner.classList.toggle('hidden', ! active);
            sendLabel.textContent = active ? 'Sending…' : 'Send Letter';
        }

        // ── Send ──────────────────────────────────────────────────────────────────
        async function send() {
            const subject = subjectEl.value.trim();
            const message = bodyEl.value.trim();
            const cfg     = TEMPLATES[currentType];

            if (! subject) { showNotice('Please enter a subject.', true); subjectEl.focus(); return; }
            if (! message) { showNotice('Please enter a message body.', true); bodyEl.focus(); return; }

            hideNotice();
            setSending(true);

            // ── Step 1: Update status ─────────────────────────────────────────────
            try {
                const statusRes = await fetch(STATUS_URL, {
                    method: 'POST', // PATCH via _method spoofing
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: new URLSearchParams({
                        _method: 'PATCH',
                        status:  cfg.newStatus,
                    }),
                });

                // The controller returns a redirect (302) in non-JSON mode,
                // so we accept both 200 and 302 as success signals.
                // A JSON 422/500 means validation or lock failure.
                if (statusRes.headers.get('content-type')?.includes('application/json')) {
                    const json = await statusRes.json();
                    if (! statusRes.ok || json.errors) {
                        const msg = json.message ?? 'Could not update application status.';
                        showNotice(msg, true);
                        setSending(false);
                        return;
                    }
                }
                // Non-JSON (redirect) response is fine — the PATCH succeeded.

            } catch (err) {
                showNotice('Network error updating status. Please try again.', true);
                setSending(false);
                return;
            }

            // ── Step 2: Send email ────────────────────────────────────────────────
            try {
                const emailRes = await fetch(EMAIL_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ subject, message }),
                });

                const json = await emailRes.json();

                if (! emailRes.ok || ! json.success) {
                    // Status already changed — warn but don't block.
                    showNotice(
                        `Status updated to "${cfg.newStatus}", but the email could not be sent. ` +
                        `Please use Contact Client to send it manually.`,
                        true
                    );
                    setSending(false);
                    return;
                }

            } catch (err) {
                showNotice(
                    `Status updated to "${cfg.newStatus}", but a network error prevented the email. ` +
                    `Please send it manually via Contact Client.`,
                    true
                );
                setSending(false);
                return;
            }

            // ── All good — close and reload ───────────────────────────────────────
            close();
            // Reload so the status badge, dropdown, and locked-status UI all reflect the change.
            window.location.reload();
        }

        // ── Wire events ───────────────────────────────────────────────────────────
        sendBtn.addEventListener('click', send);
        closeBtn.addEventListener('click', close);
        cancelBtn.addEventListener('click', close);
        backdrop.addEventListener('click', close);

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && ! modal.classList.contains('hidden')) close();
        });

        // Trigger buttons (defined in quick-actions.blade.php)
        document.getElementById('btn-approve')?.addEventListener('click', () => open('approve'));
        document.getElementById('btn-decline')?.addEventListener('click', () => open('decline'));

        // Expose for any future programmatic use
        window.ApprovalDeclineModal = { open, close };
    })();
</script>