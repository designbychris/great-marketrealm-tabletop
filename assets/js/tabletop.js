(function () {
    'use strict';

    let activeRefreshTimer = null;

    async function replaceChamber(message) {
        const current = document.querySelector('.gmrt-chamber');
        const liveStatus = document.querySelector('#gmrt-tabletop-status');

        if (!current || !window.gmrtTabletop) {
            return;
        }

        if (message && liveStatus) {
            liveStatus.textContent = message;
        }

        const body = new URLSearchParams();
        body.set('action', 'gmrt_tabletop_fragment');
        body.set('nonce', gmrtTabletop.nonce);
        body.set('table_id', current.dataset.tableId || '');

        const response = await fetch(gmrtTabletop.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
            },
            body
        });
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.data && result.data.message
                    ? result.data.message
                    : 'The live Chamber could not be refreshed.'
            );
        }

        const html = result.data && typeof result.data.html === 'string'
            ? result.data.html
            : '';
        const parsed = new DOMParser().parseFromString(html, 'text/html');
        const incoming = parsed.querySelector('.gmrt-chamber');

        if (!incoming) {
            throw new Error('The refreshed Chamber markup was not found.');
        }

        if (activeRefreshTimer) {
            window.clearInterval(activeRefreshTimer);
            activeRefreshTimer = null;
        }

        current.replaceWith(incoming);
        bootTabletop();
    }

    function bootTabletop() {
    const root = document.querySelector('.gmrt-chamber');
    const board = document.querySelector('.gmrt-board__viewport');
    const status = document.querySelector('#gmrt-tabletop-status');

    if (!root || !window.gmrtTabletop) {
        return;
    }

    const tableId = root.dataset.tableId || '';
    let selected = null;
    const removeSelectedTokenButton = document.querySelector('[data-remove-selected-token]');
    let targetingPreview = null;
    let visionDrafting = false;

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

    async function replaceLifecycle(message) {
        const liveStatus = document.querySelector('#gmrt-tabletop-status');
        const currentLifecycle = document.querySelector('[data-live-lifecycle]');
        const currentLogSlot = document.querySelector('[data-live-battle-log-slot]');

        if (!currentLifecycle) {
            throw new Error('The live lifecycle region could not be found.');
        }

        if (message && liveStatus) {
            liveStatus.textContent = message;
        }

        const data = await request('gmrt_tabletop_fragment', {});
        const html = typeof data.html === 'string' ? data.html : '';
        const parsed = new DOMParser().parseFromString(html, 'text/html');
        const incomingLifecycle = parsed.querySelector('[data-live-lifecycle]');
        const incomingLogSlot = parsed.querySelector('[data-live-battle-log-slot]');

        if (!incomingLifecycle) {
            throw new Error('The refreshed lifecycle region was not found.');
        }

        currentLifecycle.replaceChildren(...incomingLifecycle.childNodes);

        if (currentLogSlot && incomingLogSlot) {
            currentLogSlot.replaceChildren(...incomingLogSlot.childNodes);
        }

        const incomingActive = parsed.querySelector('[data-token-id].is-active-turn');
        const incomingActiveId = incomingActive?.dataset.tokenId || '';
        document.querySelectorAll('[data-token-id]').forEach((node) => {
            node.classList.toggle(
                'is-active-turn',
                incomingActiveId !== '' && node.dataset.tokenId === incomingActiveId
            );
        });

        const root = document.querySelector('.gmrt-chamber');
        if (root && typeof data.sync_revision === 'string') {
            root.dataset.syncRevision = data.sync_revision;
        }

        bindEncounterLifecycleControls();
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

    const gatheringStatus = document.querySelector('[data-gathering-status]');
    const gatheringSay = (message) => {
        if (gatheringStatus) gatheringStatus.textContent = message;
    };

    function renderGathering(members) {
        const list = document.querySelector('[data-live-gathering-list]');
        if (!list || !Array.isArray(members)) return;

        list.replaceChildren();
        members.forEach((member) => {
            const item = document.createElement('li');
            const role = String(member.role || 'player');
            const status = String(member.status || 'unknown');
            item.className = 'gmrt-party__member gmrt-party__member--' + role + ' gmrt-party__member--' + status;
            item.style.setProperty('--gmrt-fellowship-colour', String(member.table_colour_hex || '#65b9ae'));

            const avatar = document.createElement('span');
            avatar.className = 'gmrt-party__avatar';
            avatar.setAttribute('aria-hidden', 'true');
            if (member.avatar_url) {
                const image = document.createElement('img');
                image.src = String(member.avatar_url);
                image.alt = '';
                avatar.appendChild(image);
            } else {
                avatar.textContent = String(member.display_name || '?').slice(0, 1);
            }
            item.appendChild(avatar);

            const roleBadge = document.createElement('span');
            roleBadge.className = 'gmrt-party__role';
            roleBadge.textContent = role === 'dungeon-master' ? 'DM' : 'Player';
            item.appendChild(roleBadge);

            const name = document.createElement('strong');
            name.textContent = String(member.display_name || ('User #' + String(member.user_id || '')));
            item.appendChild(name);

            if (root?.dataset.viewerRole === 'dungeon-master' && role === 'player' && status !== 'left') {
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'gmrt-party__remove';
                remove.dataset.removeTablePlayer = '';
                remove.dataset.userId = String(member.user_id || '');
                remove.textContent = 'Remove from Table';
                item.appendChild(remove);
            }

            const characterId = String(member.companion_character_id || '');
            const play = member.companion_character && member.companion_character.play
                ? member.companion_character.play
                : null;
            const hp = play && play.hit_points ? play.hit_points : null;
            if (hp) {
                const current = Math.max(0, Number(hp.current || 0));
                const maximum = Math.max(0, Number(hp.maximum || 0));
                const temporary = Math.max(0, Number(hp.temporary || 0));
                const percentage = maximum > 0 ? Math.min(100, Math.max(0, Math.round((current / maximum) * 100))) : 0;
                const vitality = document.createElement('div');
                vitality.className = 'gmrt-hp';
                if (characterId) vitality.dataset.partyCharacterHp = characterId;
                vitality.setAttribute('aria-label', 'Hit Points ' + current + ' of ' + maximum);
                vitality.innerHTML = '<div class="gmrt-hp__track"><span class="gmrt-hp__fill"></span></div><small>HP <span data-party-current-hp></span>/<span data-party-maximum-hp></span><span data-party-temp-wrap></span></small>';
                vitality.querySelector('.gmrt-hp__fill')?.style.setProperty('--gmrt-hp', percentage + '%');
                vitality.querySelector('[data-party-current-hp]').textContent = String(current);
                vitality.querySelector('[data-party-maximum-hp]').textContent = String(maximum);
                const tempWrap = vitality.querySelector('[data-party-temp-wrap]');
                if (tempWrap && temporary > 0) tempWrap.textContent = ' +' + temporary + ' temp';
                item.appendChild(vitality);
            }

            list.appendChild(item);
        });
    }

    document.querySelectorAll('[data-table-colour]').forEach((button) => {
        button.addEventListener('click', async () => {
            const colour = String(button.dataset.tableColour || '');
            if (!colour) return;
            try {
                const colourResult = await request('gmrt_choose_table_colour', { colour });
                document.querySelectorAll('[data-table-colour]').forEach((swatch) => swatch.setAttribute('aria-pressed', swatch === button ? 'true' : 'false'));
                gatheringSay(String(colourResult.message || 'Fellowship Ribbon chosen.'));
                await refresh();
            } catch (error) { gatheringSay(error.message); }
        });
    });

    const acceptInvitationButton = document.querySelector(
        '[data-accept-table-invitation]'
    );

    acceptInvitationButton?.addEventListener('click', async () => {
        acceptInvitationButton.disabled = true;
        gatheringSay('Taking your seat…');

        try {
            await request('gmrt_accept_table_invitation', {});
            gatheringSay('Seat accepted. Opening the Table…');
            window.location.reload();
        } catch (error) {
            gatheringSay(error.message || 'The invitation could not be accepted.');
            acceptInvitationButton.disabled = false;
        }
    });

    const gatheringInviteForm = document.querySelector(
        '[data-gathering-invite-form]'
    );

    gatheringInviteForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const input = gatheringInviteForm.querySelector('[name="player"]');
        const button = gatheringInviteForm.querySelector('button[type="submit"]');
        const player = input ? input.value.trim() : '';

        if (!player) return;
        if (button) button.disabled = true;
        gatheringSay('Sending the invitation…');

        try {
            const data = await request('gmrt_invite_table_player', { player });
            gatheringSay(data.message || 'Invitation sent.');
            if (input) input.value = '';
            await refresh();
        } catch (error) {
            gatheringSay(error.message || 'The player could not be invited.');
            if (button) button.disabled = false;
        }
    });

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-remove-table-player]');
        if (!button) return;
        const userId = button.dataset.userId || '';
        if (!userId) return;
        button.disabled = true;
        gatheringSay('Removing the player from the Table…');
        try {
            const data = await request('gmrt_remove_table_player', { user_id: userId });
            gatheringSay(data.message || 'Player removed.');
            await refresh();
        } catch (error) {
            gatheringSay(error.message || 'The player could not be removed.');
            button.disabled = false;
        }
    });

    const companionCharacterForm = document.querySelector('[data-companion-character-form]');
    companionCharacterForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const select = companionCharacterForm.querySelector('[name="character_id"]');
        const button = companionCharacterForm.querySelector('button[type="submit"]');
        const status = companionCharacterForm.querySelector('[data-companion-character-status]');
        const characterId = select ? select.value : '';
        if (!characterId) return;
        if (button) button.disabled = true;
        if (status) status.textContent = 'Opening the Companion Character Gate…';
        try {
            const data = await request('gmrt_select_companion_character', { character_id: characterId });
            if (status) status.textContent = data.message || 'Character ready.';
            await replaceChamber(data.message || 'Your adventurer has entered the Table.');
        } catch (error) {
            if (status) status.textContent = error.message || 'The Character could not enter the Table.';
            if (button) button.disabled = false;
        }
    });

    const satchel = document.querySelector('[data-adventurer-satchel]');
    const satchelToggle = document.querySelector('[data-satchel-toggle]');
    satchelToggle?.addEventListener('click', () => {
        const open = satchel?.dataset.open !== 'true';
        if (satchel) satchel.dataset.open = open ? 'true' : 'false';
        satchelToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    const measuresToggle = document.querySelector('[data-adventuring-measures-toggle]');
    const measuresForm = document.querySelector('[data-adventuring-measures-form]');
    measuresToggle?.addEventListener('click', () => {
        const open = measuresForm?.hidden !== false;
        if (measuresForm) measuresForm.hidden = !open;
        measuresToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    measuresForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submit = measuresForm.querySelector('button[type="submit"]');
        const status = measuresForm.querySelector('[data-adventuring-measures-status]');
        const current = measuresForm.querySelector('[name="current_hp"]');
        const temporary = measuresForm.querySelector('[name="temporary_hp"]');
        if (submit) submit.disabled = true;
        if (status) status.textContent = 'Updating…';
        try {
            const data = await request('gmrt_update_adventuring_measures', {
                current_hp: current ? current.value : '',
                temporary_hp: temporary ? temporary.value : ''
            });
            const hp = data.hit_points || {};
            const currentDisplay = document.querySelector('[data-current-hp]');
            const maximumDisplay = document.querySelector('[data-maximum-hp]');
            const temporaryDisplay = document.querySelector('[data-temporary-hp]');
            if (currentDisplay) currentDisplay.textContent = String(hp.current ?? '—');
            if (maximumDisplay) maximumDisplay.textContent = String(hp.maximum ?? '—');
            if (temporaryDisplay) temporaryDisplay.textContent = String(hp.temporary ?? 0);

            const selectedCharacterId = String(document.querySelector('[data-companion-character-form] [name="character_id"]')?.value || '');
            const partyHp = selectedCharacterId !== ''
                ? document.querySelector(`[data-party-character-hp="${CSS.escape(selectedCharacterId)}"]`)
                : null;
            const partyCurrent = partyHp?.querySelector('[data-party-current-hp]');
            const partyMaximum = partyHp?.querySelector('[data-party-maximum-hp]');
            const partyTemporary = partyHp?.querySelector('[data-party-temporary-hp]');
            if (partyCurrent) partyCurrent.textContent = String(hp.current ?? '—');
            if (partyMaximum) partyMaximum.textContent = String(hp.maximum ?? '—');
            if (partyTemporary) partyTemporary.textContent = String(hp.temporary ?? 0);

            if (current) current.value = String(hp.current ?? current.value);
            if (temporary) temporary.value = String(hp.temporary ?? temporary.value);
            if (status) status.textContent = data.message || 'Measures updated.';
            say(data.message || 'Adventuring Measures updated.');
        } catch (error) {
            if (status) status.textContent = error.message || 'The measures could not be updated.';
            say(error.message || 'The measures could not be updated.');
        } finally {
            if (submit) submit.disabled = false;
        }
    });

    const quickHandsResult = document.querySelector('[data-quick-hands-result]');
    document.querySelectorAll('[data-quick-roll]').forEach((button) => {
        button.addEventListener('click', async () => {
            const original = button.innerHTML;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            if (quickHandsResult) quickHandsResult.textContent = 'The dice tumble across the Table…';
            try {
                const data = await request('gmrt_quick_hands_roll', {
                    kind: button.dataset.rollKind || '',
                    key: button.dataset.rollKey || ''
                });
                const roll = data.roll || {};
                const flourish = roll.natural_twenty ? ' ✨ Natural 20!' : (roll.natural_one ? ' · Natural 1!' : '');
                if (quickHandsResult) quickHandsResult.textContent = (data.message || 'Roll complete.') + flourish;
                say(data.message || 'Quick Hands roll complete.');
                await refresh();
            } catch (error) {
                if (quickHandsResult) quickHandsResult.textContent = error.message || 'The roll could not be made.';
                say(error.message || 'The roll could not be made.');
            } finally {
                button.disabled = false;
                button.removeAttribute('aria-busy');
                button.innerHTML = original;
            }
        });
    });


    document.querySelectorAll('[data-weapon-roll]').forEach((button) => {
        button.addEventListener('click', async () => {
            const original = button.innerHTML;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            if (quickHandsResult) quickHandsResult.textContent = 'Weapon in hand… the dice are rolling.';
            try {
                const data = await request('gmrt_weapon_hands_roll', {
                    weapon_action: button.dataset.weaponAction || '',
                    attack_id: button.dataset.attackId || ''
                });
                const roll = data.roll || {};
                const flourish = roll.action === 'attack' && roll.natural_twenty
                    ? ' ⚔ Critical! Double the weapon dice for critical damage.'
                    : (roll.action === 'attack' && roll.natural_one ? ' · Natural 1!' : '');
                if (quickHandsResult) quickHandsResult.textContent = (data.message || 'Weapon roll complete.') + flourish;
                say(data.message || 'Weapons to Hand roll complete.');
                await refresh();
            } catch (error) {
                if (quickHandsResult) quickHandsResult.textContent = error.message || 'The weapon roll could not be made.';
                say(error.message || 'The weapon roll could not be made.');
            } finally {
                button.disabled = false;
                button.removeAttribute('aria-busy');
                button.innerHTML = original;
            }
        });
    });

    document.querySelectorAll('[data-spell-roll]').forEach((button) => {
        button.addEventListener('click', async () => {
            const original = button.innerHTML;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            if (quickHandsResult) quickHandsResult.textContent = 'Magic gathers… the dice are rolling.';
            try {
                const data = await request('gmrt_spell_pouch_roll', {
                    spell_action: button.dataset.spellAction || '',
                    spell_id: button.dataset.spellId || ''
                });
                const roll = data.roll || {};
                const flourish = roll.action === 'attack' && roll.natural_twenty
                    ? ' ✨ Spell attack critical!'
                    : (roll.action === 'attack' && roll.natural_one ? ' · Natural 1!' : '');
                if (quickHandsResult) quickHandsResult.textContent = (data.message || 'Spell roll complete.') + flourish;
                say(data.message || 'Spell Pouch roll complete.');
                await refresh();
            } catch (error) {
                if (quickHandsResult) quickHandsResult.textContent = error.message || 'The spell roll could not be made.';
                say(error.message || 'The spell roll could not be made.');
            } finally {
                button.disabled = false;
                button.removeAttribute('aria-busy');
                button.innerHTML = original;
            }
        });
    });

    if (!board) {
        return;
    }

    const arsenalAttack = document.querySelector(
        '[data-arsenal-attack]'
    );
    const lensStage = document.querySelector('[data-lens-stage]');
    const lensViewport = document.querySelector('.gmrt-board__viewport');
    const lensZoomOut = document.querySelector('[data-lens-zoom-out]');
    const lensZoomIn = document.querySelector('[data-lens-zoom-in]');
    const lensFit = document.querySelector('[data-lens-fit]');
    const lensReset = document.querySelector('[data-lens-reset]');
    const lensZoomLabel = document.querySelector('[data-lens-zoom]');

    const lens = {
        scale: 1, x: 0, y: 0,
        min: .25, max: 3, step: .1,
        dragging: false,
        panStarted: false,
        pointerId: null,
        pointerX: 0,
        pointerY: 0,
        startX: 0,
        startY: 0,
        originX: 0,
        originY: 0,
        threshold: 4
    };

    const clampLensScale = (scale) =>
        Math.max(lens.min, Math.min(lens.max, scale));

    const renderLens = () => {
        if (!lensViewport) return;
        lensViewport.style.transform =
            `translate(${lens.x}px, ${lens.y}px) scale(${lens.scale})`;
        if (lensZoomLabel) {
            const label = `${Math.round(lens.scale * 100)}%`;
            lensZoomLabel.value = label;
            lensZoomLabel.textContent = label;
        }
    };

    const zoomLens = (delta) => {
        if (!lensStage || !lensViewport) return;
        const oldScale = lens.scale;
        const nextScale = clampLensScale(oldScale + delta);
        if (nextScale === oldScale) return;

        const anchorX = lensStage.clientWidth / 2;
        const anchorY = lensStage.clientHeight / 2;
        const mapX = (anchorX - lens.x) / oldScale;
        const mapY = (anchorY - lens.y) / oldScale;
        lens.scale = nextScale;
        lens.x = anchorX - (mapX * nextScale);
        lens.y = anchorY - (mapY * nextScale);
        renderLens();
    };

    const fitLens = () => {
        if (!lensStage || !lensViewport) return;
        const mapWidth = lensViewport.offsetWidth;
        const mapHeight = lensViewport.offsetHeight;
        if (!mapWidth || !mapHeight) return;
        lens.scale = clampLensScale(Math.min(
            lensStage.clientWidth / mapWidth,
            lensStage.clientHeight / mapHeight
        ));
        lens.x = (lensStage.clientWidth - (mapWidth * lens.scale)) / 2;
        lens.y = (lensStage.clientHeight - (mapHeight * lens.scale)) / 2;
        renderLens();
    };

    const resetLens = () => {
        lens.scale = 1;
        lens.x = 0;
        lens.y = 0;
        renderLens();
    };

    lensZoomOut?.addEventListener('click', () => zoomLens(-lens.step));
    lensZoomIn?.addEventListener('click', () => zoomLens(lens.step));
    lensFit?.addEventListener('click', fitLens);
    lensReset?.addEventListener('click', resetLens);

    const isLensInteractiveTarget = (target) =>
        target instanceof Element
        && Boolean(target.closest(
            'button, input, select, textarea, a, [data-token-id]'
        ));

    lensStage?.addEventListener('dragstart', (event) => {
        event.preventDefault();
    });

    lensStage?.addEventListener('pointerdown', (event) => {
        if (
            event.button !== 0
            || visionDrafting
            || isLensInteractiveTarget(event.target)
        ) {
            return;
        }

        lens.dragging = true;
        lens.panStarted = false;
        lens.pointerId = event.pointerId;
        lens.pointerX = event.clientX;
        lens.pointerY = event.clientY;
        lens.startX = event.clientX;
        lens.startY = event.clientY;
        lens.originX = lens.x;
        lens.originY = lens.y;

        lensStage.setPointerCapture(event.pointerId);
    });

    lensStage?.addEventListener('pointermove', (event) => {
        if (!lens.dragging || event.pointerId !== lens.pointerId) return;

        const dx = event.clientX - lens.startX;
        const dy = event.clientY - lens.startY;

        if (
            !lens.panStarted
            && Math.hypot(dx, dy) < lens.threshold
        ) {
            return;
        }

        if (!lens.panStarted) {
            lens.panStarted = true;
            lensStage.classList.add('is-panning');
        }

        event.preventDefault();
        lens.x = lens.originX + dx;
        lens.y = lens.originY + dy;
        renderLens();
    });

    const stopLensPan = (event) => {
        if (
            !lens.dragging
            || (
                lens.pointerId !== null
                && event.pointerId !== lens.pointerId
            )
        ) {
            return;
        }

        lens.dragging = false;
        lens.panStarted = false;
        lensStage?.classList.remove('is-panning');

        if (
            lensStage
            && lens.pointerId !== null
            && lensStage.hasPointerCapture(lens.pointerId)
        ) {
            lensStage.releasePointerCapture(lens.pointerId);
        }

        lens.pointerId = null;
    };

    lensStage?.addEventListener('pointerup', stopLensPan);
    lensStage?.addEventListener('pointercancel', stopLensPan);
    lensStage?.addEventListener('lostpointercapture', (event) => {
        if (lens.dragging) stopLensPan(event);
    });

    const footstepLayer = document.querySelector('[data-footstep-layer]');
    let footstepProjection = [];
    if (footstepLayer) {
        try {
            footstepProjection = JSON.parse(footstepLayer.dataset.footsteps || '[]');
        } catch (error) {
            footstepProjection = [];
        }
    }

    const renderFootsteps = (steps = footstepProjection) => {
        if (!footstepLayer) return;
        footstepProjection = Array.isArray(steps) ? steps : [];
        footstepLayer.replaceChildren();

        footstepProjection.forEach((step) => {
            const mark = document.createElement('span');
            mark.className = 'gmrt-footstep' + (step.memory ? ' is-memory' : '');
            mark.style.setProperty('--gmrt-step-x', (Number(step.x || 0) * 100) + '%');
            mark.style.setProperty('--gmrt-step-y', (Number(step.y || 0) * 100) + '%');
            mark.style.setProperty('--gmrt-step-angle', String(Number(step.angle || 0) + 90) + 'deg');
            mark.style.setProperty('--gmrt-step-opacity', String(Number(step.opacity || .2)));
            mark.style.setProperty('--gmrt-step-colour', String(step.table_colour_hex || '#65b9ae'));
            mark.appendChild(document.createElement('i'));
            mark.appendChild(document.createElement('i'));
            footstepLayer.appendChild(mark);
        });
    };

    const lightLayer = document.querySelector('[data-light-layer]');
    const renderLightSources = (projection = fogProjection) => {
        if (!lightLayer) return;
        lightLayer.replaceChildren();
        (Array.isArray(projection?.light_sources) ? projection.light_sources : []).forEach((source) => {
            const glow = document.createElement('span');
            glow.className = 'gmrt-carried-light' + (source.source_kind === 'dropped' ? ' is-dropped' : '');
            if (source.source_kind === 'dropped') { glow.textContent = '🔥'; glow.setAttribute('aria-hidden', 'true'); }
            glow.style.setProperty('--gmrt-light-x', (Number(source.x || 0) * 100) + '%');
            glow.style.setProperty('--gmrt-light-y', (Number(source.y || 0) * 100) + '%');
            lightLayer.appendChild(glow);
        });
    };

    const fogLayer = document.querySelector('[data-fog-layer]');
    const fogEnabled = document.querySelector('[data-fog-enabled]');
    const fogPreview = document.querySelector('[data-fog-preview]');
    const fogClear = document.querySelector('[data-fog-clear]');
    const fogStatus = document.querySelector('[data-fog-status]');
    let fogProjection = {};

    if (fogLayer) {
        try {
            fogProjection = JSON.parse(fogLayer.dataset.fog || '{}');
        } catch (error) {
            fogProjection = {};
        }
    }

    const fogPreviewStorageKey = tableId
        ? `gmrt-fog-preview:${tableId}`
        : 'gmrt-fog-preview';

    if (fogPreview) {
        fogPreview.checked =
            window.sessionStorage.getItem(
                fogPreviewStorageKey
            ) === '1';
    }

    const renderFog = (projection = fogProjection) => {
        if (!fogLayer) return;

        fogProjection = projection || {};
        renderLightSources(fogProjection);
        fogLayer.replaceChildren();

        const enabled = Boolean(fogProjection.enabled);
        const dmBypass = Boolean(fogProjection.bypass);
        const preview = Boolean(fogPreview && fogPreview.checked);

        if (
            enabled
            && Number(fogProjection.reference_width || 0) < 1
            && fogStatus
        ) {
            fogStatus.textContent =
                'Save Grid once to anchor Fog of War to this battlemat.';
        }

        if (!enabled || (dmBypass && !preview)) {
            fogLayer.hidden = true;
            return;
        }

        fogLayer.hidden = false;

        const referenceWidth = Math.max(
            1,
            Number(
                fogProjection.reference_width
                || fogLayer.parentElement?.clientWidth
                || 1
            )
        );
        const displayWidth = Math.max(
            1,
            Number(
                fogLayer.parentElement?.clientWidth
                || referenceWidth
            )
        );
        const displayScale =
            displayWidth / referenceWidth;

        const size = Math.max(
            1,
            Number(fogProjection.grid_size || 1)
            * displayScale
        );
        const offsetX =
            Number(fogProjection.offset_x || 0)
            * displayScale;
        const offsetY =
            Number(fogProjection.offset_y || 0)
            * displayScale;

        const nativeWidth = Math.max(
            1,
            Number(fogProjection.width || 1)
        );
        const nativeHeight = Math.max(
            1,
            Number(fogProjection.height || 1)
        );
        const width = displayWidth;
        const height =
            nativeHeight
            * (displayWidth / nativeWidth);
        const explored = new Set(fogProjection.explored || []);
        const visible = new Set(fogProjection.visible || []);
        const visionOrigins = Array.isArray(fogProjection.vision_origins)
            ? fogProjection.vision_origins
            : [];
        const visionRadius = Math.max(
            0,
            Number(fogProjection.vision_radius || 0)
        );

        // The server remains authoritative for which tokens may be exposed.
        // Vision origins are the same CHARACTER sight sources used by the
        // server projector, supplied so the visual veil can anchor itself to
        // the actually rendered battlefield even when responsive scaling
        // differs slightly from the saved calibration width.
        if (!Boolean(fogProjection.has_blockers)) visionOrigins.forEach((origin) => {
            const x = Math.max(0, Math.min(1, Number(origin.x || 0)));
            const y = Math.max(0, Math.min(1, Number(origin.y || 0)));
            const centerColumn = Math.floor(
                ((x * width) - offsetX) / size
            );
            const centerRow = Math.floor(
                ((y * height) - offsetY) / size
            );

            for (
                let row = centerRow - visionRadius;
                row <= centerRow + visionRadius;
                row += 1
            ) {
                for (
                    let column = centerColumn - visionRadius;
                    column <= centerColumn + visionRadius;
                    column += 1
                ) {
                    if (
                        Math.max(
                            Math.abs(column - centerColumn),
                            Math.abs(row - centerRow)
                        ) <= visionRadius
                    ) {
                        visible.add(`${column}:${row}`);
                    }
                }
            }
        });

        const minColumn = Math.floor((0 - offsetX) / size);
        const maxColumn = Math.ceil((width - offsetX) / size);
        const minRow = Math.floor((0 - offsetY) / size);
        const maxRow = Math.ceil((height - offsetY) / size);

        const fragment = document.createDocumentFragment();

        for (let row = minRow; row < maxRow; row += 1) {
            for (let column = minColumn; column < maxColumn; column += 1) {
                const key = `${column}:${row}`;

                if (visible.has(key)) {
                    continue;
                }

                const cell = document.createElement('span');
                const remembered = explored.has(key);

                cell.className = remembered
                    ? 'gmrt-fog-cell is-memory'
                    : 'gmrt-fog-cell is-unexplored';

                const neighbours = [
                    `${column - 1}:${row}`,
                    `${column + 1}:${row}`,
                    `${column}:${row - 1}`,
                    `${column}:${row + 1}`
                ];

                if (
                    neighbours.some(
                        (neighbour) => visible.has(neighbour)
                    )
                ) {
                    cell.classList.add('is-vision-edge');
                }

                if (
                    !remembered
                    && neighbours.some(
                        (neighbour) => explored.has(neighbour)
                    )
                ) {
                    cell.classList.add('is-memory-edge');
                }

                cell.style.left = `${offsetX + (column * size)}px`;
                cell.style.top = `${offsetY + (row * size)}px`;
                cell.style.width = `${size + 1}px`;
                cell.style.height = `${size + 1}px`;
                fragment.append(cell);
            }
        }

        fogLayer.append(fragment);
    };

    renderFootsteps();
    renderFog();


    // Phase IV.27B — The First Lantern. The browser chooses only on/off;
    // range and visibility remain server-authoritative.
    const lanternButton = document.querySelector('[data-toggle-carried-light]');
    const lanternState = document.querySelector('[data-lantern-state]');
    const lanternStatus = document.querySelector('[data-lantern-status]');
    lanternButton?.addEventListener('click', async () => {
        lanternButton.disabled = true;
        try {
            const response = await request('gmrt_toggle_carried_light', { table_id: tableId });
            const data = response || {};
            const lit = Boolean(data.lit);
            if (lanternState) lanternState.textContent = lit ? 'Burning' : 'Doused';
            lanternButton.textContent = lit ? 'Douse Torch' : 'Light Torch';
            if (lanternStatus) lanternStatus.textContent = String(data.message || '');
            syncTorchButtons(lit);
            const fresh = await request('gmrt_tabletop_state', { table_id: tableId });
            if (fresh?.fog) renderFog(fresh.fog);
        } catch (error) {
            if (lanternStatus) lanternStatus.textContent = error?.message || 'The lantern could not be tended.';
        } finally { lanternButton.disabled = false; }
    });
    // Phase IV.27D — Fire Upon the Floor. Drop/pick-up intent contains no
    // coordinates: the server derives the adventurer position and nearest torch.
    const droppedLightButtons = Array.from(document.querySelectorAll('[data-dropped-light-action]'));
    const syncTorchButtons = (carried) => {
        droppedLightButtons.forEach((button) => {
            const action = button.dataset.droppedLightAction;
            button.hidden = action === 'drop' ? !carried : carried;
        });
    };
    droppedLightButtons.forEach((button) => button.addEventListener('click', async () => {
        const action = String(button.dataset.droppedLightAction || '');
        droppedLightButtons.forEach((item) => { item.disabled = true; });
        try {
            const data = await request('gmrt_tend_dropped_light', { table_id: tableId, light_action: action });
            const carried = Boolean(data?.carried);
            if (lanternState) lanternState.textContent = carried ? 'Burning' : 'Doused';
            if (lanternButton) lanternButton.textContent = carried ? 'Douse Torch' : 'Light Torch';
            if (lanternStatus) lanternStatus.textContent = String(data?.message || '');
            syncTorchButtons(carried);
            const fresh = await request('gmrt_tabletop_state', { table_id: tableId });
            if (fresh?.fog) renderFog(fresh.fog);
        } catch (error) {
            if (lanternStatus) lanternStatus.textContent = error?.message || 'The torch could not be tended.';
        } finally { droppedLightButtons.forEach((item) => { item.disabled = false; }); }
    }));

    fogPreview?.addEventListener('change', () => {
        window.sessionStorage.setItem(
            fogPreviewStorageKey,
            fogPreview.checked ? '1' : '0'
        );
        renderFog();
    });

    const configureFog = async (enabled, clear = false) => {
        if (fogStatus) {
            fogStatus.textContent = clear
                ? 'Resetting exploration…'
                : 'Changing the veil…';
        }

        try {
            await request('gmrt_configure_fog', {
                enabled: enabled ? '1' : '0',
                clear: clear ? '1' : '0'
            });

            const state = await request('gmrt_tabletop_state', {});
            fogProjection = state.fog || {};
            renderFog(fogProjection);

            if (fogStatus) {
                fogStatus.textContent = clear
                    ? 'Exploration reset.'
                    : enabled
                        ? 'Fog of War enabled.'
                        : 'Fog of War disabled.';
            }
        } catch (error) {
            if (fogStatus) {
                fogStatus.textContent =
                    error.message || 'Fog of War could not be changed.';
            }
        }
    };

    fogEnabled?.addEventListener('change', () => {
        configureFog(fogEnabled.checked, false);
    });

    fogClear?.addEventListener('click', () => {
        configureFog(Boolean(fogEnabled?.checked), true);
    });

    const visionLayer = document.querySelector('[data-vision-layer]');
    const visionStatus = document.querySelector('[data-vision-status]');
    const visionRoster = document.querySelector('[data-vision-roster]');
    const visionCancel = document.querySelector('[data-vision-cancel]');
    const visionUndo = document.querySelector('[data-vision-undo]');
    const visionTools = Array.from(document.querySelectorAll('[data-vision-tool]'));
    let visionBarriers = [];
    let visionTool = null;
    let visionStart = null;
    let selectedVisionBarrier = null;
    let visionPreview = null;

    if (visionLayer) {
        try {
            visionBarriers = JSON.parse(visionLayer.dataset.vision || '[]');
        } catch (error) {
            visionBarriers = [];
        }
    }

    const visionGrid = () => {
        const referenceWidth = Math.max(
            1,
            Number(fogProjection.reference_width || board.clientWidth || 1)
        );
        const displayWidth = Math.max(1, board.clientWidth || referenceWidth);
        const scale = displayWidth / referenceWidth;
        return {
            size: Math.max(1, Number(fogProjection.grid_size || 1) * scale),
            offsetX: Number(fogProjection.offset_x || 0) * scale,
            offsetY: Number(fogProjection.offset_y || 0) * scale
        };
    };

    const barrierPoint = (column, row) => {
        const grid = visionGrid();
        return {
            x: grid.offsetX + (Number(column) * grid.size),
            y: grid.offsetY + (Number(row) * grid.size)
        };
    };

    const renderVisionLayer = (barriers = visionBarriers) => {
        if (!visionLayer) return;
        visionBarriers = Array.isArray(barriers) ? barriers : [];
        visionLayer.replaceChildren();
        const fragment = document.createDocumentFragment();

        visionBarriers.forEach((barrier) => {
            const start = barrierPoint(barrier.x1, barrier.y1);
            const end = barrierPoint(barrier.x2, barrier.y2);
            const line = document.createElementNS(
                'http://www.w3.org/2000/svg',
                'line'
            );
            line.setAttribute('x1', String(start.x));
            line.setAttribute('y1', String(start.y));
            line.setAttribute('x2', String(end.x));
            line.setAttribute('y2', String(end.y));
            line.classList.add('gmrt-vision-barrier', `is-${barrier.type}`);
            line.dataset.visionBarrier = String(barrier.id);
            if (String(barrier.id) === String(selectedVisionBarrier)) {
                line.classList.add('is-selected');
            }
            if (barrier.type === 'door' && barrier.open) {
                line.classList.add('is-open');
            }
            fragment.append(line);
        });

        if (visionPreview) {
            const start = barrierPoint(visionPreview.x1, visionPreview.y1);
            const end = barrierPoint(visionPreview.x2, visionPreview.y2);
            const preview = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            preview.setAttribute('x1', String(start.x));
            preview.setAttribute('y1', String(start.y));
            preview.setAttribute('x2', String(end.x));
            preview.setAttribute('y2', String(end.y));
            preview.classList.add('gmrt-vision-barrier', 'is-preview', `is-${visionTool || 'wall'}`);
            fragment.append(preview);
        }

        visionLayer.append(fragment);

        if (visionRoster) {
            visionRoster.replaceChildren();
            visionBarriers.forEach((barrier, index) => {
                const item = document.createElement('span');
                item.className = 'gmrt-vision-roster__item';
                item.dataset.visionSelect = String(barrier.id);
                item.classList.toggle('is-selected', String(barrier.id) === String(selectedVisionBarrier));
                const label = document.createElement('span');
                label.textContent = barrier.type === 'door'
                    ? `Door ${index + 1} · ${barrier.open ? 'OPEN' : 'CLOSED'}`
                    : `Wall ${index + 1}`;
                item.append(label);

                if (barrier.type === 'door') {
                    const toggle = document.createElement('button');
                    toggle.type = 'button';
                    toggle.dataset.visionToggle = String(barrier.id);
                    toggle.textContent = barrier.open ? 'Close' : 'Open';
                    item.append(toggle);
                }

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.dataset.visionRemove = String(barrier.id);
                remove.textContent = 'Remove';
                item.append(remove);
                visionRoster.append(item);
            });
        }
        if (visionUndo) visionUndo.disabled = visionBarriers.length === 0;
    };

    const resetVisionDraft = (message = '') => {
        visionDrafting = false;
        visionTool = null;
        visionStart = null;
        visionPreview = null;
        visionTools.forEach((button) => button.classList.remove('is-active'));
        if (visionCancel) visionCancel.disabled = true;
        if (message && visionStatus) visionStatus.textContent = message;
    };

    visionTools.forEach((button) => {
        button.addEventListener('click', () => {
            visionDrafting = true;
            visionTool = button.dataset.visionTool || 'wall';
            visionStart = null;
            visionTools.forEach((candidate) => {
                candidate.classList.toggle('is-active', candidate === button);
            });
            if (visionCancel) visionCancel.disabled = false;
            if (visionStatus) {
                visionStatus.textContent = visionTool === 'wall'
                    ? 'Wall tracing active. Click a grid intersection to begin.'
                    : 'Door placement active. Click the first grid intersection; frame the doorway with walls for a sealed room.';
            }
        });
    });

    visionCancel?.addEventListener('click', () => {
        resetVisionDraft('Vision drawing cancelled.');
    });

    board.addEventListener('pointermove', (event) => {
        if (!visionDrafting || !visionTool || !visionStart) return;
        const rect = board.getBoundingClientRect();
        const localX = ((event.clientX - rect.left) / rect.width) * board.clientWidth;
        const localY = ((event.clientY - rect.top) / rect.height) * board.clientHeight;
        const grid = visionGrid();
        visionPreview = {
            x1: visionStart.x,
            y1: visionStart.y,
            x2: Math.round((localX - grid.offsetX) / grid.size),
            y2: Math.round((localY - grid.offsetY) / grid.size)
        };
        renderVisionLayer();
    });

    board.addEventListener('click', async (event) => {
        if (!visionDrafting || !visionTool) return;
        event.preventDefault();
        event.stopPropagation();

        const rect = board.getBoundingClientRect();
        const localX = ((event.clientX - rect.left) / rect.width) * board.clientWidth;
        const localY = ((event.clientY - rect.top) / rect.height) * board.clientHeight;
        const grid = visionGrid();
        const point = {
            x: Math.round((localX - grid.offsetX) / grid.size),
            y: Math.round((localY - grid.offsetY) / grid.size)
        };

        if (!visionStart) {
            visionStart = point;
            if (visionStatus) {
                visionStatus.textContent = visionTool === 'wall'
                    ? 'Anchor set. Click the next intersection; keep clicking to trace connected walls.'
                    : 'First door edge set. Click the second intersection.';
                renderVisionLayer();
            }
            return;
        }

        try {
            await request('gmrt_add_vision_barrier', {
                type: visionTool,
                x1: visionStart.x,
                y1: visionStart.y,
                x2: point.x,
                y2: point.y
            });
            const state = await request('gmrt_tabletop_state', {});
            renderVisionLayer(state.vision_layer || []);
            renderFog(state.fog || {});
            if (visionTool === 'wall') {
                visionStart = point;
                visionPreview = null;
                if (visionStatus) {
                    visionStatus.textContent = 'Wall added. Continue from this anchor, or Finish / Cancel.';
                }
                renderVisionLayer();
            } else {
                resetVisionDraft('Door placed CLOSED. Frame both sides with walls so sight cannot travel around it.');
            }
        } catch (error) {
            if (visionStatus) visionStatus.textContent = error.message;
            visionStart = null;
            visionPreview = null;
            renderVisionLayer();
        }
    }, true);

    visionUndo?.addEventListener('click', async () => {
        const last = visionBarriers[visionBarriers.length - 1];
        if (!last) return;
        try {
            await request('gmrt_remove_vision_barrier', { barrier_id: String(last.id) });
            const state = await request('gmrt_tabletop_state', {});
            selectedVisionBarrier = null;
            renderVisionLayer(state.vision_layer || []);
            renderFog(state.fog || {});
            if (visionStatus) visionStatus.textContent = 'Last cartography segment undone.';
        } catch (error) {
            if (visionStatus) visionStatus.textContent = error.message;
        }
    });

    visionRoster?.addEventListener('click', async (event) => {
        const button = event.target instanceof Element
            ? event.target.closest('button')
            : null;
        if (!button) {
            const item = event.target instanceof Element ? event.target.closest('[data-vision-select]') : null;
            if (item) {
                selectedVisionBarrier = item.dataset.visionSelect || null;
                renderVisionLayer();
                if (visionStatus) visionStatus.textContent = 'Segment selected. Use its controls to edit the vision layer.';
            }
            return;
        }
        try {
            if (button.dataset.visionToggle) {
                await request('gmrt_toggle_vision_door', {
                    barrier_id: button.dataset.visionToggle
                });
            } else if (button.dataset.visionRemove) {
                await request('gmrt_remove_vision_barrier', {
                    barrier_id: button.dataset.visionRemove
                });
            } else {
                return;
            }
            const state = await request('gmrt_tabletop_state', {});
            renderVisionLayer(state.vision_layer || []);
            renderFog(state.fog || {});
            if (visionStatus) visionStatus.textContent = 'Vision layer updated.';
        } catch (error) {
            if (visionStatus) visionStatus.textContent = error.message;
        }
    });

    window.addEventListener('resize', () => renderVisionLayer());
    renderVisionLayer();

    const gridViewport = document.querySelector('.gmrt-board__viewport');
    const gridSize = document.querySelector('[data-grid-size]');
    const gridOffsetX = document.querySelector('[data-grid-offset-x]');
    const gridOffsetY = document.querySelector('[data-grid-offset-y]');
    const gridOpacity = document.querySelector('[data-grid-opacity]');
    const gridVisible = document.querySelector('[data-grid-visible]');
    const saveGrid = document.querySelector('[data-save-grid]');
    const resetGrid = document.querySelector('[data-reset-grid]');

    const originalGrid = gridSize ? {
        size: gridSize.value,
        x: gridOffsetX.value,
        y: gridOffsetY.value,
        opacity: gridOpacity.value,
        visible: gridVisible.checked
    } : null;

    const cartographerStatus = document.querySelector(
        '[data-cartographer-status]'
    );

    const previewGrid = () => {
        if (!gridViewport || !gridSize) return;
        gridViewport.style.setProperty('--gmrt-grid-size', `${Math.max(1, Number(gridSize.value || 1))}px`);
        gridViewport.style.setProperty('--gmrt-grid-offset-x', `${Number(gridOffsetX.value || 0)}px`);
        gridViewport.style.setProperty('--gmrt-grid-offset-y', `${Number(gridOffsetY.value || 0)}px`);
        gridViewport.style.setProperty('--gmrt-grid-opacity', String(Math.max(0, Math.min(100, Number(gridOpacity.value || 0))) / 100));
        gridViewport.style.setProperty('--gmrt-grid-display', gridVisible.checked ? 'block' : 'none');
    };

    [gridSize, gridOffsetX, gridOffsetY, gridOpacity, gridVisible]
        .filter(Boolean)
        .forEach((control) => control.addEventListener('input', previewGrid));

    document.querySelectorAll('[data-grid-nudge]').forEach((button) => {
        button.addEventListener('click', () => {
            const parts = String(button.dataset.gridNudge || '0,0').split(',').map(Number);
            gridOffsetX.value = String(Number(gridOffsetX.value || 0) + parts[0]);
            gridOffsetY.value = String(Number(gridOffsetY.value || 0) + parts[1]);
            previewGrid();
        });
    });

    if (resetGrid && originalGrid) {
        resetGrid.addEventListener('click', () => {
            gridSize.value = originalGrid.size;
            gridOffsetX.value = originalGrid.x;
            gridOffsetY.value = originalGrid.y;
            gridOpacity.value = originalGrid.opacity;
            gridVisible.checked = originalGrid.visible;
            previewGrid();
        });
    }

    if (saveGrid) {
        saveGrid.addEventListener('click', async () => {
            saveGrid.disabled = true;
            const previousLabel = saveGrid.textContent;
            saveGrid.textContent = 'Saving…';

            if (cartographerStatus) {
                cartographerStatus.textContent =
                    'Saving grid calibration…';
            }

            try {
                const data = await request('gmrt_calibrate_grid', {
                    grid_size: gridSize.value,
                    grid_offset_x: gridOffsetX.value,
                    grid_offset_y: gridOffsetY.value,
                    grid_opacity: gridOpacity.value,
                    grid_visible: gridVisible.checked ? '1' : '0',
                    grid_reference_width: String(
                        Math.max(
                            1,
                            Math.round(
                                gridViewport?.clientWidth
                                || 1
                            )
                        )
                    )
                });

                const saved = data.grid || {};

                gridSize.value = String(saved.size ?? gridSize.value);
                gridOffsetX.value = String(saved.offset_x ?? gridOffsetX.value);
                gridOffsetY.value = String(saved.offset_y ?? gridOffsetY.value);
                gridOpacity.value = String(saved.opacity ?? gridOpacity.value);
                gridVisible.checked = Boolean(saved.visible);

                if (
                    gridViewport
                    && saved.reference_width
                ) {
                    gridViewport.dataset.gridReferenceWidth =
                        String(saved.reference_width);
                }

                if (originalGrid) {
                    originalGrid.size = gridSize.value;
                    originalGrid.x = gridOffsetX.value;
                    originalGrid.y = gridOffsetY.value;
                    originalGrid.opacity = gridOpacity.value;
                    originalGrid.visible = gridVisible.checked;
                }

                previewGrid();

                const confirmation =
                    `Grid saved · ${gridSize.value}px · `
                    + `X ${gridOffsetX.value} · Y ${gridOffsetY.value}`;

                if (cartographerStatus) {
                    cartographerStatus.textContent = confirmation;
                }
                say(confirmation);
            } catch (error) {
                const message =
                    error.message
                    || 'Grid calibration could not be saved.';

                if (cartographerStatus) {
                    cartographerStatus.textContent = message;
                }
                say(message);
            } finally {
                saveGrid.disabled = false;
                saveGrid.textContent = previousLabel;
            }
        });
    }

    const chooseBattlemap = document.querySelector(
        '[data-choose-battlemap]'
    );
    if (chooseBattlemap) {
        chooseBattlemap.addEventListener('click', () => {
            if (
                !window.wp
                || !window.wp.media
            ) {
                say('The WordPress Media Library is unavailable.');
                return;
            }

            const frame = window.wp.media({
                title: 'Choose a Battlemap',
                button: {
                    text: 'Use this Battlemap'
                },
                library: {
                    type: 'image'
                },
                multiple: false
            });

            frame.on('select', async () => {
                const selected = frame.state()
                    .get('selection')
                    .first();

                if (!selected) {
                    return;
                }

                const attachment = selected.toJSON();

                try {
                    if (cartographerStatus) {
                        cartographerStatus.textContent =
                            'The Cartographer is preparing the new battlemap…';
                    }

                    const data = await request(
                        'gmrt_replace_battlemap',
                        {
                            attachment_id: attachment.id
                        }
                    );

                    const battlemap = data.battlemap || {};
                    const map = document.querySelector(
                        '[data-battlemap-image]'
                    );

                    if (map && battlemap.url) {
                        map.src = String(battlemap.url);
                        map.width = Number(battlemap.width || map.width);
                        map.height = Number(battlemap.height || map.height);
                    } else {
                        window.location.reload();
                        return;
                    }

                    if (cartographerStatus) {
                        cartographerStatus.textContent =
                            'Battlemap changed. Tokens and grid remain in place.';
                    }

                    say('Battlemap changed.');
                } catch (error) {
                    if (cartographerStatus) {
                        cartographerStatus.textContent =
                            error.message || 'The battlemap could not be changed.';
                    }
                    say(
                        error.message
                        || 'The battlemap could not be changed.'
                    );
                }
            });

            frame.open();
        });
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
                    target_token_id: attackTarget.value,
                    attack_id: arsenalAttack ? arsenalAttack.value : ''
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

    if (arsenalAttack) {
        arsenalAttack.addEventListener(
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

        if (removeSelectedTokenButton) {
            const viewerId = String(root?.dataset.viewerUserId || '');
            const isDungeonMaster = root?.dataset.viewerRole === 'dungeon-master';
            const ownsCharacter = selected
                && selected.dataset.tokenType === 'character'
                && selected.dataset.tokenController === viewerId;
            removeSelectedTokenButton.hidden = !(selected && (isDungeonMaster || ownsCharacter));
        }
    }

    removeSelectedTokenButton?.addEventListener('click', async () => {
        if (!selected) return;
        const tokenId = selected.dataset.tokenId || '';
        const label = selected.title || 'this token';
        if (!tokenId || !window.confirm('Remove ' + label + ' from this Chamber?')) return;

        removeSelectedTokenButton.disabled = true;
        try {
            const data = await request('gmrt_remove_chamber_token', { token_id: tokenId });
            selected.remove();
            selected = null;
            removeSelectedTokenButton.hidden = true;
            say(data.message || 'Token removed from the Chamber.');
            await refresh();
        } catch (error) {
            say(error.message || 'The token could not be removed.');
        } finally {
            removeSelectedTokenButton.disabled = false;
        }
    });

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
            await refresh();
        } catch (error) {
            say(error.message);
            await refresh();
        }
    }

    const battleLog = document.querySelector(
        '[data-battle-log]'
    );
    const battleLogEmpty = document.querySelector(
        '[data-battle-log-empty]'
    );

    function renderChronicle(entries, battleMode) {
        if (!battleLog) {
            return;
        }

        const chronicle = document.querySelector('[data-table-chronicle]');
        const eyebrow = document.querySelector('[data-chronicle-eyebrow]');
        const title = document.querySelector('[data-chronicle-title]');
        if (chronicle) chronicle.dataset.chronicleMode = battleMode ? 'battle' : 'chamber';
        if (eyebrow) eyebrow.textContent = battleMode ? 'Battle Chronicle' : 'Chamber Chronicle';
        if (title) title.textContent = battleMode ? 'Deeds at the Table' : 'Tales from the Chamber';

        battleLog.replaceChildren();

        const safeEntries = Array.isArray(entries)
            ? entries
            : [];

        safeEntries.forEach((entry) => {
            const item = document.createElement('li');
            item.dataset.battleLogEntry = '';
            item.dataset.chronicleLogEntry = '';
            const chronicleColour = entry.table_colour && entry.table_colour.hex ? entry.table_colour.hex : '#8f8779';
            item.style.setProperty('--gmrt-fellowship-colour', String(chronicleColour));

            const round = document.createElement('small');
            round.textContent = battleMode
                ? 'Round ' + String(entry.round || 0)
                : 'At the Table';

            const summary = document.createElement('span');
            summary.textContent = String(
                entry.summary || ''
            );

            item.append(round, summary);
            battleLog.append(item);
        });

        if (battleLogEmpty) {
            battleLogEmpty.hidden =
                safeEntries.length > 0;
        }
    }

    function updateCombatantState(node, state) {
        const states = [
            'healthy',
            'wounded',
            'downed',
            'defeated',
            'deceased'
        ];

        states.forEach((value) => {
            node.classList.toggle(
                'is-state-' + value,
                value === state
            );
        });

        node.dataset.combatantState = state;

        const badge = node.querySelector(
            '[data-token-state-badge]'
        );

        if (!badge) {
            return;
        }

        const label = state === 'downed'
            ? 'DOWN'
            : state === 'defeated'
                ? 'KO'
                : state === 'deceased'
                    ? 'DEAD'
                    : '';

        badge.textContent = label;
        badge.hidden = label === '';
    }

    async function refresh() {
        if (!tableId) {
            return;
        }

        try {
            const state = await request('gmrt_tabletop_state', {});
            const tokens = Array.isArray(state.tokens) ? state.tokens : [];
            const currentEncounter = document.querySelector('[data-encounter-id]');
            const incomingEncounter = state.encounter || null;
            const currentEncounterId = currentEncounter?.dataset.encounterId || '';
            const incomingEncounterId = incomingEncounter?.id || '';
            const currentEncounterRevision = currentEncounter?.dataset.encounterRevision || '';
            const incomingEncounterRevision = incomingEncounter
                ? String(incomingEncounter.revision || 1)
                : '';

            const encounterLifecycleChanged =
                currentEncounterId !== incomingEncounterId;
            const encounterRevisionChanged =
                currentEncounterRevision !== incomingEncounterRevision;

            if (encounterLifecycleChanged) {
                await replaceLifecycle(
                    incomingEncounter
                        ? 'Battle has begun — the Table takes its places.'
                        : 'Peace returns — exploration resumes.'
                );
                return;
            }

            if (encounterRevisionChanged && currentEncounter && incomingEncounter) {
                currentEncounter.dataset.encounterRevision = incomingEncounterRevision;

                const liveRound = currentEncounter.querySelector('[data-live-round]');
                if (liveRound) {
                    liveRound.textContent = 'Round ' + String(incomingEncounter.round || 0);
                }

                const currentTokenId = String(incomingEncounter.current_token_id || '');
                const activeToken = tokens.find((token) =>
                    String(token.id || '') === currentTokenId
                );
                const liveCombatant = currentEncounter.querySelector(
                    '[data-live-current-combatant]'
                );
                if (liveCombatant) {
                    liveCombatant.textContent = activeToken
                        ? String(activeToken.label || 'Unknown combatant')
                        : 'Unknown combatant';
                }

                document.querySelectorAll('[data-token-id]').forEach((node) => {
                    node.classList.toggle(
                        'is-active-turn',
                        node.dataset.tokenId === currentTokenId
                    );
                });

                const deeds = document.querySelector('.gmrt-deeds[data-current-token]');
                if (deeds) {
                    deeds.dataset.currentToken = currentTokenId;
                }

                say('The Table stirred — the turn has changed.');
            }

            if (state.sync_revision) {
                root.dataset.syncRevision = String(state.sync_revision);
            }

            renderGathering(state.members);
            renderChronicle(state.encounter ? state.battle_log : state.chamber_log, Boolean(state.encounter));
            renderFootsteps(state.footsteps || []);
            renderFog(state.fog || {});
            renderVisionLayer(state.vision_layer || []);

            const combatantStates =
                state.combatant_states || {};
            const tokenLayer = document.querySelector('.gmrt-board__tokens');
            const incomingTokenIds = new Set(
                tokens.map((token) => String(token.id || ''))
            );

            document.querySelectorAll('.gmrt-board__tokens [data-token-id]').forEach((node) => {
                if (!incomingTokenIds.has(String(node.dataset.tokenId || ''))) {
                    node.remove();
                }
            });

            tokens.forEach((token) => {
                let node = document.querySelector(
                    '[data-token-id="' + CSS.escape(String(token.id)) + '"]'
                );

                if (!node && tokenLayer) {
                    node = document.createElement('div');
                    const label = String(token.label || 'Token');
                    const type = String(token.type || 'character')
                        .replace(/[^a-z0-9_-]/gi, '');
                    node.className = 'gmrt-token gmrt-token--' + type;
                    node.dataset.tokenId = String(token.id || '');
                    node.dataset.tokenController = String(token.controller_user_id || '');
                    node.dataset.tokenType = String(token.type || '');
                    node.tabIndex = 0;
                    node.setAttribute('role', 'button');
                    node.setAttribute('aria-label', 'Select token: ' + label);
                    node.title = label;

                    const initial = document.createElement('span');
                    initial.className = 'gmrt-token__face';
                    initial.setAttribute('aria-hidden', 'true');
                    const recipe = token.companion_character && token.companion_character.token
                        ? token.companion_character.token
                        : null;
                    if (recipe && recipe.image_url) {
                        const image = document.createElement('img');
                        image.src = String(recipe.image_url);
                        image.alt = '';
                        image.style.setProperty('--gmrt-token-focus-x', String(recipe.focus_x || 50) + '%');
                        image.style.setProperty('--gmrt-token-focus-y', String(recipe.focus_y || 50) + '%');
                        image.style.setProperty('--gmrt-token-zoom', String(recipe.zoom || 100) + '%');
                        initial.appendChild(image);
                    } else {
                        initial.textContent = label.slice(0, 1).toUpperCase();
                    }
                    node.appendChild(initial);

                    const badge = document.createElement('span');
                    badge.className = 'gmrt-token__state-badge';
                    badge.dataset.tokenStateBadge = '';
                    badge.setAttribute('aria-hidden', 'true');
                    badge.hidden = true;
                    node.appendChild(badge);

                    tokenLayer.appendChild(node);
                }

                if (!node) {
                    return;
                }

                node.style.setProperty('--gmrt-token-x', (token.x * 100) + '%');
                node.style.setProperty('--gmrt-token-y', (token.y * 100) + '%');
                node.style.setProperty(
                    '--gmrt-token-width',
                    String(Math.max(1, Number(token.width_units || 1)))
                );
                node.style.setProperty(
                    '--gmrt-token-height',
                    String(Math.max(1, Number(token.height_units || 1)))
                );
                node.dataset.tokenRevision = String(token.revision || 1);
                node.style.setProperty('--gmrt-fellowship-colour', String(token.table_colour_hex || '#d8ad4f'));

                const combatantState =
                    combatantStates[String(token.id)]
                    || 'healthy';

                updateCombatantState(
                    node,
                    combatantState
                );
            });
        } catch (error) {
            say(error.message);
        }
    }

    document.querySelectorAll('.gmrt-token').forEach((token) => {
        token.setAttribute('aria-pressed', 'false');

        const tokenDrag = {
            active: false,
            moved: false,
            pointerId: null,
            startX: 0,
            startY: 0,
            threshold: 3
        };

        token.addEventListener('pointerdown', (event) => {
            if (event.button !== 0) return;

            event.stopPropagation();
            select(token);
            tokenDrag.active = true;
            tokenDrag.moved = false;
            tokenDrag.pointerId = event.pointerId;
            tokenDrag.startX = event.clientX;
            tokenDrag.startY = event.clientY;
            token.setPointerCapture(event.pointerId);
        });

        token.addEventListener('pointermove', (event) => {
            if (
                !tokenDrag.active
                || event.pointerId !== tokenDrag.pointerId
            ) {
                return;
            }

            const dx = event.clientX - tokenDrag.startX;
            const dy = event.clientY - tokenDrag.startY;

            if (
                !tokenDrag.moved
                && Math.hypot(dx, dy) < tokenDrag.threshold
            ) {
                return;
            }

            tokenDrag.moved = true;
            token.classList.add('is-dragging');
            event.preventDefault();
            event.stopPropagation();

            const point = coordinatesFromPointer(event);
            token.style.setProperty(
                '--gmrt-token-x',
                (point.x * 100) + '%'
            );
            token.style.setProperty(
                '--gmrt-token-y',
                (point.y * 100) + '%'
            );
        });

        const finishTokenDrag = (event) => {
            if (
                !tokenDrag.active
                || event.pointerId !== tokenDrag.pointerId
            ) {
                return;
            }

            const moved = tokenDrag.moved;
            tokenDrag.active = false;
            tokenDrag.moved = false;
            token.classList.remove('is-dragging');

            if (
                tokenDrag.pointerId !== null
                && token.hasPointerCapture(tokenDrag.pointerId)
            ) {
                token.releasePointerCapture(tokenDrag.pointerId);
            }

            tokenDrag.pointerId = null;

            if (moved) {
                event.preventDefault();
                event.stopPropagation();
                const point = coordinatesFromPointer(event);
                moveSelected(point.x, point.y);
            }
        };

        token.addEventListener('pointerup', finishTokenDrag);
        token.addEventListener('pointercancel', finishTokenDrag);

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


    function bindEncounterLifecycleControls() {
        const startEncounterButton = document.querySelector(
            '[data-start-encounter]'
        );

        if (startEncounterButton && !startEncounterButton.dataset.liveBound) {
            startEncounterButton.dataset.liveBound = '1';
            startEncounterButton.addEventListener('click', async () => {
                const name = document.querySelector('[data-encounter-name]');
                const selectedCombatants = Array.from(
                    document.querySelectorAll('[data-encounter-combatant]:checked')
                );
                const combatants = selectedCombatants.map((checkbox) => {
                    const tokenId = String(checkbox.value || '');
                    const initiative = document.querySelector(
                        '[data-encounter-initiative="'
                        + CSS.escape(tokenId)
                        + '"]'
                    );

                    return {
                        token_id: tokenId,
                        initiative: initiative ? parseInt(initiative.value || '0', 10) : 0,
                        initiative_modifier: 0
                    };
                });

                if (combatants.length === 0) {
                    say('Choose at least one combatant before beginning battle.');
                    return;
                }

                startEncounterButton.disabled = true;
                say('Calling the Table to battle…');

                try {
                    await request('gmrt_begin_encounter', {
                        name: name ? name.value : 'A Sudden Encounter',
                        combatants: JSON.stringify(combatants)
                    });
                    await replaceChamber('Battle begins.');
                } catch (error) {
                    startEncounterButton.disabled = false;
                    say(error.message || 'The Encounter could not begin.');
                }
            });
        }

        const endEncounterButton = document.querySelector('[data-end-encounter]');

        if (endEncounterButton && !endEncounterButton.dataset.liveBound) {
            endEncounterButton.dataset.liveBound = '1';
            endEncounterButton.addEventListener('click', async () => {
                const encounter = document.querySelector('[data-encounter-id]');
                if (!encounter) {
                    say('No current Encounter.');
                    return;
                }

                endEncounterButton.disabled = true;
                say('Ending the Encounter…');

                try {
                    await request('gmrt_end_encounter', {
                        encounter_id: encounter.dataset.encounterId || '',
                        revision: encounter.dataset.encounterRevision || '1'
                    });
                    await replaceChamber('Peace returns to the path.');
                } catch (error) {
                    endEncounterButton.disabled = false;
                    say(error.message || 'The Encounter could not end.');
                }
            });
        }
    }

    bindEncounterLifecycleControls();

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
                await request('gmrt_advance_encounter', {
                    encounter_id: encounter.dataset.encounterId || '',
                    revision: encounter.dataset.encounterRevision || '1'
                });

                // Keep the DOM revision stale until refresh() receives the
                // authoritative state. That lets the same live-state patch
                // used by remote Players also update the Keeper's own round,
                // current combatant, active token and Chronicle in place.
                say('Turn passed.');
                await refresh();
                endTurnButton.disabled = false;
                endTurnButton.textContent = 'End Turn ▶';
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
    const diceworksOutcome = document.querySelector(
        '[data-diceworks-outcome]'
    );
    const diceworksOutcomeTitle = document.querySelector(
        '[data-diceworks-outcome-title]'
    );
    const diceworksOutcomeDetail = document.querySelector(
        '[data-diceworks-outcome-detail]'
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

        if (diceworksOutcome) {
            diceworksOutcome.hidden = true;
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

    function renderImmediateCombatOutcome(data) {
        if (
            !data
            || !data.attack
            || !diceworksOutcome
        ) {
            return;
        }

        const attack = data.attack;
        const title = attack.result === 'critical-hit'
            ? 'CRITICAL HIT!'
            : attack.result === 'critical-miss'
                ? 'CRITICAL MISS'
                : attack.hit
                    ? 'HIT!'
                    : 'MISS!';

        const selectedAttack = data.selected_attack || null;

        let detail =
            (selectedAttack ? String(selectedAttack.name) + ' · ' : '')
            + String(attack.roll)
            + ' + ' + String(attack.modifier)
            + ' = ' + String(attack.total)
            + ' vs AC '
            + String(attack.armor_class);

        if (
            data.targeting
            && data.targeting.distance_feet !== undefined
        ) {
            detail +=
                ' · '
                + String(data.targeting.distance_feet)
                + ' ft';
        }

        if (
            data.damage_adjustment
            && data.vitality
        ) {
            const adjusted = data.damage_adjustment;
            const effects = Array.isArray(adjusted.effects)
                ? adjusted.effects
                : [];

            let effect = '';

            if (effects.includes('immune')) {
                effect = ' · IMMUNE!';
            } else if (effects.includes('vulnerable')) {
                effect = ' · WEAK!';
            } else if (effects.includes('resistant')) {
                effect = ' · RESIST!';
            }

            detail +=
                ' · '
                + String(adjusted.resolved_damage)
                + ' '
                + String(adjusted.damage_type).toUpperCase()
                + ' DAMAGE'
                + effect
                + ' · HP '
                + String(data.vitality.current_hp)
                + '/'
                + String(data.vitality.maximum_hp);
        }

        if (diceworksOutcomeTitle) {
            diceworksOutcomeTitle.textContent = title;
        }

        if (diceworksOutcomeDetail) {
            diceworksOutcomeDetail.textContent = detail;
        }

        diceworksOutcome.hidden = false;
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

    const syncDeathSaveHud = (data) => {
        const panel = document.querySelector(
            '[data-death-saves]'
        );

        if (!panel || !data) {
            return;
        }

        const vitality = data.vitality || {};
        const saves = data.death_saves || {};

        if (Number(vitality.current_hp || 0) > 0) {
            panel.remove();
            return;
        }

        const heading = panel.querySelector('strong');
        const details = panel.querySelector('span');
        const rollButton = panel.querySelector(
            '[data-roll-death-save]'
        );

        if (saves.dead) {
            if (heading) heading.textContent = 'DECEASED';
            if (details) details.textContent = 'Death confirmed';
            if (rollButton) rollButton.remove();
            return;
        }

        if (saves.stable) {
            if (heading) heading.textContent = 'DOWN';
            if (details) details.textContent = 'Stable';
            if (rollButton) rollButton.remove();
            return;
        }

        if (heading) heading.textContent = 'DOWN';
        if (details) {
            details.textContent =
                `Saves ${Number(saves.successes || 0)}/3`
                + ` · Failures ${Number(saves.failures || 0)}/3`;
        }
    };

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
                syncDeathSaveHud(data);
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
                        attack_id: arsenalAttack
                            ? arsenalAttack.value
                            : '',
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
                    renderImmediateCombatOutcome(data);
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

                    say('Attack resolved — see Guild Diceworks.');
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


    activeRefreshTimer = window.setInterval(refresh, 5000);
    }

    window.addEventListener('beforeunload', () => {
        if (activeRefreshTimer) {
            window.clearInterval(activeRefreshTimer);
            activeRefreshTimer = null;
        }
    });

    bootTabletop();
}());
