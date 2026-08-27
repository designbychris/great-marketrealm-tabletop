(function () {
    'use strict';

    const root = document.querySelector('.gmrt-chamber');
    const board = document.querySelector('.gmrt-board__viewport');
    const status = document.querySelector('#gmrt-tabletop-status');

    if (!root || !window.gmrtTabletop) {
        return;
    }

    const tableId = root.dataset.tableId || '';
    let selected = null;
    let refreshTimer = null;
    let targetingPreview = null;

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

    const prepareTestTableButton = document.querySelector(
        '[data-prepare-test-table]'
    );

    if (prepareTestTableButton) {
        prepareTestTableButton.addEventListener('click', async () => {
            prepareTestTableButton.disabled = true;
            prepareTestTableButton.textContent = 'Preparing…';
            say('Sage is preparing the Training Grounds…');

            try {
                const data = await request('gmrt_prepare_test_table', {});
                const url = new URL(window.location.href);
                url.searchParams.set('table', data.table_id);
                window.location.assign(url.toString());
            } catch (error) {
                say(error.message || 'The test Table could not be prepared.');
                prepareTestTableButton.disabled = false;
                prepareTestTableButton.textContent = 'Prepare Test Table';
            }
        });
    }

    if (!board) {
        return;
    }

    const attackTarget = document.querySelector(
        '[data-attack-target]'
    );
    const rangeStatus = document.querySelector(
        '[data-target-range-status]'
    );
    const targetLine = document.querySelector(
        '[data-target-line]'
    );
    const deedsPanel = document.querySelector(
        '.gmrt-deeds[data-current-token]'
    );

    function attackButton() {
        return document.querySelector(
            '[data-battle-deed="attack"]'
        );
    }

    function clearTargeting() {
        targetingPreview = null;

        if (targetLine) {
            targetLine.classList.remove(
                'is-visible',
                'is-long-range',
                'is-out-of-range'
            );
        }

        if (rangeStatus) {
            rangeStatus.textContent = 'Choose target';
            rangeStatus.className = 'gmrt-target-range';
            delete rangeStatus.dataset.rollMode;
        }

        const button = attackButton();
        if (button) {
            button.disabled = false;
            button.removeAttribute('aria-disabled');
        }
    }

    function drawTargetLine(targetId, rangeState) {
        if (!targetLine || !deedsPanel || !targetId) {
            return;
        }

        const attackerId = deedsPanel.dataset.currentToken || '';
        const attacker = document.querySelector(
            '[data-token-id="' + CSS.escape(attackerId) + '"]'
        );
        const target = document.querySelector(
            '[data-token-id="' + CSS.escape(targetId) + '"]'
        );

        if (!attacker || !target) {
            return;
        }

        const boardRect = board.getBoundingClientRect();
        const attackerRect = attacker.getBoundingClientRect();
        const targetRect = target.getBoundingClientRect();

        targetLine.setAttribute(
            'x1',
            String(
                attackerRect.left
                + attackerRect.width / 2
                - boardRect.left
            )
        );
        targetLine.setAttribute(
            'y1',
            String(
                attackerRect.top
                + attackerRect.height / 2
                - boardRect.top
            )
        );
        targetLine.setAttribute(
            'x2',
            String(
                targetRect.left
                + targetRect.width / 2
                - boardRect.left
            )
        );
        targetLine.setAttribute(
            'y2',
            String(
                targetRect.top
                + targetRect.height / 2
                - boardRect.top
            )
        );

        targetLine.classList.add('is-visible');
        targetLine.classList.toggle(
            'is-long-range',
            rangeState === 'long-range'
        );
        targetLine.classList.toggle(
            'is-out-of-range',
            rangeState === 'out-of-range'
        );
    }

    async function updateTargeting() {
        if (
            !attackTarget
            || !attackTarget.value
        ) {
            clearTargeting();
            return;
        }

        const encounter = document.querySelector(
            '[data-encounter-id]'
        );

        if (!encounter) {
            clearTargeting();
            return;
        }

        try {
            const data = await request(
                'gmrt_measure_target',
                {
                    encounter_id:
                        encounter.dataset.encounterId || '',
                    target_token_id: attackTarget.value
                }
            );

            targetingPreview = data;
            const range = data.range || {};
            const distance = data.distance || {};
            const rollMode = data.roll_mode || 'normal';

            let label =
                String(distance.feet || 0)
                + ' ft · ';

            if (range.range_status === 'out-of-range') {
                label += 'OUT OF RANGE';
            } else if (range.range_status === 'long-range') {
                label += 'LONG RANGE';
            } else {
                label += 'IN RANGE';
            }

            if (rollMode !== 'normal') {
                label += ' · ' + rollMode.toUpperCase();
            }

            if (rangeStatus) {
                rangeStatus.textContent = label;
                rangeStatus.className =
                    'gmrt-target-range is-'
                    + String(range.range_status || 'unknown');
                rangeStatus.dataset.rollMode = rollMode;
            }

            drawTargetLine(
                attackTarget.value,
                range.range_status || ''
            );

            const button = attackButton();
            if (button) {
                const out = range.in_range === false;
                button.disabled = out;
                button.setAttribute(
                    'aria-disabled',
                    out ? 'true' : 'false'
                );
                button.title = out
                    ? 'Out of range'
                    : '';
            }
        } catch (error) {
            clearTargeting();
            say(error.message);
        }
    }

    if (attackTarget) {
        attackTarget.addEventListener(
            'change',
            updateTargeting
        );
    }

    window.addEventListener('resize', () => {
        if (
            targetingPreview
            && attackTarget
            && attackTarget.value
        ) {
            drawTargetLine(
                attackTarget.value,
                targetingPreview.range
                    ? targetingPreview.range.range_status
                    : ''
            );
        }
    });

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
            await updateTargeting();
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


    const endTurnButton = document.querySelector('[data-end-turn]');

    if (endTurnButton) {
        endTurnButton.addEventListener('click', async () => {
            const encounter = document.querySelector('[data-encounter-id]');

            if (!encounter) {
                say('No active Encounter.');
                return;
            }

            endTurnButton.disabled = true;
            endTurnButton.textContent = 'Passing…';
            say('Passing the turn…');

            try {
                const data = await request('gmrt_advance_encounter', {
                    encounter_id: encounter.dataset.encounterId || '',
                    revision: encounter.dataset.encounterRevision || '1'
                });

                if (data.encounter) {
                    encounter.dataset.encounterRevision =
                        String(data.encounter.revision || 1);
                }

                say('Turn passed.');
                window.location.reload();
            } catch (error) {
                say(error.message || 'The turn could not be passed.');
                endTurnButton.disabled = false;
                endTurnButton.textContent = 'End Turn ▶';
                await refresh();
            }
        });
    }

    const applyConditionButton = document.querySelector(
        '[data-apply-condition]'
    );
    const removeConditionButton = document.querySelector(
        '[data-remove-condition]'
    );

    async function changeCondition(action) {
        const encounter = document.querySelector('[data-encounter-id]');
        const target = document.querySelector('[data-condition-target]');
        const type = document.querySelector('[data-condition-type]');
        const duration = document.querySelector('[data-condition-duration]');

        if (!encounter || !target || !target.value || !type) {
            say('Choose a combatant and condition first.');
            return;
        }

        try {
            await request(action, {
                encounter_id: encounter.dataset.encounterId || '',
                token_id: target.value,
                condition: type.value,
                turns_remaining: duration ? duration.value : '0'
            });

            say(
                type.value.toUpperCase()
                + (
                    action === 'gmrt_apply_condition'
                        ? ' applied.'
                        : ' removed.'
                )
            );
            window.location.reload();
        } catch (error) {
            say(error.message || 'The affliction could not be changed.');
        }
    }

    if (applyConditionButton) {
        applyConditionButton.addEventListener('click', () => {
            changeCondition('gmrt_apply_condition');
        });
    }

    if (removeConditionButton) {
        removeConditionButton.addEventListener('click', () => {
            changeCondition('gmrt_remove_condition');
        });
    }


    const diceworks = document.querySelector(
        '[data-combat-diceworks]'
    );
    const diceworksMode = document.querySelector(
        '[data-diceworks-mode]'
    );
    const diceworksResult = document.querySelector(
        '[data-diceworks-result]'
    );
    const combatDice = Array.from(
        document.querySelectorAll('[data-combat-die]')
    );
    const lonelyConfetti = document.querySelector(
        '[data-lonely-confetti]'
    );

    function beginCombatRoll() {
        if (!diceworks) {
            return;
        }

        const expectedMode = rangeStatus
            && rangeStatus.dataset.rollMode
            ? rangeStatus.dataset.rollMode
            : 'normal';
        const count = expectedMode === 'normal'
            ? 1
            : 2;

        diceworks.hidden = false;
        diceworks.classList.add('is-rolling');
        diceworks.classList.remove(
            'is-critical-hit',
            'is-critical-miss'
        );

        if (diceworksMode) {
            diceworksMode.textContent =
                expectedMode === 'normal'
                    ? 'D20'
                    : expectedMode.toUpperCase();
        }

        if (diceworksResult) {
            diceworksResult.textContent =
                count === 2
                    ? 'Two certified d20s are rolling…'
                    : 'The certified d20 is rolling…';
        }

        combatDice.forEach((die, index) => {
            die.hidden = index >= count;
            die.classList.remove(
                'is-chosen',
                'is-rejected'
            );
            const value = die.querySelector(
                '[data-die-value]'
            );
            if (value) {
                value.textContent = '?';
            }
        });

        if (lonelyConfetti) {
            lonelyConfetti.hidden = true;
        }
    }

    function revealCombatRoll(attack) {
        if (!diceworks || !attack) {
            return;
        }

        const rolls = Array.isArray(attack.rolls)
            ? attack.rolls
            : [attack.roll];
        const chosenIndex = Math.max(
            0,
            rolls.indexOf(attack.roll)
        );

        diceworks.hidden = false;
        diceworks.classList.remove('is-rolling');
        diceworks.classList.toggle(
            'is-critical-hit',
            attack.result === 'critical-hit'
        );
        diceworks.classList.toggle(
            'is-critical-miss',
            attack.result === 'critical-miss'
        );

        if (diceworksMode) {
            diceworksMode.textContent =
                attack.roll_mode === 'normal'
                    ? 'D20'
                    : String(attack.roll_mode).toUpperCase();
        }

        combatDice.forEach((die, index) => {
            const visible = index < rolls.length;
            die.hidden = !visible;

            if (!visible) {
                return;
            }

            const value = die.querySelector(
                '[data-die-value]'
            );
            if (value) {
                value.textContent = String(rolls[index]);
            }

            die.classList.toggle(
                'is-chosen',
                index === chosenIndex
            );
            die.classList.toggle(
                'is-rejected',
                rolls.length > 1
                && index !== chosenIndex
            );
        });

        if (diceworksResult) {
            diceworksResult.textContent =
                attack.roll_mode === 'normal'
                    ? 'Result: ' + attack.roll
                    : 'Chosen d20: ' + attack.roll;
        }

        if (lonelyConfetti) {
            lonelyConfetti.hidden =
                attack.result !== 'critical-miss';
        }
    }

    function cancelCombatRoll() {
        if (diceworks) {
            diceworks.classList.remove('is-rolling');
        }
    }

    const deathSaveButton = document.querySelector(
        '[data-roll-death-save]'
    );

    if (deathSaveButton) {
        deathSaveButton.addEventListener('click', async () => {
            const encounter = document.querySelector('[data-encounter-id]');

            if (!encounter) {
                say('No active Encounter.');
                return;
            }

            deathSaveButton.disabled = true;

            try {
                const data = await request('gmrt_roll_death_save', {
                    encounter_id: encounter.dataset.encounterId || '',
                    revision: encounter.dataset.encounterRevision || '1'
                });

                const save = data.death_save;
                let message = 'Death save: d20 ' + save.roll + '. ';

                if (save.result === 'natural-twenty') {
                    message += 'Natural 20! Back on 1 HP.';
                } else if (save.result === 'natural-one') {
                    message += 'Natural 1 — two failures.';
                } else if (save.result === 'success') {
                    message += 'Success.';
                } else {
                    message += 'Failure.';
                }

                say(message);
                await refresh();
            } catch (error) {
                say(
                    error.message
                    || 'The death save could not be resolved.'
                );
            } finally {
                deathSaveButton.disabled = false;
            }
        });
    }

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

                    beginCombatRoll();

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
                    revealCombatRoll(attack);
                    const prefix = attack.result === 'critical-hit'
                        ? 'CRITICAL HIT!'
                        : attack.result === 'critical-miss'
                            ? 'Critical miss.'
                            : attack.hit ? 'Hit!' : 'Miss!';

                    let rollContext = '';

                    if (
                        attack.roll_mode
                        && attack.roll_mode !== 'normal'
                        && Array.isArray(attack.rolls)
                    ) {
                        rollContext =
                            ' '
                            + attack.roll_mode.toUpperCase()
                            + ' [' + attack.rolls.join(' / ') + ']';
                    }

                    let message =
                        prefix
                        + rollContext
                        + ' d20 ' + attack.roll
                        + ' + ' + attack.modifier
                        + ' = ' + attack.total
                        + ' vs AC ' + attack.armor_class + '.';

                    if (
                        data.damage
                        && data.damage_adjustment
                        && data.vitality
                    ) {
                        const adjusted = data.damage_adjustment;
                        const effects = adjusted.effects || [];
                        let defenseText = '';

                        if (effects.includes('immune')) {
                            defenseText = ' IMMUNE!';
                        } else {
                            if (effects.includes('resistant')) {
                                defenseText += ' RESIST!';
                            }
                            if (effects.includes('vulnerable')) {
                                defenseText += ' WEAK!';
                            }
                        }

                        message +=
                            ' '
                            + adjusted.damage_type.toUpperCase()
                            + defenseText
                            + ' Damage '
                            + adjusted.resolved_damage;

                        if (
                            adjusted.resolved_damage
                            !== adjusted.raw_damage
                        ) {
                            message +=
                                ' (rolled '
                                + adjusted.raw_damage
                                + ')';
                        }

                        message +=
                            '. HP '
                            + data.vitality.current_hp
                            + '/' + data.vitality.maximum_hp
                            + '.';
                    }

                    say(message);
                    await refresh();
                    return;
                }

                const deed = data.event
                    && data.event.payload
                    && data.event.payload.deed
                    ? data.event.payload.deed
                    : 'deed';

                say('Battle deed recorded: ' + deed + '.');
            } catch (error) {
                cancelCombatRoll();
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
