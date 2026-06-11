/**
 * floating-ui powered tooltips + guided onboarding popover
 *
 * Übernimmt die Positionierung aller .tip-box Elemente.
 * Fallback: reines CSS (hover/focus-within) bleibt aktiv.
 */

const { computePosition, flip, shift, offset, arrow } = FloatingUIDOM;

// ── Tooltip-System ────────────────────────────────────────────────────────────

function setupTooltips() {
    document.querySelectorAll('.tip-wrap').forEach(wrap => {
        const trigger = wrap.querySelector('.tip-icon');
        const box     = wrap.querySelector('.tip-box');
        if (!trigger || !box) return;

        // floating-ui übernimmt Position — CSS-Klasse steuert Sichtbarkeit
        box.style.position = 'fixed';
        box.style.zIndex   = '999';
        box.style.display  = 'none';

        async function position() {
            const { x, y } = await computePosition(trigger, box, {
                placement: 'top',
                middleware: [
                    offset(8),
                    flip({ fallbackPlacements: ['bottom', 'right', 'left'] }),
                    shift({ padding: 8 }),
                ],
            });
            box.style.left = `${x}px`;
            box.style.top  = `${y}px`;
        }

        function show() {
            box.style.display = 'block';
            position();
        }

        function hide() {
            box.style.display = 'none';
        }

        trigger.addEventListener('mouseenter', show);
        trigger.addEventListener('mouseleave', hide);
        trigger.addEventListener('focus',      show);
        trigger.addEventListener('blur',       hide);
    });
}

// ── Guided Onboarding Popover ─────────────────────────────────────────────────

const STEPS = [
    {
        anchor:  '#review_text',
        title:   'Schritt 1 – Bewertung einfügen',
        text:    'Füge hier den Google-Bewertungstext ein. Mindestens 10 Zeichen — am besten die vollständige Kundenbewertung.',
        placement: 'bottom',
    },
    {
        anchor:  '#tone',
        title:   'Schritt 2 – Ton wählen',
        text:    'Wähle den Kommunikationsstil. <em>Freundlich</em> für herzliche Antworten, <em>Professionell</em> für sachliche, <em>Entschuldigend</em> zum Deeskalieren.',
        placement: 'bottom',
    },
    {
        anchor:  '#generate-btn',
        title:   'Schritt 3 – Generieren & prüfen',
        text:    'Klicke hier, um drei Antwortvarianten zu erstellen. Immer kurz lesen, bevor du eine kopierst — die Antworten gehören zu deiner Marke.',
        placement: 'top',
    },
];

let currentStep = 0;
let popover = null;

function createPopover() {
    const el = document.createElement('div');
    el.id = 'guided-popover';
    el.setAttribute('role', 'dialog');
    el.setAttribute('aria-modal', 'false');
    el.innerHTML = `
        <div class="gp-header">
            <span class="gp-step-indicator"></span>
            <button class="gp-close" aria-label="Tour beenden">✕</button>
        </div>
        <p class="gp-title"></p>
        <p class="gp-text"></p>
        <div class="gp-footer">
            <button class="gp-btn gp-skip">Überspringen</button>
            <button class="gp-btn gp-primary gp-next">Weiter</button>
        </div>
    `;
    document.body.appendChild(el);

    el.querySelector('.gp-close').addEventListener('click', dismissPopover);
    el.querySelector('.gp-skip').addEventListener('click', dismissPopover);
    el.querySelector('.gp-next').addEventListener('click', nextStep);

    return el;
}

async function showStep(step) {
    if (!popover) popover = createPopover();

    const cfg    = STEPS[step];
    const anchor = document.querySelector(cfg.anchor);
    if (!anchor) { nextStep(); return; }

    popover.querySelector('.gp-step-indicator').textContent = `${step + 1} / ${STEPS.length}`;
    popover.querySelector('.gp-title').textContent          = cfg.title;
    popover.querySelector('.gp-text').innerHTML             = cfg.text;

    const nextBtn = popover.querySelector('.gp-next');
    nextBtn.textContent = step === STEPS.length - 1 ? 'Fertig' : 'Weiter';

    popover.style.display = 'block';
    popover.style.position = 'fixed';
    popover.style.zIndex   = '1000';

    const { x, y } = await computePosition(anchor, popover, {
        placement: cfg.placement,
        middleware: [
            offset(12),
            flip({ fallbackPlacements: ['bottom', 'top', 'right'] }),
            shift({ padding: 12 }),
        ],
    });

    popover.style.left = `${x}px`;
    popover.style.top  = `${y}px`;

    // Highlight-Umrandung am Anker-Element
    document.querySelectorAll('.gp-highlight').forEach(el => el.classList.remove('gp-highlight'));
    anchor.classList.add('gp-highlight');
}

function nextStep() {
    currentStep++;
    if (currentStep >= STEPS.length) {
        dismissPopover();
        return;
    }
    showStep(currentStep);
}

function dismissPopover() {
    if (popover) popover.style.display = 'none';
    document.querySelectorAll('.gp-highlight').forEach(el => el.classList.remove('gp-highlight'));
    localStorage.setItem('rrp_tour_done', '1');
}

function startTour() {
    if (localStorage.getItem('rrp_tour_done')) return;
    currentStep = 0;
    showStep(0);
}

// ── Init ──────────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    setupTooltips();

    // Tour erst nach Schließen des Onboarding-Modals starten (falls sichtbar)
    const onboardingModal = document.getElementById('onboarding-modal');
    const onboardingClose = document.getElementById('onboarding-close');

    if (onboardingModal && !onboardingModal.classList.contains('hidden')) {
        // Onboarding ist gerade sichtbar → Tour danach starten
        if (onboardingClose) {
            onboardingClose.addEventListener('click', () => {
                setTimeout(startTour, 400);
            }, { once: true });
        }
    } else {
        // Onboarding war schon gesehen → Tour direkt starten (falls noch nicht erledigt)
        setTimeout(startTour, 600);
    }
});
