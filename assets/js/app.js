(() => {
    // --- State ---
    let selectedStars = 0;
    let historyData   = {};
    const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // --- DOM refs ---
    const reviewText      = document.getElementById('review_text');
    const charCount       = document.getElementById('char-count');
    const industryEl      = document.getElementById('industry');
    const toneEl          = document.getElementById('tone');
    const starsInput      = document.getElementById('stars');
    const starBtns        = document.querySelectorAll('.star-btn');
    const generateBtn     = document.getElementById('generate-btn');
    const btnText         = document.getElementById('btn-text');
    const btnSpinner      = document.getElementById('btn-spinner');
    const formErrors      = document.getElementById('form-errors');
    const riskWrap        = document.getElementById('risk-wrap');
    const riskBadge       = document.getElementById('risk-badge');
    const riskSub         = document.getElementById('risk-sub');
    const replyCards      = document.getElementById('reply-cards');
    const placeholder     = document.getElementById('results-placeholder');
    const historyList     = document.getElementById('history-list');
    const refreshBtn      = document.getElementById('refresh-history');
    const onboardingModal = document.getElementById('onboarding-modal');
    const onboardingClose = document.getElementById('onboarding-close');
    const historyModal    = document.getElementById('history-modal');
    const historyModalClose = document.getElementById('history-modal-close');

    // --- Config ---
    const variantMeta = [
        { name: 'Kurz & prägnant',  hint: 'Ideal für schnelle, herzliche Reaktionen' },
        { name: 'Ausgewogen',        hint: 'Gute Allround-Wahl für die meisten Reviews' },
        { name: 'Ausführlich',       hint: 'Wenn mehr Kontext oder Erklärung angebracht ist' },
    ];

    const riskConfig = {
        low:    { label: 'Geringes Risiko',  sub: 'Direkt nutzbar — kurze Prüfung empfohlen' },
        medium: { label: 'Mittleres Risiko', sub: 'Leicht anpassen — besonders bei konkreten Details' },
        high:   { label: 'Hohes Risiko',     sub: 'Manuell prüfen — persönlicher Kontakt sinnvoll' },
    };

    // --- Char counter ---
    reviewText.addEventListener('input', () => {
        charCount.textContent = reviewText.value.length;
        updateGenerateBtn();
    });

    // --- Star selection ---
    starBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            selectedStars = parseInt(btn.dataset.value);
            starsInput.value = selectedStars;
            starBtns.forEach(b => b.classList.toggle('active', parseInt(b.dataset.value) <= selectedStars));
            updateGenerateBtn();
        });
    });

    function updateGenerateBtn() {
        generateBtn.disabled = reviewText.value.trim().length < 10 || selectedStars === 0;
    }

    // --- Generate ---
    generateBtn.addEventListener('click', async () => {
        formErrors.classList.add('hidden');
        formErrors.textContent = '';
        setLoading(true);

        try {
            const res = await fetch('/api/generate-review-reply.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
                body: JSON.stringify({
                    review_text: reviewText.value,
                    industry:    industryEl.value,
                    stars:       selectedStars,
                    tone:        toneEl.value,
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                const msgs = data.errors ?? [data.error ?? 'Unbekannter Fehler.'];
                formErrors.innerHTML = msgs.map(e => `<div>${e}</div>`).join('');
                formErrors.classList.remove('hidden');
                return;
            }

            showReplies(data);
            loadHistory();
        } catch {
            formErrors.textContent = 'Netzwerkfehler. Bitte erneut versuchen.';
            formErrors.classList.remove('hidden');
        } finally {
            setLoading(false);
        }
    });

    function setLoading(on) {
        generateBtn.disabled = on;
        btnText.classList.toggle('hidden', on);
        btnSpinner.classList.toggle('hidden', !on);
    }

    function showReplies(data) {
        document.getElementById('reply-text-1').textContent = data.reply_1;
        document.getElementById('reply-text-2').textContent = data.reply_2;
        document.getElementById('reply-text-3').textContent = data.reply_3;

        variantMeta.forEach((m, i) => {
            document.getElementById(`variant-name-${i + 1}`).textContent = m.name;
            document.getElementById(`variant-hint-${i + 1}`).textContent = m.hint;
        });

        const risk = riskConfig[data.risk_level] ?? riskConfig.medium;
        riskBadge.textContent = risk.label;
        riskBadge.className   = `risk-badge risk-${data.risk_level}`;
        riskSub.textContent   = risk.sub;
        riskWrap.classList.remove('hidden');

        placeholder.classList.add('hidden');
        replyCards.classList.remove('hidden');
    }

    // --- Copy buttons ---
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.target);
            navigator.clipboard.writeText(target.textContent).then(() => {
                btn.textContent = 'Kopiert ✓';
                btn.classList.add('copied');
                setTimeout(() => { btn.textContent = 'Kopieren'; btn.classList.remove('copied'); }, 2000);
            });
        });
    });

    // --- History ---
    async function loadHistory() {
        try {
            const res  = await fetch('/api/history.php');
            const data = await res.json();

            if (!data.data || data.data.length === 0) {
                historyList.innerHTML =
                    '<p class="placeholder">Noch keine Einträge. Gib links eine Bewertung ein und generiere deine erste Antwort.</p>';
                return;
            }

            historyData = {};
            data.data.forEach(e => { historyData[e.id] = e; });

            historyList.innerHTML = data.data.map(entry => `
                <div class="history-item" data-id="${entry.id}" role="button" tabindex="0" title="Klicken für Details">
                    <span class="risk-dot ${entry.risk_level}"></span>
                    <div class="history-meta">
                        <span class="history-stars">${'★'.repeat(entry.stars)}${'☆'.repeat(5 - entry.stars)}</span>
                        <span class="history-industry">${escHtml(entry.industry)}</span>
                    </div>
                    <span class="history-text">${escHtml(entry.review_text)}</span>
                    <span class="history-date">${formatDate(entry.created_at)}</span>
                    <button class="history-delete" data-id="${entry.id}" title="Löschen" aria-label="Eintrag löschen">✕</button>
                </div>
            `).join('');

            historyList.querySelectorAll('.history-delete').forEach(btn => {
                btn.addEventListener('click', e => {
                    e.stopPropagation();
                    deleteEntry(parseInt(btn.dataset.id));
                });
            });

            historyList.querySelectorAll('.history-item').forEach(item => {
                item.addEventListener('click', () => {
                    const entry = historyData[parseInt(item.dataset.id)];
                    if (entry) showHistoryModal(entry);
                });
                item.addEventListener('keydown', e => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        item.click();
                    }
                });
            });
        } catch {
            historyList.innerHTML = '<p class="placeholder">Verlauf konnte nicht geladen werden.</p>';
        }
    }

    async function deleteEntry(id) {
        try {
            await fetch('/api/delete-history.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
                body: JSON.stringify({ id }),
            });
            loadHistory();
        } catch {}
    }

    // --- History detail modal ---
    function showHistoryModal(entry) {
        const toneLabels = {
            freundlich:    'Freundlich',
            professionell: 'Professionell',
            entschuldigend:'Entschuldigend',
            premium:       'Premium',
        };

        document.getElementById('hist-modal-meta').innerHTML =
            `<span>${'★'.repeat(entry.stars)}${'☆'.repeat(5 - entry.stars)}</span>` +
            `<span>${escHtml(entry.industry)}</span>` +
            `<span>${toneLabels[entry.tone] ?? escHtml(entry.tone)}</span>` +
            `<span>${formatDate(entry.created_at)}</span>`;

        document.getElementById('hist-modal-review').textContent = entry.review_text;

        const replies = [entry.reply_1, entry.reply_2, entry.reply_3];
        document.getElementById('hist-modal-replies').innerHTML = replies.map((text, i) => `
            <div class="hist-modal-reply">
                <div class="hist-modal-reply-header">
                    <span class="hist-modal-reply-name">${variantMeta[i].name}</span>
                    <button class="copy-btn hist-copy" data-text="${escAttr(text)}">Kopieren</button>
                </div>
                <p class="hist-modal-reply-text">${escHtml(text)}</p>
            </div>
        `).join('');

        historyModal.querySelectorAll('.hist-copy').forEach(btn => {
            btn.addEventListener('click', () => {
                navigator.clipboard.writeText(btn.dataset.text).then(() => {
                    btn.textContent = 'Kopiert ✓';
                    btn.classList.add('copied');
                    setTimeout(() => { btn.textContent = 'Kopieren'; btn.classList.remove('copied'); }, 2000);
                });
            });
        });

        historyModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeHistoryModal() {
        historyModal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    historyModalClose.addEventListener('click', closeHistoryModal);
    historyModal.addEventListener('click', e => { if (e.target === historyModal) closeHistoryModal(); });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeHistoryModal();
            closeOnboarding();
        }
    });

    // --- Onboarding modal ---
    function closeOnboarding() {
        if (!onboardingModal.classList.contains('hidden')) {
            onboardingModal.classList.add('hidden');
            document.body.style.overflow = '';
            localStorage.setItem('rrp_onboarded', '1');
        }
    }

    onboardingClose.addEventListener('click', closeOnboarding);
    onboardingModal.addEventListener('click', e => { if (e.target === onboardingModal) closeOnboarding(); });

    if (!localStorage.getItem('rrp_onboarded')) {
        onboardingModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // --- Helpers ---
    function escHtml(str) {
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function escAttr(str) {
        return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    function formatDate(str) {
        const d = new Date(str.replace(' ', 'T'));
        return d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' })
             + ' ' + d.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
    }

    refreshBtn.addEventListener('click', loadHistory);
    loadHistory();
})();
