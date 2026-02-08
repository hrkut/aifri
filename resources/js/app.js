import './bootstrap';

import Sortable from 'sortablejs';

// Enable drag & drop ordering + inline duration edit on Admin → Program page
document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.querySelector('[data-program-sortable="true"]');
    if (!tbody) return;

    const reorderUrl = tbody.getAttribute('data-reorder-url');
    if (!reorderUrl) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const programDurationInputs = () => document.querySelectorAll('[data-program-duration-input]');

    const postJson = async (url, payload) => {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload ?? {}),
        });

        if (!res.ok) {
            let msg = 'Request failed';
            try {
                const data = await res.json();
                msg = data?.message ?? msg;
            } catch (e) {}
            throw new Error(msg);
        }

        try {
            return await res.json();
        } catch (e) {
            return null;
        }
    };

    const applyTimes = (times) => {
        if (!times) return;
        Object.entries(times).forEach(([rid, time]) => {
            const tElement = document.querySelector(`[data-program-time-input][data-id="${rid}"]`);
            if (tElement) {
                tElement.textContent = time || '—';
            }
        });
    };

    // ------------------ Inline duration editing ------------------
    const saveDuration = async (input) => {
        const id = input.getAttribute('data-id');
        const value = input.value;
        if (!id || value === '' || value === null) return;

        const minutes = Number(value);
        if (!Number.isFinite(minutes) || minutes < 0) return;

        input.disabled = true;
        input.classList.add('opacity-70');

        try {
            const data = await postJson(`${window.location.origin}/admin/program/${id}/duration`, { duration_minutes: minutes });
            applyTimes(data?.times);
        } catch (e) {
            alert(e.message || 'Nepodarilo sa uložiť trvanie.');
        } finally {
            input.disabled = false;
            input.classList.remove('opacity-70');
        }
    };

    programDurationInputs().forEach((input) => {
        // Save on blur (when user clicks away)
        input.addEventListener('blur', () => saveDuration(input));

        // Save on Enter key
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveDuration(input);
                input.blur();
            }
        });
    });

    // ------------------ Drag & drop ordering ------------------
    const syncReorder = async () => {
        const ids = Array.from(tbody.querySelectorAll('tr[data-id]'))
            .map((tr) => Number(tr.getAttribute('data-id')))
            .filter((n) => Number.isFinite(n) && n > 0);

        const res = await fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ ids }),
        });

        if (!res.ok) {
            let msg = 'Reorder failed';
            try {
                const data = await res.json();
                msg = data?.message ?? msg;
            } catch (e) {
                msg = `HTTP ${res.status}: ${res.statusText}`;
            }
            throw new Error(msg);
        }

        try {
            return await res.json();
        } catch (e) {
            throw new Error('Invalid JSON response from server');
        }
    };

    Sortable.create(tbody, {
        animation: 150,
        handle: '[data-drag-handle="true"]',
        onEnd: () => {
            syncReorder()
                .then((data) => {
                    applyTimes(data?.times);
                })
                .catch((e) => {
                    alert(e.message || 'Nepodarilo sa presunúť príspevok');
                });
        },
    });

});
