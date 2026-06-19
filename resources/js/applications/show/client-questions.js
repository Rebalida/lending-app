// resources/js/applications/client-questions.js
// Handles: answer submission + inline document upload per question card
// Uses shared bank connection logic from basiq and creditsense modules

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const section   = document.getElementById('client-questions-section');
    const csrf      = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const announcer = document.getElementById('client-qa-announcer');
    const toastEl   = document.getElementById('client-qa-toast');

    if (!section) return;

    const ALLOWED_TYPES = [
        'application/pdf', 'image/jpeg', 'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];
    const MAX_SIZE = 10 * 1024 * 1024; // 10 MB

    // ── Get app ID from URL ───────────────────────────────────────────────────
    const getAppId = () => {
        const match = window.location.pathname.match(/applications\/(\d+)/);
        return match?.[1];
    };

    // ── SHARED BANK CONNECTION API CALLS ──────────────────────────────────────

    /**
     * Shared: Create Basiq user (idempotent)
     */
    async function createBasiqUser(appId) {
        const res = await fetch(`/applications/${appId}/basiq/user`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
            },
        });

        const data = await res.json();
        if (!res.ok) {
            throw new Error(
                (data.errors && typeof data.errors === 'object')
                    ? Object.values(data.errors).flat().join('\n')
                    : data.error ?? 'Failed to create Basiq user'
            );
        }
        return data.bank_api_user_ref;
    }

    /**
     * Shared: Create Basiq auth link and return redirect URL
     */
    async function createBasiqAuthLink(appId) {
        const res = await fetch(`/applications/${appId}/basiq/auth-link`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
            },
        });

        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.error ?? 'Failed to create auth link');
        }
        return data.url;
    }

    /**
     * Shared: Fetch CreditSense configuration
     */
    async function fetchCreditSenseConfig(appId) {
        const res = await fetch(`/applications/${appId}/creditsense/config`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(),
            },
        });

        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            throw new Error(data?.message ?? 'Failed to load CreditSense configuration');
        }
        return res.json();
    }

    /**
     * Shared: Dynamically load CreditSense SDK script
     */
    function loadCsSdk(cdnUrl) {
        return new Promise((resolve, reject) => {
            // Already loaded
            if (typeof jQuery !== 'undefined' && jQuery.CreditSense) {
                resolve();
                return;
            }

            // Script already injected
            if (document.getElementById('cs-sdk-script')) {
                // Wait for it
                const existing = document.getElementById('cs-sdk-script');
                const handler = () => {
                    if (typeof jQuery !== 'undefined' && jQuery.CreditSense) {
                        resolve();
                    } else {
                        reject(new Error('CreditSense SDK loaded but not attached to jQuery'));
                    }
                };
                existing.addEventListener('load', handler);
                existing.addEventListener('error', () => reject(new Error('Failed to load CreditSense SDK')));
                return;
            }

            if (typeof jQuery === 'undefined') {
                reject(new Error('jQuery is required by CreditSense SDK but not found'));
                return;
            }

            const script = document.createElement('script');
            script.id = 'cs-sdk-script';
            script.src = cdnUrl;
            script.async = true;

            script.addEventListener('load', () => {
                if (typeof jQuery !== 'undefined' && jQuery.CreditSense) {
                    resolve();
                } else {
                    reject(new Error('CreditSense SDK loaded but jQuery.CreditSense not available'));
                }
            });

            script.addEventListener('error', () => {
                reject(new Error(`Failed to load CreditSense SDK from ${cdnUrl}`));
            });

            document.head.appendChild(script);
        });
    }

    // ── END SHARED BANK CONNECTION CALLS ──────────────────────────────────────

    // ── File input wiring (one per card) ─────────────────────────────────────

    function wireCard(card) {
        const fileInput = card.querySelector('.doc-file-input');
        if (!fileInput || fileInput.dataset.wired) return;
        fileInput.dataset.wired = '1';

        const preview     = card.querySelector('.doc-file-preview');
        const previewName = card.querySelector('.doc-preview-name');
        const previewSize = card.querySelector('.doc-preview-size');
        const clearBtn    = card.querySelector('.doc-clear-btn');
        const uploadError = card.querySelector('.doc-upload-error');

        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (!file) return;

            uploadError?.classList.add('hidden');

            if (!ALLOWED_TYPES.includes(file.type)) {
                showUploadError(uploadError, 'Unsupported file type. Please upload a PDF, image, Word, or Excel file.');
                fileInput.value = '';
                return;
            }
            if (file.size > MAX_SIZE) {
                showUploadError(uploadError, 'File size must not exceed 10 MB.');
                fileInput.value = '';
                return;
            }

            if (previewName) previewName.textContent = file.name;
            if (previewSize) previewSize.textContent = formatBytes(file.size);
            preview?.classList.remove('hidden');
        });

        clearBtn?.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            fileInput.value = '';
            preview?.classList.add('hidden');
            uploadError?.classList.add('hidden');
            card.querySelector('.doc-upload-trigger')?.focus();
        });
    }

    section.querySelectorAll('.question-card').forEach(wireCard);

    // ── Bank connect wiring & dispatcher ──────────────────────────────────────

    function wireBankConnectCard(card) {
        const connectBtn = card.querySelector('.cs-connect-btn');
        if (!connectBtn || connectBtn.dataset.wired) return;
        connectBtn.dataset.wired = '1';

        connectBtn.addEventListener('click', () => launchBankConnect(card));
    }

    section.querySelectorAll('.question-card[data-bank-connect="true"]').forEach(wireBankConnectCard);

    // ── Dispatcher: route to correct provider handler ─────────────────────────

    async function launchBankConnect(card) {
        const provider = card.dataset.bankProvider ?? 'creditsense';
        
        if (provider === 'basiq') {
            await launchBasiqFromQuestion(card);
        } else {
            await launchCreditSenseFromQuestion(card);
        }
    }

    // ── BASIQ handler (uses shared functions) ────────────────────────────────

    async function launchBasiqFromQuestion(card) {
        const questionId = card.dataset.questionId;
        const connectBtn = card.querySelector('.cs-connect-btn');
        const connectIcon = card.querySelector('.cs-connect-icon');
        const connectSpinner = card.querySelector('.cs-connect-spinner');
        const connectLabel = card.querySelector('.cs-connect-label');
        
        if (!connectBtn) return;

        const appId = getAppId();
        if (!appId) {
            showToast('Could not determine application ID', 'error');
            return;
        }

        connectBtn.disabled = true;
        connectIcon?.classList.add('hidden');
        connectSpinner?.classList.remove('hidden');
        if (connectLabel) connectLabel.textContent = 'Connecting…';

        let pollInterval = null;
        let pollTimeouts = 0;
        const MAX_POLL_DURATION = 15 * 60 * 1000; // 15 minutes

        try {
            // Step 1: Create Basiq user
            await createBasiqUser(appId);

            // Step 2: Create auth link
            const authUrl = await createBasiqAuthLink(appId);

            // Step 3: Open in popup
            const popup = window.open(authUrl, 'basiq_consent', 'width=800,height=600');
            
            if (!popup) {
                throw new Error('Popup blocked. Please allow popups and try again.');
            }

            if (connectLabel) connectLabel.textContent = 'Complete the steps, then close the popup…';

            pollInterval = setInterval(async () => {

                // ── Primary signal: popup was closed by user ──────────────────────
                if (popup.closed) {
                    clearInterval(pollInterval);

                    if (connectLabel) connectLabel.textContent = 'Verifying connection…';

                    try {
                        // Always call /complete — it's idempotent, safe to call even if
                        // the server already knows (e.g. via webhook)
                        await fetch(`/applications/${appId}/basiq/complete`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf(),
                            },
                        });

                        await autoAnswerBankConnectQuestion(questionId, appId, card);

                    } catch (e) {
                        console.error('[Basiq] Failed to mark complete after popup closed', e);
                        connectBtn.disabled = false;
                        connectIcon?.classList.remove('hidden');
                        connectSpinner?.classList.add('hidden');
                        if (connectLabel) connectLabel.textContent = 'Connect My Bank';
                        showToast('Could not confirm bank connection. Please try again.', 'error');
                    }
                    return;
                }

                // ── Fallback: server already knows (webhook fired while popup open) ─
                try {
                    const checkRes = await fetch(`/applications/${appId}/basiq/check-completion`, {
                        headers: { 'X-CSRF-TOKEN': csrf() },
                    });
                    const checkData = await checkRes.json();

                    if (checkData.completed) {
                        clearInterval(pollInterval);
                        try { popup.close(); } catch (e) {}
                        await autoAnswerBankConnectQuestion(questionId, appId, card);
                        return;
                    }
                } catch (err) {
                    console.error('[Basiq] Poll check failed:', err);
                }

                // ── Timeout guard ─────────────────────────────────────────────────
                pollTimeouts += 2000;
                if (pollTimeouts > MAX_POLL_DURATION) {
                    clearInterval(pollInterval);
                    try { popup.close(); } catch (e) {}
                    showToast('Connection timed out. Please try again.', 'error');
                    connectBtn.disabled = false;
                    connectIcon?.classList.remove('hidden');
                    connectSpinner?.classList.add('hidden');
                    if (connectLabel) connectLabel.textContent = 'Connect My Bank';
                }

            }, 2000);

        } catch (err) {
            if (pollInterval) clearInterval(pollInterval);
            
            connectBtn.disabled = false;
            connectIcon?.classList.remove('hidden');
            connectSpinner?.classList.add('hidden');
            if (connectLabel) connectLabel.textContent = 'Connect My Bank';
            
            showToast(err.message ?? 'Bank connection failed. Please try again.', 'error');
        }
    }

    // ── CREDITSENSE handler (uses shared functions) ──────────────────────────

    async function launchCreditSenseFromQuestion(card) {
        const questionId   = card.dataset.questionId;
        const connectBtn   = card.querySelector('.cs-connect-btn');
        const connectIcon  = card.querySelector('.cs-connect-icon');
        const connectSpinner = card.querySelector('.cs-connect-spinner');
        const connectLabel = card.querySelector('.cs-connect-label');
        const iframeContainer = card.querySelector('.cs-iframe-container');
        const iframeLoading   = card.querySelector('.cs-iframe-loading');
        const iframeEl        = card.querySelector('.cs-iframe');
        const iframeError     = card.querySelector('.cs-iframe-error');
        const iframeErrorMsg  = card.querySelector('.cs-iframe-error-msg');

        const appId = getAppId();
        if (!appId) {
            showToast('Could not determine application ID', 'error');
            return;
        }

        if (!connectBtn) return;

        connectBtn.disabled = true;
        connectIcon?.classList.add('hidden');
        connectSpinner?.classList.remove('hidden');
        if (connectLabel) connectLabel.textContent = 'Connecting…';

        try {
            const config = await fetchCreditSenseConfig(appId);

            if (config.already_completed) {
                markCardAnswered(card, new Date().toIso8601String());
                return;
            }

            // RESET: Clear error states on new attempt
            iframeError?.classList.add('hidden');
            if (iframeErrorMsg) iframeErrorMsg.textContent = '';
            
            // RESET: Show loading and hide previous iframe content
            iframeLoading?.classList.remove('hidden');
            iframeEl?.classList.add('hidden');
            
            iframeContainer?.classList.remove('hidden');

            const cdnUrl = config.cdn_url
                ?? 'https://6dadc58e31982fd9f0be-d4a1ccb0c1936ef2a5b7f304db75b8a4.ssl.cf4.rackcdn.com/CS-Integrated-Iframe-v1.min.js';
            
            await loadCsSdk(cdnUrl);

            if (typeof jQuery === 'undefined' || !jQuery.CreditSense?.Iframe) {
                throw new Error('CreditSense SDK failed to load');
            }

            // RESET: Clear previous iframe to force fresh initialization
            const iframeSelector = `#creditSenseIFrame-${questionId}`;
            const oldIframe = document.querySelector(iframeSelector);
            if (oldIframe) {
                oldIframe.src = 'about:blank';  // Clear iframe
            }

            jQuery.CreditSense.Iframe({
                client:              config.client_code,
                elementSelector:     iframeSelector,
                enableDynamicHeight: true,
                params: { appRef: config.app_ref, centrelink: true },
                callback: (code) => handleCsCallbackFromQuestion(code, appId, questionId, card, config),
            });

            connectBtn.closest('.flex.items-center')?.classList.add('hidden');

        } catch (err) {
            connectBtn.disabled = false;
            connectIcon?.classList.remove('hidden');
            connectSpinner?.classList.add('hidden');
            if (connectLabel) connectLabel.textContent = 'Connect My Bank';

            const containerVisible = iframeContainer && !iframeContainer.classList.contains('hidden');

            if (containerVisible) {
                iframeLoading?.classList.add('hidden');
                iframeEl?.classList.add('hidden');
                iframeError?.classList.remove('hidden');
                if (iframeErrorMsg) iframeErrorMsg.textContent = err.message ?? 'Connection failed';
            } else {
                showToast(err.message ?? 'Unable to connect to bank. Please try again.', 'error');
            }
        }
    }

    function handleCsCallbackFromQuestion(code, appId, questionId, card, config) {
        const iframeLoading = card.querySelector('.cs-iframe-loading');
        const iframeEl      = card.querySelector('.cs-iframe');
        const iframeError   = card.querySelector('.cs-iframe-error');
        const iframeErrorMsg = card.querySelector('.cs-iframe-error-msg');
        const connectBtn    = card.querySelector('.cs-connect-btn');

        switch (String(code)) {
            case '0':
                iframeLoading?.classList.add('hidden');
                iframeEl?.classList.remove('hidden');
                break;

            case '3':
                if (iframeLoading) iframeLoading.textContent = 'Verifying account…';
                break;

            case '99':
            case '100':
                iframeLoading?.classList.add('hidden');
                iframeEl?.classList.add('hidden');
                autoAnswerBankConnectQuestion(questionId, appId, card);
                break;

            case '-1':
                // Cancelled — allow user to retry
                iframeError?.classList.remove('hidden');
                if (iframeErrorMsg) iframeErrorMsg.textContent = 'Connection was cancelled. Click the button above to try again.';
                
                // Re-enable the connect button for retry
                const connectBtn2 = card.querySelector('.cs-connect-btn');
                if (connectBtn2) {
                    connectBtn2.disabled = false;
                    connectBtn2.classList.remove('hidden');
                    connectBtn2.closest('.flex.items-center')?.classList.remove('hidden');
                }
                break;

            case '-2':
                // Timeout
                iframeError?.classList.remove('hidden');
                if (iframeErrorMsg) iframeErrorMsg.textContent = 'Connection timed out. Please try again.';
                
                // Re-enable the connect button for retry
                const connectBtn3 = card.querySelector('.cs-connect-btn');
                if (connectBtn3) {
                    connectBtn3.disabled = false;
                    connectBtn3.classList.remove('hidden');
                    connectBtn3.closest('.flex.items-center')?.classList.remove('hidden');
                }
                break;
        }
    }

    async function autoAnswerBankConnectQuestion(questionId, appId, card) {
        try {
            // Mark CreditSense complete on server
            await fetch(`/applications/${appId}/creditsense/complete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                },
            });

            // Submit answer
            const answerRes = await fetch(card.dataset.answerRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ answer: 'Bank account connected securely.' }),
            });

            const answerData = await answerRes.json();

            if (answerData.success) {
                markCardAnswered(card, new Date().toIso8601String());
                showToast('Bank connection successful!', 'success');
                announce('Bank account connected and question answered');
            }
        } catch (err) {
            showToast('Bank connected but failed to update question. Please refresh.', 'error');
        }
    }

    // ── Delegated: submit answer button ──────────────────────────────────────

    section.addEventListener('click', e => {
        const btn = e.target.closest('.submit-answer-btn');
        if (btn) handleSubmit(btn);
    });

    section.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const ta = e.target.closest('.answer-input');
            if (!ta) return;
            const qId = ta.dataset.questionId;
            const btn = section.querySelector(`.submit-answer-btn[data-question-id="${qId}"]`);
            if (btn && !btn.disabled) handleSubmit(btn);
        }
    });

    // ── Submit handler ────────────────────────────────────────────────────────

    async function handleSubmit(btn) {
        const questionId  = btn.dataset.questionId;
        const requiresDoc = btn.dataset.requiresDoc === 'true';
        const docCategory = btn.dataset.docCategory;

        const card      = section.querySelector(`#client-question-card-${questionId}`);
        const textarea  = card?.querySelector('.answer-input');
        const answerErr = card?.querySelector('.answer-error');

        if (!card || !textarea) return;

        const answerText = textarea.value.trim();

        if (!answerText) {
            showUploadError(answerErr, 'Please enter an answer.');
            textarea.setAttribute('aria-invalid', 'true');
            textarea.focus();
            return;
        }

        answerErr?.classList.add('hidden');
        textarea.removeAttribute('aria-invalid');

        btn.disabled = true;
        btn.querySelector('.btn-text').textContent = 'Submitting…';
        btn.querySelector('.btn-spinner').classList.remove('hidden');

        try {
            const answerRes = await fetch(`/questions/${questionId}/answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ answer: answerText }),
            });

            const answerData = await answerRes.json();

            if (!answerData.success) {
                throw new Error(answerData.message || 'Failed to submit answer.');
            }

            if (requiresDoc) {
                const fileInput = card.querySelector('.doc-file-input');
                const file = fileInput?.files[0];

                if (file) {
                    await uploadDocument(card, file, docCategory, questionId);
                }
            }

            card._pendingAnswerText = answerText;
            markCardAnswered(card, answerData.answered_at);
            updatePendingBanner();
            showToast(answerData.message ?? 'Answer submitted successfully.', 'success');
            announce(answerData.message ?? 'Answer submitted.');

        } catch (err) {
            showToast(err.message || 'A network error occurred. Please try again.', 'error');
            btn.disabled = false;
            btn.querySelector('.btn-text').textContent = 'Submit Answer';
            btn.querySelector('.btn-spinner').classList.add('hidden');
        }
    }

    // ── Document upload (XHR for progress) ───────────────────────────────────

    function uploadDocument(card, file, docCategory, questionId) {
        const uploadRoute  = card.dataset.uploadRoute;
        const progressWrap = card.querySelector('.doc-upload-progress');
        const progressBar  = card.querySelector('.doc-progress-bar');
        const progressPct  = card.querySelector('.doc-progress-pct');
        const successWrap  = card.querySelector('.doc-upload-success');
        const successName  = card.querySelector('.doc-upload-success-name');
        const uploadError  = card.querySelector('.doc-upload-error');

        return new Promise((resolve) => {
            const formData = new FormData();
            formData.append('document',          file);
            formData.append('document_category', docCategory);
            formData.append('description',       `Uploaded in response to question #${questionId}`);
            formData.append('_token', csrf());

            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', e => {
                if (!e.lengthComputable) return;
                const pct = Math.round((e.loaded / e.total) * 100);
                progressWrap?.classList.remove('hidden');
                if (progressBar) progressBar.style.width = `${pct}%`;
                if (progressPct) progressPct.textContent  = `${pct}%`;
                progressWrap?.querySelector('[role="progressbar"]')
                    ?.setAttribute('aria-valuenow', pct);
            });

            xhr.addEventListener('load', () => {
                progressWrap?.classList.add('hidden');

                let data = null;
                try {
                    data = JSON.parse(xhr.responseText);
                } catch {
                    const friendlyMsg = xhr.status === 500
                        ? 'Server error during upload. Check storage permissions, php_fileinfo extension, and that APP_KEY is set in production.'
                        : `Upload failed with status ${xhr.status}.`;
                    showUploadError(uploadError, friendlyMsg);
                    console.error('[DocumentUpload] Non-JSON response:', xhr.status, xhr.responseText.slice(0, 300));
                    resolve(null);
                    return;
                }

                if (xhr.status >= 200 && xhr.status < 300 && data?.success) {
                    if (successName) successName.textContent = file.name;
                    successWrap?.classList.remove('hidden');
                    announce(`Document "${file.name}" uploaded successfully.`);
                    resolve(data);
                } else {
                    const msg = data?.message
                        ?? data?.error
                        ?? 'Document upload failed.';
                    showUploadError(uploadError, msg);
                    console.error('[DocumentUpload] Server rejected upload:', xhr.status, data);
                    resolve(null);
                }
            });

            xhr.addEventListener('error', () => {
                progressWrap?.classList.add('hidden');
                showUploadError(uploadError, 'Network error during upload. Please try uploading the document separately.');
                resolve(null);
            });

            xhr.open('POST', uploadRoute);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrf());
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.send(formData);
        });
    }

    // ── DOM: mark a card as answered ─────────────────────────────────────────

    function markCardAnswered(card, answeredAt) {
        const isBankConnect = card.dataset.bankConnect === 'true';

        card.classList.replace('bg-amber-50', 'bg-gray-50');
        card.classList.replace('border-amber-200', 'border-gray-200');
        card.dataset.status = 'answered';

        const badge = card.querySelector('.question-status');
        if (badge) {
            badge.className = badge.className
                .replace('bg-amber-100 text-amber-700', 'bg-green-100 text-green-700');
            badge.textContent = 'Answered';
            badge.setAttribute('aria-label', 'Status: Answered');
        }

        const iconWrap = card.querySelector('[aria-hidden="true"].rounded-full');
        if (iconWrap) {
            iconWrap.classList.replace('bg-amber-100', 'bg-gray-200');
            iconWrap.querySelector('svg')?.classList.replace('text-amber-600', 'text-gray-500');
        }

        if (answeredAt) {
            const meta = card.querySelector('.text-xs.text-gray-500');
            if (meta) {
                const base = meta.textContent.trim().split('·')[0].trim();
                meta.textContent = `${base} · Answered ${answeredAt}`;
            }
        }

        const form = card.querySelector('.answer-form');
        if (form) {
            const div = document.createElement('div');

            if (isBankConnect) {
                div.className = 'answer-display mt-1 flex items-center gap-2.5 px-3 py-2.5 rounded-xl border border-green-200 bg-green-50';
                div.setAttribute('role', 'status');
                div.innerHTML = `
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-xs font-semibold text-green-800">Bank account connected</p>
                        ${answeredAt ? `<p class="text-xs text-green-600">Completed ${esc(answeredAt)}</p>` : ''}
                    </div>`;
            } else {
                div.className = 'answer-display mt-1 p-3 bg-white rounded-xl border border-gray-200';
                div.innerHTML = `<p class="text-sm text-gray-700 whitespace-pre-wrap">${esc(card._pendingAnswerText ?? '')}</p>`;
            }

            form.replaceWith(div);
        }
    }

    // ── Pending banner update ─────────────────────────────────────────────────

    function updatePendingBanner() {
        const remaining = section.querySelectorAll('.question-card[data-status="pending"]').length;
        const banner    = document.getElementById('pending-questions-warning');
        if (!banner) return;

        if (remaining === 0) {
            banner.style.opacity = '0';
            banner.style.transition = 'opacity 0.3s ease';
            setTimeout(() => banner.remove(), 300);
        } else {
            const countEl = document.getElementById('pending-count');
            const badgeEl = document.getElementById('pending-badge');
            if (countEl) countEl.textContent = remaining;
            if (badgeEl) badgeEl.textContent  = remaining;
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    let toastTimer = null;

    function showToast(message, type = 'success') {
        if (!toastEl) return;
        const ok = type === 'success';
        toastEl.className = `mb-4 p-3 rounded-xl text-sm font-medium border ${
            ok ? 'bg-green-50 border-green-200 text-green-800'
               : 'bg-red-50 border-red-200 text-red-800'}`;
        toastEl.textContent = message;
        toastEl.classList.remove('hidden');
        toastEl.focus();
        clearTimeout(toastTimer);
        if (ok) toastTimer = setTimeout(() => toastEl.classList.add('hidden'), 4000);
    }

    function announce(msg) {
        if (!announcer) return;
        announcer.textContent = '';
        requestAnimationFrame(() => { announcer.textContent = msg; });
    }

    function showUploadError(el, msg) {
        if (!el) return;
        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function formatBytes(bytes) {
        if (!bytes) return '0 B';
        const k = 1024, sizes = ['B','KB','MB','GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${(bytes / Math.pow(k, i)).toFixed(1)} ${sizes[i]}`;
    }

    function esc(str) {
        return String(str ?? '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    window.scrollToQuestions = function () {
        if (!section) return;
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        section.style.boxShadow = '0 0 0 3px rgba(99,102,241,0.3)';
        setTimeout(() => section.style.boxShadow = '', 1500);
        setTimeout(() => {
            section.querySelector('.answer-input')?.focus();
        }, 500);
    };

});