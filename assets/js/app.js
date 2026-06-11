(() => {
    // --- State ---
    let selectedStars = 0;

    // --- DOM refs ---
    const reviewText    = document.getElementById('review_text');
    const charCount     = document.getElementById('char-count');
    const industryEl    = document.getElementById('industry');
    const toneEl        = document.getElementById('tone');
    const starsInput    = document.getElementById('stars');
    const starBtns      = document.querySelectorAll('.star-btn');
    const generateBtn   = document.getElementById('generate-btn');
    const btnText       = document.getElementById('btn-text');
    const btnSpinner    = document.getElementById('btn-spinner');
    const formErrors    = document.getElementById('form-errors');
    const riskBadge     = document.getElementById('risk-badge');
    const replyCards    = document.getElementById('reply-cards');
    const placeholder   = document.getElementById('results-placeholder');
    const historyList   = document.getElementById('history-list');
    const refreshBtn    = document.getElementById('refresh-history');

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
        generateBtn.disabled = reviewText.value.trim() === '' || selectedStars === 0;
    }

    // --- Generate ---
    generateBtn.addEventListener('click', async () => {
        formErrors.classList.add('hidden');
        formErrors.textContent = '';
        setLoading(true);

        try {
            const res = await fetch('/api/generate-review-reply.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
        } catch (e) {
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

        const riskLabels = { low: 'Geringes Risiko', medium: 'Mittleres Risiko', high: 'Hohes Risiko – persönlicher Kontakt empfohlen' };
        riskBadge.textContent = riskLabels[data.risk_level] ?? '';
        riskBadge.className = `risk-badge risk-${data.risk_level}`;
        riskBadge.classList.remove('hidden');

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
                historyList.innerHTML = '<p class="placeholder">Noch keine Einträge.</p>';
                return;
            }

            historyList.innerHTML = data.data.map(entry => `
                <div class="history-item" data-id="${entry.id}">
                    <span class="risk-dot ${entry.risk_level}"></span>
                    <div class="history-meta">
                        <span class="history-stars">${'★'.repeat(entry.stars)}${'☆'.repeat(5 - entry.stars)}</span>
                        <span class="history-industry">${entry.industry}</span>
                    </div>
                    <span class="history-text">${escHtml(entry.review_text)}</span>
                    <span class="history-date">${formatDate(entry.created_at)}</span>
                    <button class="history-delete" data-id="${entry.id}" title="Löschen">✕</button>
                </div>
            `).join('');

            historyList.querySelectorAll('.history-delete').forEach(btn => {
                btn.addEventListener('click', () => deleteEntry(parseInt(btn.dataset.id)));
            });
        } catch (e) {
            historyList.innerHTML = '<p class="placeholder">Verlauf konnte nicht geladen werden.</p>';
        }
    }

    async function deleteEntry(id) {
        try {
            await fetch('/api/delete-history.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id }),
            });
            loadHistory();
        } catch (e) {}
    }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function formatDate(str) {
        const d = new Date(str.replace(' ', 'T'));
        return d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' })
             + ' ' + d.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
    }

    refreshBtn.addEventListener('click', loadHistory);
    loadHistory();
})();
