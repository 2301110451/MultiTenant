import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function attachLivePolling() {
    const nodes = document.querySelectorAll('[data-live-endpoint]');
    if (nodes.length === 0) {
        return;
    }

    const state = new WeakMap();
    const resolveInterval = (container) => {
        const intervalMs = Number(container.getAttribute('data-live-interval') ?? '15000');

        return Number.isFinite(intervalMs) && intervalMs >= 3000 ? intervalMs : 15000;
    };

    const runPoll = async (container) => {
        const endpoint = container.getAttribute('data-live-endpoint');
        if (!endpoint) {
            return;
        }

        const localState = state.get(container) ?? { inFlight: false, failures: 0, timer: null };
        if (localState.inFlight) {
            return;
        }
        localState.inFlight = true;
        state.set(container, localState);

        try {
            const response = await window.axios.get(endpoint, { headers: { Accept: 'application/json' } });
            const payload = response.data ?? {};
            localState.failures = 0;

            container.querySelectorAll('[data-live-key]').forEach((node) => {
                const key = node.getAttribute('data-live-key');
                if (!key) return;
                if (payload[key] === undefined || payload[key] === null) return;

                const newValue = String(payload[key]);
                if (node.textContent !== newValue) {
                    node.style.transition = 'opacity 0.2s ease';
                    node.style.opacity = '0.5';
                    requestAnimationFrame(() => {
                        node.textContent = newValue;
                        node.style.opacity = '1';
                    });
                }
            });
        } catch (_error) {
            localState.failures += 1;
        } finally {
            localState.inFlight = false;
        }
    };

    const schedulePoll = (container) => {
        const localState = state.get(container) ?? { inFlight: false, failures: 0, timer: null };
        const baseInterval = resolveInterval(container);
        const backoffMultiplier = Math.min(Math.max(localState.failures, 0), 3);
        const nextInterval = baseInterval * (2 ** backoffMultiplier);

        if (localState.timer) {
            clearTimeout(localState.timer);
        }

        localState.timer = setTimeout(async () => {
            if (document.visibilityState === 'visible') {
                await runPoll(container);
            }
            schedulePoll(container);
        }, nextInterval);

        state.set(container, localState);
    };

    nodes.forEach((container) => {
        // Avoid an immediate XHR right after first paint — the page already has SSR values;
        // polling only needs to refresh on the interval.
        schedulePoll(container);
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState !== 'visible') {
            return;
        }

        nodes.forEach((container) => {
            runPoll(container);
        });
    });
}

document.addEventListener('DOMContentLoaded', attachLivePolling);
