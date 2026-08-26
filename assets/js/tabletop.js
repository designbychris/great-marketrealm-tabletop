(function () {
    'use strict';

    const root = document.querySelector('.gmrt-chamber');
    const board = document.querySelector('.gmrt-board__viewport');
    const status = document.querySelector('#gmrt-tabletop-status');

    if (!root || !board || !window.gmrtTabletop) {
        return;
    }

    const tableId = root.dataset.tableId || '';
    let selected = null;
    let refreshTimer = null;

    function say(message) {
        if (status) {
            status.textContent = message;
        }
    }

    function payload(action, values) {
        const body = new URLSearchParams();
        body.set('action', action);
        body.set('nonce', gmrtTabletop.nonce);
        body.set('table_id', tableId);

        Object.entries(values || {}).forEach(([key, value]) => {
            body.set(key, String(value));
        });

        return body;
    }

    async function request(action, values) {
        const response = await fetch(gmrtTabletop.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
            },
            body: payload(action, values)
        });

        const data = await response.json();

        if (!data.success) {
            const message = data.data && data.data.message
                ? data.data.message
                : 'The Tabletop rejected that request.';
            throw new Error(message);
        }

        return data.data;
    }

    function select(token) {
        if (selected) {
            selected.classList.remove('is-selected');
            selected.setAttribute('aria-pressed', 'false');
        }

        selected = token;

        if (selected) {
            selected.classList.add('is-selected');
            selected.setAttribute('aria-pressed', 'true');
            say('Selected ' + (selected.title || 'token') + '.');
        }
    }

    function coordinatesFromPointer(event) {
        const rect = board.getBoundingClientRect();

        return {
            x: Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width)),
            y: Math.max(0, Math.min(1, (event.clientY - rect.top) / rect.height))
        };
    }

    async function moveSelected(x, y) {
        if (!selected || !tableId) {
            return;
        }

        const tokenId = selected.dataset.tokenId || '';
        const revision = Number(selected.dataset.tokenRevision || '1');

        try {
            const data = await request('gmrt_move_token', {
                token_id: tokenId,
                x: x,
                y: y,
                revision: revision
            });

            const token = data.token;
            selected.style.setProperty('--gmrt-token-x', (token.x * 100) + '%');
            selected.style.setProperty('--gmrt-token-y', (token.y * 100) + '%');
            selected.dataset.tokenRevision = String(token.revision);
            say((token.label || 'Token') + ' moved.');
        } catch (error) {
            say(error.message);
            await refresh();
        }
    }

    async function refresh() {
        if (!tableId) {
            return;
        }

        try {
            const state = await request('gmrt_tabletop_state', {});
            const tokens = Array.isArray(state.tokens) ? state.tokens : [];

            tokens.forEach((token) => {
                const node = document.querySelector(
                    '[data-token-id="' + CSS.escape(String(token.id)) + '"]'
                );

                if (!node) {
                    return;
                }

                node.style.setProperty('--gmrt-token-x', (token.x * 100) + '%');
                node.style.setProperty('--gmrt-token-y', (token.y * 100) + '%');
                node.dataset.tokenRevision = String(token.revision || 1);
            });
        } catch (error) {
            say(error.message);
        }
    }

    document.querySelectorAll('.gmrt-token').forEach((token) => {
        token.setAttribute('aria-pressed', 'false');

        token.addEventListener('click', (event) => {
            event.stopPropagation();
            select(token);
        });

        token.addEventListener('keydown', (event) => {
            if (!['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(event.key)) {
                return;
            }

            event.preventDefault();

            const currentX = parseFloat(token.style.getPropertyValue('--gmrt-token-x')) / 100;
            const currentY = parseFloat(token.style.getPropertyValue('--gmrt-token-y')) / 100;
            const step = event.shiftKey ? 0.05 : 0.01;

            let x = currentX;
            let y = currentY;

            if (event.key === 'ArrowLeft') x -= step;
            if (event.key === 'ArrowRight') x += step;
            if (event.key === 'ArrowUp') y -= step;
            if (event.key === 'ArrowDown') y += step;

            select(token);
            moveSelected(
                Math.max(0, Math.min(1, x)),
                Math.max(0, Math.min(1, y))
            );
        });
    });

    board.addEventListener('click', (event) => {
        if (!selected) {
            return;
        }

        const point = coordinatesFromPointer(event);
        moveSelected(point.x, point.y);
    });


    document.querySelectorAll('[data-battle-deed]').forEach((button) => {
        button.addEventListener('click', async () => {
            const encounter = document.querySelector('[data-encounter-id]');

            if (!encounter) {
                return;
            }

            try {
                const deedKey = button.dataset.battleDeed || '';
                let data;

                if (deedKey === 'attack') {
                    const target = document.querySelector(
                        '[data-attack-target]'
                    );

                    if (!target || !target.value) {
                        say('Choose a target before attacking.');
                        return;
                    }

                    data = await request('gmrt_resolve_attack', {
                        encounter_id: encounter.dataset.encounterId || '',
                        target_token_id: target.value,
                        revision: encounter.dataset.encounterRevision || '1'
                    });
                } else {
                    data = await request('gmrt_perform_battle_deed', {
                        encounter_id: encounter.dataset.encounterId || '',
                        deed: deedKey,
                        revision: encounter.dataset.encounterRevision || '1'
                    });
                }

                if (data.encounter) {
                    encounter.dataset.encounterRevision =
                        String(data.encounter.revision || 1);
                }

                if (data.attack) {
                    const attack = data.attack;
                    const prefix = attack.result === 'critical-hit'
                        ? 'CRITICAL HIT!'
                        : attack.result === 'critical-miss'
                            ? 'Critical miss.'
                            : attack.hit ? 'Hit!' : 'Miss!';

                    say(
                        prefix
                        + ' d20 ' + attack.roll
                        + ' + ' + attack.modifier
                        + ' = ' + attack.total
                        + ' vs AC ' + attack.armor_class + '.'
                    );
                    return;
                }

                const deed = data.event
                    && data.event.payload
                    && data.event.payload.deed
                    ? data.event.payload.deed
                    : 'deed';

                say('Battle deed recorded: ' + deed + '.');
            } catch (error) {
                say(error.message);
                await refresh();
            }
        });
    });


    refreshTimer = window.setInterval(refresh, 5000);

    window.addEventListener('beforeunload', () => {
        if (refreshTimer) {
            window.clearInterval(refreshTimer);
        }
    });
}());
