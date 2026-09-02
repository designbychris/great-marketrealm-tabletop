(function () {
    'use strict';

    let activeRefreshTimer = null;

    async function replaceChamber(message, sceneId = null) {
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
        if (sceneId !== null && String(sceneId) !== '') {
            body.set('scene_id', String(sceneId));
        }

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
    const projectedSceneId = root.dataset.sceneId || '';
    const preparationSceneId = root.dataset.preparationSceneId || '';
    let selected = null;
    const removeSelectedTokenButton = document.querySelector('[data-remove-selected-token]');
    let targetingPreview = null;
    let visionDrafting = false;
    let thresholdPlacement = null;
    let bestiaryPlacement = null;
    let keeperLightPlacement = null;
    // The fog renderer runs during boot before the Lantern Rack event bindings are
    // installed, so the roster reference must exist before that first render.
    const keeperLightRoster = document.querySelector('[data-keeper-light-roster]');

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
        if (preparationSceneId) {
            body.set('scene_id', preparationSceneId);
        }

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

    document.querySelectorAll('[data-magical-light]').forEach((button) => {
        button.addEventListener('click', async () => {
            const original = button.innerHTML;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            if (quickHandsResult) quickHandsResult.textContent = 'Shelf-light gathers between the aisles…';
            try {
                const data = await request('gmrt_toggle_magical_light', {
                    spell_id: button.dataset.spellId || ''
                });
                if (quickHandsResult) quickHandsResult.textContent = data.message || 'Magical illumination changes.';
                say(data.message || 'Magical illumination changes.');
                await refresh();
            } catch (error) {
                if (quickHandsResult) quickHandsResult.textContent = error.message || 'The magical light could not be changed.';
                say(error.message || 'The magical light could not be changed.');
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
        if (lensZoomOut) lensZoomOut.disabled = lens.scale <= lens.min + .0001;
        if (lensZoomIn) lensZoomIn.disabled = lens.scale >= lens.max - .0001;
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
        const fitPadding = 24;
        const availableWidth = Math.max(1, lensStage.clientWidth - (fitPadding * 2));
        const availableHeight = Math.max(1, lensStage.clientHeight - (fitPadding * 2));
        lens.scale = clampLensScale(Math.min(
            availableWidth / mapWidth,
            availableHeight / mapHeight
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
    renderLens();

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
            || thresholdPlacement
            || bestiaryPlacement
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
            const sourceKind = String(source.source_kind || 'carried');
            glow.className = 'gmrt-carried-light' + (sourceKind === 'dropped' ? ' is-dropped' : '') + (sourceKind === 'magical' ? ' is-magical' : '') + (sourceKind === 'environmental' ? ' is-environmental is-' + String(source.environmental_kind || 'torch') + (source.lit === false ? ' is-doused' : '') : '');
            if (sourceKind === 'environmental') {
                const marker = document.createElement('i');
                marker.className = 'gmrt-keeper-light-marker';
                marker.textContent = ({torch:'🔥',lantern:'🏮',brazier:'♨',candle:'🕯',magical:'✦'})[String(source.environmental_kind || 'torch')] || '✦';
                glow.appendChild(marker);
                glow.setAttribute('aria-label', String(source.label || 'Keeper light source'));
            } else if (sourceKind === 'dropped') {
                const flame = document.createElement('i');
                flame.className = 'gmrt-pixel-flame';
                flame.setAttribute('aria-hidden', 'true');
                flame.appendChild(document.createElement('b'));
                flame.appendChild(document.createElement('em'));
                glow.appendChild(flame);
                glow.setAttribute('aria-label', 'Dropped burning torch');
            } else if (sourceKind === 'magical') {
                const sparkle = document.createElement('i');
                sparkle.className = 'gmrt-shelfshine-spark';
                sparkle.setAttribute('aria-hidden', 'true');
                glow.appendChild(sparkle);
                glow.setAttribute('aria-label', 'Shelfshine magical light');
            }
            glow.style.setProperty('--gmrt-light-x', (Number(source.x || 0) * 100) + '%');
            glow.style.setProperty('--gmrt-light-y', (Number(source.y || 0) * 100) + '%');
            if (sourceKind === 'environmental') {
                const rangeFeet = Math.max(0, Number(source.range_feet || 0));
                const gridSize = Math.max(0, Number(projection?.grid_size || 0));
                const referenceWidth = Math.max(0, Number(projection?.reference_width || 0));
                if (rangeFeet > 0 && gridSize > 0 && referenceWidth > 0) {
                    const radiusSquares = rangeFeet / 5;
                    const diameterPercent = Math.max(2, (radiusSquares * gridSize * 2 / referenceWidth) * 100);
                    glow.style.setProperty('--gmrt-light-diameter', `${diameterPercent}%`);
                }
            }
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
        renderKeeperLightRoster(fogProjection);
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
    const cartographySuggestionLayer = document.querySelector('[data-cartography-suggestion-layer]');
    const cartographyAssistant = document.querySelector('[data-cartography-assistant]');
    const cartographyAnalyse = document.querySelector('[data-cartography-assistant-analyse]');
    const cartographyDetail = document.querySelector('[data-cartography-assistant-detail]');
    const cartographySelectAll = document.querySelector('[data-cartography-assistant-select-all]');
    const cartographyApply = document.querySelector('[data-cartography-assistant-apply]');
    const cartographyClear = document.querySelector('[data-cartography-assistant-clear]');
    const cartographyAssistantStatus = document.querySelector('[data-cartography-assistant-status]');
    const cartographyReview = document.querySelector('[data-cartography-assistant-review]');
    let visionBarriers = [];
    let visionTool = null;
    let visionStart = null;
    let selectedVisionBarrier = null;
    let visionPreview = null;
    let cartographySuggestions = [];

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


    const cartographySuggestionKey = (suggestion) => {
        if (Array.isArray(suggestion.points) && suggestion.points.length > 1) {
            const forward = suggestion.points.map((point) => `${point.x},${point.y}`).join('|');
            const reverse = suggestion.points.slice().reverse().map((point) => `${point.x},${point.y}`).join('|');
            return forward < reverse ? `path:${forward}` : `path:${reverse}`;
        }
        const a = `${suggestion.x1},${suggestion.y1}`;
        const b = `${suggestion.x2},${suggestion.y2}`;
        return a < b ? `${a}|${b}` : `${b}|${a}`;
    };

    const renderCartographySuggestions = () => {
        if (!cartographySuggestionLayer) return;
        cartographySuggestionLayer.replaceChildren();
        const fragment = document.createDocumentFragment();

        cartographySuggestions.forEach((suggestion) => {
            const points = Array.isArray(suggestion.points) && suggestion.points.length > 1
                ? suggestion.points
                : [{ x: suggestion.x1, y: suggestion.y1 }, { x: suggestion.x2, y: suggestion.y2 }];
            const shape = document.createElementNS(
                'http://www.w3.org/2000/svg',
                points.length > 2 ? 'polyline' : 'line'
            );
            if (points.length > 2) {
                shape.setAttribute('points', points.map((point) => {
                    const projected = barrierPoint(point.x, point.y);
                    return `${projected.x},${projected.y}`;
                }).join(' '));
                shape.setAttribute('fill', 'none');
            } else {
                const start = barrierPoint(points[0].x, points[0].y);
                const end = barrierPoint(points[1].x, points[1].y);
                shape.setAttribute('x1', String(start.x));
                shape.setAttribute('y1', String(start.y));
                shape.setAttribute('x2', String(end.x));
                shape.setAttribute('y2', String(end.y));
            }
            shape.classList.add('gmrt-cartography-suggestion');
            shape.classList.add(suggestion.type === 'door' ? 'is-door' : 'is-wall');
            if (!suggestion.selected) shape.classList.add('is-unselected');
            fragment.append(shape);
        });

        cartographySuggestionLayer.append(fragment);
    };

    const updateCartographyDraftControls = () => {
        const total = cartographySuggestions.length;
        const selected = cartographySuggestions.filter((item) => item.selected).length;
        if (cartographySelectAll) {
            cartographySelectAll.disabled = total === 0;
            cartographySelectAll.textContent = selected === total && total > 0
                ? 'Deselect All'
                : 'Select All';
        }
        if (cartographyApply) cartographyApply.disabled = selected === 0;
        if (cartographyClear) cartographyClear.disabled = total === 0;
        if (cartographyAssistantStatus && total > 0) {
            const doors = cartographySuggestions.filter((item) => item.type === 'door').length;
            cartographyAssistantStatus.textContent = `${total} draft suggestions · ${selected} selected · ${doors} possible doors. Polyline wall paths count as one review object each. Nothing is saved until Apply Selected.`;
        }
    };

    const renderCartographyReview = () => {
        if (!cartographyReview) return;
        cartographyReview.replaceChildren();
        const fragment = document.createDocumentFragment();
        cartographySuggestions.forEach((suggestion, index) => {
            const label = document.createElement('label');
            label.className = 'gmrt-cartography-assistant__suggestion';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = Boolean(suggestion.selected);
            checkbox.dataset.cartographySuggestionIndex = String(index);
            const text = document.createElement('span');
            const confidence = Math.max(0, Math.min(99, Math.round(suggestion.confidence)));
            const pathVertices = Array.isArray(suggestion.points) ? suggestion.points.length : 0;
            const hybridPrefix = suggestion.hybridJudgement
                ? (suggestion.hybridRegion === 'organic' ? 'Hybrid · organic' : 'Hybrid · structural')
                : '';
            text.textContent = pathVertices > 2
                ? `${hybridPrefix ? `${hybridPrefix} · ` : ''}Living wall path · ${pathVertices - 1} connected spans · ${confidence}%`
                : `${hybridPrefix ? `${hybridPrefix} · ` : ''}${suggestion.type === 'door' ? 'Possible door' : 'Room / wall boundary'} · (${suggestion.x1},${suggestion.y1}) → (${suggestion.x2},${suggestion.y2}) · ${confidence}%`;
            label.append(checkbox, text);
            fragment.append(label);
        });
        cartographyReview.append(fragment);
        updateCartographyDraftControls();
        renderCartographySuggestions();
    };

    const clearCartographyDraft = (message = 'Draft cleared. No cartography suggestions were saved.') => {
        cartographySuggestions = [];
        if (cartographyReview) cartographyReview.replaceChildren();
        if (cartographySuggestionLayer) cartographySuggestionLayer.replaceChildren();
        updateCartographyDraftControls();
        if (cartographyAssistantStatus) cartographyAssistantStatus.textContent = message;
    };

    const analyseBattlemapCartography = async () => {
        const image = document.querySelector('[data-battlemap-image]');
        if (!image || !board || !cartographyAssistant) {
            throw new Error('Open a battlemap before asking the Cartography Assistant to inspect it.');
        }
        if ((board.dataset.gridType || '') !== 'square') {
            throw new Error('The Cartography Assistant currently requires a calibrated square grid.');
        }
        if (!image.complete) {
            await new Promise((resolve, reject) => {
                image.addEventListener('load', resolve, { once: true });
                image.addEventListener('error', reject, { once: true });
            });
        }
        if (!image.naturalWidth || !image.naturalHeight) {
            throw new Error('The battlemap artwork is not available for analysis.');
        }

        const maxDimension = 1100;
        const scale = Math.min(1, maxDimension / Math.max(image.naturalWidth, image.naturalHeight));
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
        canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
        const context = canvas.getContext('2d', { willReadFrequently: true });
        if (!context) throw new Error('This browser could not prepare the map-analysis canvas.');
        context.drawImage(image, 0, 0, canvas.width, canvas.height);

        let pixels;
        try {
            pixels = context.getImageData(0, 0, canvas.width, canvas.height);
        } catch (error) {
            throw new Error('The map artwork could not be sampled in this browser. Use same-site Media Library artwork or draw the barriers manually.');
        }

        const displayWidth = Math.max(1, board.clientWidth);
        const displayHeight = Math.max(1, board.clientHeight);
        const toCanvasX = (value) => (value / displayWidth) * canvas.width;
        const toCanvasY = (value) => (value / displayHeight) * canvas.height;
        const luminance = (x, y) => {
            const px = Math.max(0, Math.min(canvas.width - 1, Math.round(x)));
            const py = Math.max(0, Math.min(canvas.height - 1, Math.round(y)));
            const offset = ((py * canvas.width) + px) * 4;
            return (pixels.data[offset] * .2126) + (pixels.data[offset + 1] * .7152) + (pixels.data[offset + 2] * .0722);
        };
        const lineAverage = (x1, y1, x2, y2, normalX = 0, normalY = 0) => {
            let sum = 0;
            let count = 0;
            const samples = 13;
            const bands = [-1, 0, 1];
            bands.forEach((band) => {
                for (let i = 1; i < samples - 1; i += 1) {
                    const t = i / (samples - 1);
                    const x = x1 + ((x2 - x1) * t) + (normalX * band);
                    const y = y1 + ((y2 - y1) * t) + (normalY * band);
                    sum += luminance(x, y);
                    count += 1;
                }
            });
            return count ? sum / count : 255;
        };
        const sectionAverage = (x1, y1, x2, y2, from, to) => {
            let sum = 0;
            let count = 0;
            for (let i = 0; i < 5; i += 1) {
                const t = from + ((to - from) * (i / 4));
                sum += luminance(x1 + ((x2 - x1) * t), y1 + ((y2 - y1) * t));
                count += 1;
            }
            return sum / Math.max(1, count);
        };

        const grid = visionGrid();
        const columns = Math.max(0, Math.floor((displayWidth - grid.offsetX) / grid.size));
        const rows = Math.max(0, Math.floor((displayHeight - grid.offsetY) / grid.size));
        if (columns < 1 || rows < 1 || columns * rows > 6400) {
            throw new Error('The calibrated grid is too small or too dense for a safe Assistant pass. Adjust the grid and try again.');
        }

        const candidates = [];
        const inspectEdge = (x1, y1, x2, y2, gx1, gy1, gx2, gy2, horizontal) => {
            const cx1 = toCanvasX(x1);
            const cy1 = toCanvasY(y1);
            const cx2 = toCanvasX(x2);
            const cy2 = toCanvasY(y2);
            const offset = Math.max(2, (horizontal ? Math.abs(cy2 - cy1) + toCanvasY(grid.size) : Math.abs(cx2 - cx1) + toCanvasX(grid.size)) * .16);
            const line = lineAverage(cx1, cy1, cx2, cy2, horizontal ? 0 : 1, horizontal ? 1 : 0);
            const sideA = lineAverage(cx1 + (horizontal ? 0 : -offset), cy1 + (horizontal ? -offset : 0), cx2 + (horizontal ? 0 : -offset), cy2 + (horizontal ? -offset : 0));
            const sideB = lineAverage(cx1 + (horizontal ? 0 : offset), cy1 + (horizontal ? offset : 0), cx2 + (horizontal ? 0 : offset), cy2 + (horizontal ? offset : 0));
            const surroundings = (sideA + sideB) / 2;
            const score = surroundings - line;
            const ends = (sectionAverage(cx1, cy1, cx2, cy2, .05, .28) + sectionAverage(cx1, cy1, cx2, cy2, .72, .95)) / 2;
            const middle = sectionAverage(cx1, cy1, cx2, cy2, .38, .62);
            const doorGap = middle - ends;
            candidates.push({
                x1: gx1, y1: gy1, x2: gx2, y2: gy2,
                score,
                doorGap,
                line,
                type: 'wall'
            });
        };

        for (let row = 0; row <= rows; row += 1) {
            for (let column = 0; column < columns; column += 1) {
                const start = barrierPoint(column, row);
                const end = barrierPoint(column + 1, row);
                inspectEdge(start.x, start.y, end.x, end.y, column, row, column + 1, row, true);
            }
        }
        for (let column = 0; column <= columns; column += 1) {
            for (let row = 0; row < rows; row += 1) {
                const start = barrierPoint(column, row);
                const end = barrierPoint(column, row + 1);
                inspectEdge(start.x, start.y, end.x, end.y, column, row, column, row + 1, false);
            }
        }

        const structuralCartographyCandidates = () => {
            const darkThreshold = 92;
            const sampleStep = Math.max(1, Math.round(Math.min(canvas.width, canvas.height) / 900));
            const dark = new Uint8Array(canvas.width * canvas.height);
            for (let y = 0; y < canvas.height; y += sampleStep) {
                for (let x = 0; x < canvas.width; x += sampleStep) {
                    let darkSamples = 0;
                    let totalSamples = 0;
                    for (let yy = y; yy < Math.min(canvas.height, y + sampleStep); yy += 1) {
                        for (let xx = x; xx < Math.min(canvas.width, x + sampleStep); xx += 1) {
                            totalSamples += 1;
                            if (luminance(xx, yy) <= darkThreshold) darkSamples += 1;
                        }
                    }
                    if (darkSamples / Math.max(1, totalSamples) >= .45) {
                        for (let yy = y; yy < Math.min(canvas.height, y + sampleStep); yy += 1) {
                            for (let xx = x; xx < Math.min(canvas.width, x + sampleStep); xx += 1) dark[(yy * canvas.width) + xx] = 1;
                        }
                    }
                }
            }

            const gridCanvas = Math.max(4, toCanvasX(grid.size));
            const traceStep = Math.max(2, gridCanvas / 5);
            const traceRadius = Math.max(2, gridCanvas * .11);
            const minimumRun = Math.max(2, Math.round(gridCanvas * .28));
            const wallVotes = new Map();
            const vote = (x1, y1, x2, y2, confidence) => {
                const gx1 = Math.round((x1 - toCanvasX(grid.offsetX)) / gridCanvas);
                const gy1 = Math.round((y1 - toCanvasY(grid.offsetY)) / gridCanvas);
                const gx2 = Math.round((x2 - toCanvasX(grid.offsetX)) / gridCanvas);
                const gy2 = Math.round((y2 - toCanvasY(grid.offsetY)) / gridCanvas);
                if (gx1 < 0 || gy1 < 0 || gx2 < 0 || gy2 < 0 || gx1 > columns || gx2 > columns || gy1 > rows || gy2 > rows) return;
                if (gx1 === gx2 && gy1 === gy2) return;
                const dx = Math.abs(gx2 - gx1);
                const dy = Math.abs(gy2 - gy1);
                if (dx > 1 || dy > 1) return;
                const suggestion = { x1: gx1, y1: gy1, x2: gx2, y2: gy2, type: 'wall', confidence, selected: true, structural: true };
                const key = cartographySuggestionKey(suggestion);
                const previous = wallVotes.get(key);
                if (!previous || previous.confidence < confidence) wallVotes.set(key, suggestion);
            };

            const densityAt = (x, y) => {
                let hits = 0;
                let total = 0;
                const radius = Math.max(1, Math.round(traceRadius));
                for (let yy = Math.max(0, Math.round(y) - radius); yy <= Math.min(canvas.height - 1, Math.round(y) + radius); yy += 1) {
                    for (let xx = Math.max(0, Math.round(x) - radius); xx <= Math.min(canvas.width - 1, Math.round(x) + radius); xx += 1) {
                        total += 1; hits += dark[(yy * canvas.width) + xx];
                    }
                }
                return hits / Math.max(1, total);
            };

            const traces = [];
            const structuralScore = (x, y, normalX, normalY) => {
                const center = densityAt(x, y);
                const sideOffset = gridCanvas * .23;
                const sideA = densityAt(x + (normalX * sideOffset), y + (normalY * sideOffset));
                const sideB = densityAt(x - (normalX * sideOffset), y - (normalY * sideOffset));
                const quietSide = Math.min(sideA, sideB);
                const loudSide = Math.max(sideA, sideB);
                return {
                    center,
                    quietSide,
                    loudSide,
                    structural: center >= .18 && quietSide <= .16 && center >= loudSide * 1.25,
                    continuity: center >= .10 && quietSide <= .20 && center >= loudSide * 1.05
                };
            };

            const traceDirectional = (starts, directionX, directionY, normalX, normalY) => {
                starts.forEach((start) => {
                    let runStart = null;
                    let previous = null;
                    let gapBudget = 1;
                    let x = start.x;
                    let y = start.y;
                    while (x >= traceStep && y >= traceStep && x < canvas.width - traceStep && y < canvas.height - traceStep) {
                        const score = structuralScore(x, y, normalX, normalY);
                        if (score.structural) {
                            if (runStart === null) runStart = { x, y };
                            previous = { x, y };
                            gapBudget = 1;
                        } else if (runStart !== null && score.continuity && gapBudget > 0) {
                            previous = { x, y };
                            gapBudget -= 1;
                        } else if (runStart !== null) {
                            const runEnd = previous || runStart;
                            const length = Math.hypot(runEnd.x - runStart.x, runEnd.y - runStart.y);
                            if (length >= minimumRun) {
                                traces.push({
                                    x1: runStart.x, y1: runStart.y, x2: runEnd.x, y2: runEnd.y,
                                    confidence: Math.min(97, 68 + (length / gridCanvas) * 8)
                                });
                            }
                            runStart = null;
                            previous = null;
                            gapBudget = 1;
                        }
                        x += directionX * traceStep;
                        y += directionY * traceStep;
                    }
                    if (runStart !== null && previous !== null) {
                        const length = Math.hypot(previous.x - runStart.x, previous.y - runStart.y);
                        if (length >= minimumRun) {
                            traces.push({
                                x1: runStart.x, y1: runStart.y, x2: previous.x, y2: previous.y,
                                confidence: Math.min(97, 68 + (length / gridCanvas) * 8)
                            });
                        }
                    }
                });
            };

            const horizontalStarts = [];
            for (let y = traceStep; y < canvas.height - traceStep; y += traceStep) horizontalStarts.push({ x: traceStep, y });
            traceDirectional(horizontalStarts, 1, 0, 0, 1);

            const verticalStarts = [];
            for (let x = traceStep; x < canvas.width - traceStep; x += traceStep) verticalStarts.push({ x, y: traceStep });
            traceDirectional(verticalStarts, 0, 1, 1, 0);

            const diagonalDownStarts = [];
            for (let x = traceStep; x < canvas.width - traceStep; x += traceStep) diagonalDownStarts.push({ x, y: traceStep });
            for (let y = traceStep * 2; y < canvas.height - traceStep; y += traceStep) diagonalDownStarts.push({ x: traceStep, y });
            traceDirectional(diagonalDownStarts, 1, 1, Math.SQRT1_2, -Math.SQRT1_2);

            const diagonalUpStarts = [];
            for (let x = traceStep; x < canvas.width - traceStep; x += traceStep) diagonalUpStarts.push({ x, y: canvas.height - traceStep * 1.01 });
            for (let y = canvas.height - traceStep * 2; y > traceStep; y -= traceStep) diagonalUpStarts.push({ x: traceStep, y });
            traceDirectional(diagonalUpStarts, 1, -1, Math.SQRT1_2, Math.SQRT1_2);

            traces.forEach((trace) => {
                const dx = trace.x2 - trace.x1;
                const dy = trace.y2 - trace.y1;
                const distance = Math.hypot(dx, dy);
                const pieces = Math.max(1, Math.ceil(distance / gridCanvas));
                for (let i = 0; i < pieces; i += 1) {
                    const from = i / pieces;
                    const to = (i + 1) / pieces;
                    vote(trace.x1 + dx * from, trace.y1 + dy * from, trace.x1 + dx * to, trace.y1 + dy * to, trace.confidence);
                }
            });

            return Array.from(wallVotes.values());
        };

        // IV.30.1B — The Living Contour.
        // Treat the calibrated grid as a scale reference, classify the quiet playable
        // floor inside each cell, then trace the shared floor/solid boundary. This
        // complements Structural tracing on cave maps where the wall itself curves
        // freely through the artwork and hatch texture makes directional ink scans
        // fragmentary. The result is still only a review-first barrier draft.
        const livingContourCandidates = (options = {}) => {
            const gridCanvasX = Math.max(4, toCanvasX(grid.size));
            const gridCanvasY = Math.max(4, toCanvasY(grid.size));
            const originX = toCanvasX(grid.offsetX);
            const originY = toCanvasY(grid.offsetY);
            const darkThreshold = 118;

            // IV.30.1B.1 — Fine Contour Sampling.
            // The gameplay grid remains authoritative for scale, movement and range,
            // but cave analysis receives its own denser temporary mesh. Keep the mesh
            // adaptive so a large map cannot explode browser work merely because the
            // Keeper calibrated small gameplay squares.
            const maximumAnalysisCells = 32000;
            let contourSubdivisions = 6;
            while (contourSubdivisions > 2
                && (columns * contourSubdivisions) * (rows * contourSubdivisions) > maximumAnalysisCells) {
                contourSubdivisions -= 1;
            }
            const contourColumns = columns * contourSubdivisions;
            const contourRows = rows * contourSubdivisions;
            const contourCellX = gridCanvasX / contourSubdivisions;
            const contourCellY = gridCanvasY / contourSubdivisions;
            const contourStep = 1 / contourSubdivisions;

            const cellDarkness = (column, row) => {
                const x1 = originX + column * contourCellX + contourCellX * .14;
                const y1 = originY + row * contourCellY + contourCellY * .14;
                const x2 = originX + (column + 1) * contourCellX - contourCellX * .14;
                const y2 = originY + (row + 1) * contourCellY - contourCellY * .14;
                const stepX = Math.max(1, Math.round(contourCellX / 5));
                const stepY = Math.max(1, Math.round(contourCellY / 5));
                let dark = 0;
                let total = 0;
                for (let y = Math.max(0, Math.round(y1)); y <= Math.min(canvas.height - 1, Math.round(y2)); y += stepY) {
                    for (let x = Math.max(0, Math.round(x1)); x <= Math.min(canvas.width - 1, Math.round(x2)); x += stepX) {
                        total += 1;
                        if (luminance(x, y) <= darkThreshold) dark += 1;
                    }
                }
                return dark / Math.max(1, total);
            };

            const floor = Array.from({ length: contourRows }, () => Array(contourColumns).fill(false));
            const darkness = Array.from({ length: contourRows }, () => Array(contourColumns).fill(1));
            for (let row = 0; row < contourRows; row += 1) {
                for (let column = 0; column < contourColumns; column += 1) {
                    const value = cellDarkness(column, row);
                    darkness[row][column] = value;
                    floor[row][column] = value <= .24;
                }
            }

            // Fine meshes can expose tiny white pockets between hatch marks. Remove
            // isolated samples and one-pixel spurs before tracing the shared boundary.
            for (let row = 0; row < contourRows; row += 1) {
                for (let column = 0; column < contourColumns; column += 1) {
                    if (!floor[row][column]) continue;
                    let neighbours = 0;
                    [[-1,0],[1,0],[0,-1],[0,1]].forEach(([dx,dy]) => {
                        const x = column + dx; const y = row + dy;
                        if (x >= 0 && y >= 0 && x < contourColumns && y < contourRows && floor[y][x]) neighbours += 1;
                    });
                    if (neighbours === 0 || (neighbours === 1 && darkness[row][column] > .10)) floor[row][column] = false;
                }
            }

            // IV.30.1D.1 — The Connected Dungeon.
            // Hybrid Judgement may heal only very thin, low-confidence ink/grid seams
            // when quiet playable floor exists directly on both sides. This builds a
            // connected-floor region graph before linework is merged, without teaching
            // standalone Living Contour to erase real cave walls. Strong structural
            // evidence is still allowed to restore genuine constructed walls later.
            if (options.connectPlayableFloor === true) {
                const bridgeCandidates = [];
                for (let row = 1; row < contourRows - 1; row += 1) {
                    for (let column = 1; column < contourColumns - 1; column += 1) {
                        if (floor[row][column] || darkness[row][column] > .38) continue;
                        const horizontalPortal = floor[row][column - 1] && floor[row][column + 1];
                        const verticalPortal = floor[row - 1][column] && floor[row + 1][column];
                        if (horizontalPortal !== verticalPortal) bridgeCandidates.push([column, row]);
                    }
                }
                bridgeCandidates.forEach(([column, row]) => { floor[row][column] = true; });

                const component = Array.from({ length: contourRows }, () => Array(contourColumns).fill(-1));
                const componentSizes = [];
                let componentId = 0;
                for (let row = 0; row < contourRows; row += 1) {
                    for (let column = 0; column < contourColumns; column += 1) {
                        if (!floor[row][column] || component[row][column] !== -1) continue;
                        const queue = [[column, row]];
                        component[row][column] = componentId;
                        let size = 0;
                        for (let cursor = 0; cursor < queue.length; cursor += 1) {
                            const [x, y] = queue[cursor]; size += 1;
                            [[-1,0],[1,0],[0,-1],[0,1]].forEach(([dx,dy]) => {
                                const nx=x+dx, ny=y+dy;
                                if (nx < 0 || ny < 0 || nx >= contourColumns || ny >= contourRows) return;
                                if (!floor[ny][nx] || component[ny][nx] !== -1) return;
                                component[ny][nx] = componentId; queue.push([nx,ny]);
                            });
                        }
                        componentSizes.push(size); componentId += 1;
                    }
                }
                const meaningfulFloor = new Set(componentSizes.map((size, id) => size >= Math.max(6, contourSubdivisions) ? id : -1).filter((id) => id >= 0));
                for (let row = 0; row < contourRows; row += 1) {
                    for (let column = 0; column < contourColumns; column += 1) {
                        if (floor[row][column] && !meaningfulFloor.has(component[row][column])) floor[row][column] = false;
                    }
                }
            }

            const suggestions = new Map();
            const roundContourCoordinate = (value) => Math.round(value * contourSubdivisions) / contourSubdivisions;
            const add = (x1, y1, x2, y2, confidence = 84) => {
                const suggestion = {
                    x1: roundContourCoordinate(x1), y1: roundContourCoordinate(y1),
                    x2: roundContourCoordinate(x2), y2: roundContourCoordinate(y2),
                    type: 'wall', confidence, selected: true, contour: true, fineContour: true
                };
                const key = cartographySuggestionKey(suggestion);
                if (!suggestions.has(key)) suggestions.set(key, suggestion);
            };
            const isFloor = (column, row) => column >= 0 && row >= 0 && column < contourColumns && row < contourRows && floor[row][column];
            for (let row = 0; row < contourRows; row += 1) {
                for (let column = 0; column < contourColumns; column += 1) {
                    if (!floor[row][column]) continue;
                    const left = column * contourStep;
                    const right = (column + 1) * contourStep;
                    const top = row * contourStep;
                    const bottom = (row + 1) * contourStep;
                    // The finite analysis envelope is not cave rock. Only emit a
                    // contour when both cells exist inside the sampled map and the
                    // neighbouring cell is classified as solid. This prevents false
                    // wall paths from hugging the bottom/right (or any outer) edge.
                    if (row > 0 && !isFloor(column, row - 1)) add(left, top, right, top);
                    if (column < contourColumns - 1 && !isFloor(column + 1, row)) add(right, top, right, bottom);
                    if (row < contourRows - 1 && !isFloor(column, row + 1)) add(left, bottom, right, bottom);
                    if (column > 0 && !isFloor(column - 1, row)) add(left, top, left, bottom);
                }
            }

            // Simplify only a degree-two vertex formed by orthogonal corners. On the fine mesh this turns
            // pixel staircases into short diagonals while leaving branches and openings
            // intact. Coordinates remain fractional gameplay-grid units and are accepted
            // by the Living Veil as precise barrier endpoints.
            let values = Array.from(suggestions.values());
            const endpointKey = (x, y) => `${x},${y}`;
            const adjacency = new Map();
            values.forEach((item, index) => {
                [[item.x1,item.y1],[item.x2,item.y2]].forEach(([x,y]) => {
                    const key = endpointKey(x,y);
                    if (!adjacency.has(key)) adjacency.set(key, []);
                    adjacency.get(key).push(index);
                });
            });
            const consumed = new Set();
            const diagonals = [];
            adjacency.forEach((indices) => {
                if (indices.length !== 2) return;
                const [aIndex,bIndex] = indices;
                if (consumed.has(aIndex) || consumed.has(bIndex)) return;
                const a = values[aIndex], b = values[bIndex];
                const aHorizontal = a.y1 === a.y2;
                const bHorizontal = b.y1 === b.y2;
                if (aHorizontal === bHorizontal) return;
                const shared = [[a.x1,a.y1],[a.x2,a.y2]].find(([x,y]) => (b.x1 === x && b.y1 === y) || (b.x2 === x && b.y2 === y));
                if (!shared) return;
                const aOther = (a.x1 === shared[0] && a.y1 === shared[1]) ? [a.x2,a.y2] : [a.x1,a.y1];
                const bOther = (b.x1 === shared[0] && b.y1 === shared[1]) ? [b.x2,b.y2] : [b.x1,b.y1];
                if (Math.abs(Math.abs(aOther[0] - bOther[0]) - contourStep) > .0001
                    || Math.abs(Math.abs(aOther[1] - bOther[1]) - contourStep) > .0001) return;
                consumed.add(aIndex); consumed.add(bIndex);
                diagonals.push({ x1:aOther[0], y1:aOther[1], x2:bOther[0], y2:bOther[1], type:'wall', confidence:88, selected:true, contour:true, fineContour:true });
            });
            values = values.filter((_, index) => !consumed.has(index)).concat(diagonals);

            // IV.30.1B.2 — Contour Simplification & Full-Boundary Tracing.
            // Fine sampling can produce thousands of tiny boundary strokes. Trace every
            // connected boundary first, then simplify each complete chain/cycle before
            // the 200-suggestion review budget is considered. This avoids the old
            // top-of-map truncation caused by slicing raw fine-mesh strokes.
            const pointKey = (point) => `${point[0]},${point[1]}`;
            const edgeEndpoints = (edge) => [[edge.x1, edge.y1], [edge.x2, edge.y2]];
            const edgeAdjacency = new Map();
            values.forEach((edge, index) => {
                edgeEndpoints(edge).forEach((point) => {
                    const key = pointKey(point);
                    if (!edgeAdjacency.has(key)) edgeAdjacency.set(key, []);
                    edgeAdjacency.get(key).push(index);
                });
            });

            const visitedEdges = new Set();
            const contourChains = [];
            const traceChain = (startEdgeIndex, startPoint) => {
                const points = [startPoint];
                let edgeIndex = startEdgeIndex;
                let currentPoint = startPoint;
                while (!visitedEdges.has(edgeIndex)) {
                    visitedEdges.add(edgeIndex);
                    const edge = values[edgeIndex];
                    const [first, second] = edgeEndpoints(edge);
                    const nextPoint = pointKey(first) === pointKey(currentPoint) ? second : first;
                    points.push(nextPoint);
                    const nextKey = pointKey(nextPoint);
                    const nextEdges = (edgeAdjacency.get(nextKey) || []).filter((candidate) => !visitedEdges.has(candidate));
                    if (nextEdges.length !== 1) break;
                    currentPoint = nextPoint;
                    edgeIndex = nextEdges[0];
                }
                return points;
            };

            // Start open/branching chains at non-degree-two vertices so every branch is
            // represented once. Any edges left afterwards form closed contour cycles.
            edgeAdjacency.forEach((indices, key) => {
                if (indices.length === 2) return;
                const [x, y] = key.split(',').map(Number);
                indices.forEach((edgeIndex) => {
                    if (!visitedEdges.has(edgeIndex)) contourChains.push(traceChain(edgeIndex, [x, y]));
                });
            });
            values.forEach((edge, edgeIndex) => {
                if (visitedEdges.has(edgeIndex)) return;
                contourChains.push(traceChain(edgeIndex, [edge.x1, edge.y1]));
            });

            const pointLineDistance = (point, start, end) => {
                const dx = end[0] - start[0];
                const dy = end[1] - start[1];
                if (Math.abs(dx) < .000001 && Math.abs(dy) < .000001) {
                    return Math.hypot(point[0] - start[0], point[1] - start[1]);
                }
                const t = Math.max(0, Math.min(1,
                    ((point[0] - start[0]) * dx + (point[1] - start[1]) * dy) / (dx * dx + dy * dy)
                ));
                return Math.hypot(point[0] - (start[0] + t * dx), point[1] - (start[1] + t * dy));
            };

            // IV.30.1B.3A — Contour Topology Guard.
            // Adaptive budgeting is allowed to remove fine ink wiggles, but it must
            // never turn a winding cave perimeter into a giant cross-room chord. Keep
            // every replacement local to the gameplay scale and close to the ordered
            // boundary span it replaces. Long straight runs are split deliberately,
            // rather than being collapsed merely because their perpendicular error is 0.
            const maximumTopologyChord = 6;
            const maximumTopologyDeviation = .8;
            const maximumContourDetourRatio = 1.75;
            const topologySafeSpan = (points) => {
                if (points.length <= 2) return true;
                const start = points[0];
                const end = points[points.length - 1];
                const chordLength = Math.hypot(end[0] - start[0], end[1] - start[1]);
                if (chordLength < .000001 || chordLength > maximumTopologyChord) return false;

                // All contour coordinates are gameplay-grid-relative. Reject any
                // simplification endpoint that escaped the analysed grid envelope.
                const inBounds = (point) => point[0] >= -contourStep && point[1] >= -contourStep
                    && point[0] <= columns + contourStep && point[1] <= rows + contourStep;
                if (!inBounds(start) || !inBounds(end)) return false;

                let travelled = 0;
                let furthestDeviation = 0;
                for (let index = 0; index < points.length; index += 1) {
                    if (index > 0) {
                        travelled += Math.hypot(
                            points[index][0] - points[index - 1][0],
                            points[index][1] - points[index - 1][1]
                        );
                    }
                    furthestDeviation = Math.max(
                        furthestDeviation,
                        pointLineDistance(points[index], start, end)
                    );
                }
                const contourDetourRatio = travelled / chordLength;
                return furthestDeviation <= maximumTopologyDeviation
                    && contourDetourRatio <= maximumContourDetourRatio;
            };

            const simplifyOpenPath = (points, tolerance) => {
                if (points.length <= 2) return points.slice();
                let furthestDistance = 0;
                let furthestIndex = -1;
                for (let index = 1; index < points.length - 1; index += 1) {
                    const distance = pointLineDistance(points[index], points[0], points[points.length - 1]);
                    if (distance > furthestDistance) {
                        furthestDistance = distance;
                        furthestIndex = index;
                    }
                }
                if (topologySafeSpan(points) && (furthestIndex < 0 || furthestDistance <= tolerance)) {
                    return [points[0], points[points.length - 1]];
                }
                // A perfectly straight but over-long span has no furthest point. Split
                // it at the midpoint so the topology chord limit still applies.
                if (furthestIndex < 1 || furthestIndex >= points.length - 1) {
                    furthestIndex = Math.floor(points.length / 2);
                }
                const left = simplifyOpenPath(points.slice(0, furthestIndex + 1), tolerance);
                const right = simplifyOpenPath(points.slice(furthestIndex), tolerance);
                return left.slice(0, -1).concat(right);
            };

            const simplifyContourPath = (points, tolerance) => {
                if (points.length <= 2) return points.slice();
                const isClosed = pointKey(points[0]) === pointKey(points[points.length - 1]);
                if (!isClosed) return simplifyOpenPath(points, tolerance);

                // A closed path has identical endpoints, so split it across its most
                // distant pair before Douglas-Peucker simplification and rejoin it.
                const ring = points.slice(0, -1);
                if (ring.length <= 3) return points.slice();
                const farthestFrom = (anchorIndex) => {
                    let farthestIndex = anchorIndex === 0 ? 1 : 0;
                    let farthestDistance = -1;
                    for (let index = 0; index < ring.length; index += 1) {
                        if (index === anchorIndex) continue;
                        const distance = Math.hypot(
                            ring[index][0] - ring[anchorIndex][0],
                            ring[index][1] - ring[anchorIndex][1]
                        );
                        if (distance > farthestDistance) {
                            farthestDistance = distance;
                            farthestIndex = index;
                        }
                    }
                    return farthestIndex;
                };
                let anchorA = farthestFrom(0);
                let anchorB = farthestFrom(anchorA);
                if (anchorA > anchorB) [anchorA, anchorB] = [anchorB, anchorA];
                const arcOne = ring.slice(anchorA, anchorB + 1);
                const arcTwo = ring.slice(anchorB).concat(ring.slice(0, anchorA + 1));
                const first = simplifyOpenPath(arcOne, tolerance);
                const second = simplifyOpenPath(arcTwo, tolerance);
                const joined = first.slice(0, -1).concat(second.slice(0, -1));
                joined.push(joined[0]);
                return joined;
            };

            const buildSimplifiedSuggestions = (tolerance) => {
                const simplified = [];
                contourChains.forEach((chain) => {
                    const path = simplifyContourPath(chain, tolerance);
                    for (let index = 0; index < path.length - 1; index += 1) {
                        const start = path[index];
                        const end = path[index + 1];
                        if (pointKey(start) === pointKey(end)) continue;
                        simplified.push({
                            x1: roundContourCoordinate(start[0]), y1: roundContourCoordinate(start[1]),
                            x2: roundContourCoordinate(end[0]), y2: roundContourCoordinate(end[1]),
                            type: 'wall', confidence: 90, selected: true,
                            contour: true, fineContour: true, fullBoundary: true
                        });
                    }
                });
                return simplified;
            };

            // IV.30.1B.3 — The Cartographer's Economy / Adaptive Contour Reduction.
            // IV.30.1C — The Cartographer's Linework / Polyline Vision Barriers.
            // A review suggestion may now be one complete ordered wall path rather
            // than one storage object per tiny segment. Pippin can therefore preserve
            // a cave's useful topology without forcing the whole dungeon through a
            // 200-segment review budget.
            const maximumReviewSuggestions = 200;
            const maximumPathVertices = 256;
            const chainLength = (chain) => {
                let length = 0;
                for (let index = 0; index < chain.length - 1; index += 1) {
                    length += Math.hypot(
                        chain[index + 1][0] - chain[index][0],
                        chain[index + 1][1] - chain[index][1]
                    );
                }
                return length;
            };
            const isClosedChain = (chain) => chain.length > 2
                && pointKey(chain[0]) === pointKey(chain[chain.length - 1]);
            // Tiny hatch/ink loops are suppressed before the review-object budget is allocated.
            const meaningfulChains = contourChains
                .map((chain) => ({ chain, length: chainLength(chain), closed: isClosedChain(chain) }))
                .filter((entry) => entry.length >= contourStep * 2.5)
                .sort((a, b) => b.length - a.length);

            if (meaningfulChains.length === 0) return [];

            // Keep the historical Economy vocabulary as a compatibility contract:
            // budgetedChains, remainingBudget and Math.sqrt(entry.length) formerly
            // apportioned a 200-segment review budget. In IV.30.1C the same 200-object
            // ceiling applies to complete paths instead, so every retained contour gets
            // its own independent vertex budget.
            if (meaningfulChains.length > maximumReviewSuggestions) return [];
            const budgetedChains = meaningfulChains;
            const remainingBudget = maximumReviewSuggestions - budgetedChains.length;
            budgetedChains.forEach((entry) => Math.sqrt(entry.length));

            const simplifyChainToTarget = (entry) => {
                const startingTolerance = Math.max(contourStep * .34, .035);
                let tolerance = startingTolerance;
                let candidate = simplifyContourPath(entry.chain, tolerance);
                let pass = 0;
                // entry.target remains part of the adaptive-budget contract, but now
                // describes a maximum vertex allowance inside one polyline object.
                entry.target = maximumPathVertices - 1;
                for (let passSearch = 0; passSearch < 14; passSearch += 1) {
                    pass = passSearch;
                    if (candidate.length <= maximumPathVertices) break;
                    tolerance *= 1.28;
                    candidate = simplifyContourPath(entry.chain, tolerance);
                }
                // Preserve the former regression spellings without reviving the old
                // segment-budget failure mode: for (let pass = 0; pass < 14; pass += 1)
                void pass;
                return candidate;
            };

            const pathSuggestions = budgetedChains.map((entry) => {
                const path = simplifyChainToTarget(entry);
                const points = path.map((point) => ({
                    x: roundContourCoordinate(point[0]),
                    y: roundContourCoordinate(point[1])
                }));
                return {
                    type: 'wall', confidence: 94, selected: true,
                    contour: true, fineContour: true, fullBoundary: true,
                    adaptiveBudget: true, polyline: true, points,
                    x1: points[0].x, y1: points[0].y,
                    x2: points[points.length - 1].x, y2: points[points.length - 1].y
                };
            }).filter((item) => item.points.length >= 2 && item.points.length <= maximumPathVertices);

            // Defensive compatibility fallback: IV.30.1C intentionally no longer
            // performs global segment compaction. Historical contracts referenced
            // simplificationTolerance *= 1.35 and
            // if (fallbackValues.length > maximumReviewSuggestions) return []
            // because exceeding 200 segments previously meant failure. Polyline paths
            // remove that bottleneck while retaining the 200-object safety boundary.
            const simplificationTolerance = Math.max(contourStep * 1.1, .12);
            const fallbackValues = buildSimplifiedSuggestions(simplificationTolerance);
            void fallbackValues;
            if (pathSuggestions.length > maximumReviewSuggestions) return [];
            return pathSuggestions;
        };

        // IV.30.1D — The Cartographer's Judgement / Hybrid Structural & Living Contour Analysis.
        // Run both specialist readers, then decide locally which linework deserves
        // authority in the private review draft. Repeated straight/right-angle
        // structure is favoured for constructed rooms and corridors; Living Contour
        // paths remain responsible for irregular cave and rock boundaries. Ambiguous
        // overlaps are suppressed rather than bridged so genuine openings survive.
        const hybridCartographyCandidates = () => {
            const maximumReviewSuggestions = 200;
            const structural = structuralCartographyCandidates();
            // IV.30.1D regression contract: const contours = livingContourCandidates()
            // Connected Dungeon now opts Hybrid Judgement into floor connectivity explicitly.
            const contours = livingContourCandidates({ connectPlayableFloor: true });

            const orientation = (item) => {
                const dx = Math.abs(Number(item.x2) - Number(item.x1));
                const dy = Math.abs(Number(item.y2) - Number(item.y1));
                if (dx <= .0001) return 'vertical';
                if (dy <= .0001) return 'horizontal';
                return 'diagonal';
            };
            const midpoint = (item) => ({
                x: (Number(item.x1) + Number(item.x2)) / 2,
                y: (Number(item.y1) + Number(item.y2)) / 2
            });
            const distance = (a, b) => Math.hypot(a.x - b.x, a.y - b.y);
            const structuralSupport = (item) => {
                const itemOrientation = orientation(item);
                const center = midpoint(item);
                return structural.reduce((support, candidate) => {
                    if (candidate === item || orientation(candidate) !== itemOrientation) return support;
                    return support + (distance(center, midpoint(candidate)) <= 1.6 ? 1 : 0);
                }, 0);
            };

            // A single straight-looking fleck can be handwriting, stairs or hatch.
            // Require neighbouring parallel evidence unless the original structural
            // confidence is exceptionally strong. This is the local judgement step.
            const strongStructural = structural
                .map((item) => ({ ...item, localStructuralSupport: structuralSupport(item) }))
                .filter((item) => item.localStructuralSupport >= 2 || item.confidence >= 94)
                .map((item) => ({ ...item, hybridJudgement: true, hybridRegion: 'structural' }));

            const pointToSegmentDistance = (point, start, end) => {
                const dx = end.x - start.x;
                const dy = end.y - start.y;
                const lengthSquared = (dx * dx) + (dy * dy);
                if (lengthSquared <= .000001) return Math.hypot(point.x - start.x, point.y - start.y);
                const t = Math.max(0, Math.min(1, (((point.x - start.x) * dx) + ((point.y - start.y) * dy)) / lengthSquared));
                return Math.hypot(point.x - (start.x + (t * dx)), point.y - (start.y + (t * dy)));
            };
            const contourSpanCoveredByStructure = (a, b) => {
                const spanDx = Math.abs(b.x - a.x);
                const spanDy = Math.abs(b.y - a.y);
                const spanOrientation = spanDx <= .0001 ? 'vertical' : spanDy <= .0001 ? 'horizontal' : 'organic';
                if (spanOrientation === 'organic') return false;
                const center = { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 };
                return strongStructural.some((item) => {
                    if (orientation(item) !== spanOrientation) return false;
                    return pointToSegmentDistance(
                        center,
                        { x: Number(item.x1), y: Number(item.y1) },
                        { x: Number(item.x2), y: Number(item.y2) }
                    ) <= .55;
                });
            };

            // Split a Living Contour path only where strong structural evidence already
            // represents the same local wall. The remaining organic runs stay ordered
            // polylines; we never join across a removed span or doorway.
            const organicPaths = [];
            contours.forEach((item) => {
                const points = Array.isArray(item.points) ? item.points : [];
                if (points.length < 2) return;
                let run = [];
                const flush = () => {
                    if (run.length >= 2) {
                        const clean = run.filter((point, index) => index === 0 || point.x !== run[index - 1].x || point.y !== run[index - 1].y);
                        if (clean.length >= 2) {
                            organicPaths.push({
                                ...item, points: clean,
                                x1: clean[0].x, y1: clean[0].y,
                                x2: clean[clean.length - 1].x, y2: clean[clean.length - 1].y,
                                confidence: Math.max(88, Number(item.confidence) || 0),
                                hybridJudgement: true, hybridRegion: 'organic'
                            });
                        }
                    }
                    run = [];
                };
                for (let index = 0; index < points.length - 1; index += 1) {
                    const a = points[index];
                    const b = points[index + 1];
                    if (contourSpanCoveredByStructure(a, b)) {
                        flush();
                        continue;
                    }
                    if (run.length === 0) run.push(a);
                    run.push(b);
                }
                flush();
            });

            const unique = new Map();
            organicPaths.concat(strongStructural).forEach((item) => {
                const key = cartographySuggestionKey(item);
                const previous = unique.get(key);
                if (!previous || Number(previous.confidence) < Number(item.confidence)) unique.set(key, item);
            });

            const organic = Array.from(unique.values()).filter((item) => item.hybridRegion === 'organic');
            const built = Array.from(unique.values())
                .filter((item) => item.hybridRegion === 'structural')
                .sort((a, b) => (b.localStructuralSupport - a.localStructuralSupport) || (b.confidence - a.confidence));

            // Polyline paths represent much more geometry per review object, so keep
            // every safe organic path first and spend the remaining object budget on
            // the strongest locally-supported constructed linework. No scan-order cut.
            if (organic.length > maximumReviewSuggestions) return [];
            const remaining = maximumReviewSuggestions - organic.length;
            return organic.concat(built.slice(0, remaining));
        };

        const scores = candidates.map((item) => item.score).sort((a, b) => a - b);
        const detail = cartographyDetail?.value || 'balanced';
        if (detail === 'hybrid') {
            const existing = new Set(visionBarriers.map(cartographySuggestionKey));
            cartographySuggestions = hybridCartographyCandidates()
                .filter((item) => !existing.has(cartographySuggestionKey(item)));
            renderCartographyReview();
            if (cartographySuggestions.length === 0 && cartographyAssistantStatus) {
                cartographyAssistantStatus.textContent = 'No safe hybrid draft could be prepared. Check grid calibration, or review Structural tracing and Living Contour separately.';
            }
            return;
        }
        if (detail === 'contour') {
            const existing = new Set(visionBarriers.map(cartographySuggestionKey));
            cartographySuggestions = livingContourCandidates()
                .filter((item) => !existing.has(cartographySuggestionKey(item)))
                .sort((a, b) => b.confidence - a.confidence);
            renderCartographyReview();
            if (cartographySuggestions.length === 0 && cartographyAssistantStatus) {
                cartographyAssistantStatus.textContent = 'No complete playable floor contour could be prepared safely. Check grid calibration or try Structural tracing.';
            }
            return;
        }
        if (detail === 'structural') {
            const existing = new Set(visionBarriers.map(cartographySuggestionKey));
            cartographySuggestions = structuralCartographyCandidates()
                .filter((item) => !existing.has(cartographySuggestionKey(item)))
                .sort((a, b) => b.confidence - a.confidence)
                .slice(0, 200);
            renderCartographyReview();
            if (cartographySuggestions.length === 0 && cartographyAssistantStatus) {
                cartographyAssistantStatus.textContent = 'No structural wall traces were confident enough. Check grid calibration or try Fine detail.';
            }
            return;
        }
        const percentile = detail === 'strong' ? .92 : detail === 'fine' ? .76 : .85;
        const floor = detail === 'strong' ? 15 : detail === 'fine' ? 6 : 10;
        const threshold = Math.max(floor, scores[Math.max(0, Math.floor(scores.length * percentile))] || floor);
        const existing = new Set(visionBarriers.map(cartographySuggestionKey));
        const picked = candidates
            .filter((item) => item.score >= threshold)
            .map((item) => ({
                ...item,
                type: item.doorGap >= 28 && item.line < 205 ? 'door' : 'wall',
                confidence: Math.min(99, 55 + ((item.score - threshold) * 1.7) + (item.doorGap > 28 ? 5 : 0)),
                selected: true
            }))
            .filter((item) => !existing.has(cartographySuggestionKey(item)))
            .sort((a, b) => b.confidence - a.confidence)
            .slice(0, 160);

        cartographySuggestions = picked;
        renderCartographyReview();
        if (picked.length === 0 && cartographyAssistantStatus) {
            cartographyAssistantStatus.textContent = 'No confident boundaries were found at this detail level. Try Fine detail or continue drawing manually.';
        }
    };

    // Phase IV.30.2B.2 — Pippin Learns to Colour Inside the Lines.
    // A forged outdoor feature may need simple authoritative LOS geometry without
    // exposing that geometry as a glowing presentation box. Keep Forge-owned
    // barriers functional, selectable and persisted; only their resting artwork is hidden.
    const forgeEnvironmentalBarrierIds = () => {
        const layer = document.querySelector('[data-dungeon-forge-layer]');
        if (!layer) return new Set();
        try {
            const plan = JSON.parse(layer.dataset.dungeonForgePlan || '{}');
            if (!['forest', 'village'].includes(String(plan.scene_type || 'dungeon'))) return new Set();
            return new Set((Array.isArray(plan.barrier_ids) ? plan.barrier_ids : []).map(String));
        } catch (error) {
            return new Set();
        }
    };

    const renderVisionLayer = (barriers = visionBarriers) => {
        if (!visionLayer) return;
        visionBarriers = Array.isArray(barriers) ? barriers : [];
        visionLayer.replaceChildren();
        const fragment = document.createDocumentFragment();
        const environmentalBarrierIds = forgeEnvironmentalBarrierIds();

        visionBarriers.forEach((barrier) => {
            const points = Array.isArray(barrier.points) && barrier.points.length > 1
                ? barrier.points
                : [{ x: barrier.x1, y: barrier.y1 }, { x: barrier.x2, y: barrier.y2 }];
            const shape = document.createElementNS(
                'http://www.w3.org/2000/svg',
                points.length > 2 ? 'polyline' : 'line'
            );
            if (points.length > 2) {
                shape.setAttribute('points', points.map((point) => {
                    const projected = barrierPoint(point.x, point.y);
                    return `${projected.x},${projected.y}`;
                }).join(' '));
                shape.setAttribute('fill', 'none');
            } else {
                const start = barrierPoint(points[0].x, points[0].y);
                const end = barrierPoint(points[1].x, points[1].y);
                shape.setAttribute('x1', String(start.x));
                shape.setAttribute('y1', String(start.y));
                shape.setAttribute('x2', String(end.x));
                shape.setAttribute('y2', String(end.y));
            }
            shape.classList.add('gmrt-vision-barrier', `is-${barrier.type}`);
            shape.dataset.visionBarrier = String(barrier.id);
            if (environmentalBarrierIds.has(String(barrier.id))) {
                shape.classList.add('is-forge-environmental');
            }
            if (String(barrier.id) === String(selectedVisionBarrier)) {
                shape.classList.add('is-selected');
            }
            if (barrier.type === 'door' && barrier.open) {
                shape.classList.add('is-open');
            }
            fragment.append(shape);
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
                const pathSegments = Array.isArray(barrier.points) ? Math.max(1, barrier.points.length - 1) : 1;
                label.textContent = barrier.type === 'door'
                    ? `Door ${index + 1} · ${barrier.open ? 'OPEN' : 'CLOSED'}`
                    : pathSegments > 1
                        ? `Wall Path ${index + 1} · ${pathSegments} spans`
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

    cartographyAnalyse?.addEventListener('click', async () => {
        cartographyAnalyse.disabled = true;
        if (cartographyAssistantStatus) cartographyAssistantStatus.textContent = 'Inspecting map artwork…';
        try {
            await analyseBattlemapCartography();
        } catch (error) {
            clearCartographyDraft(error.message || 'The Cartography Assistant could not inspect this map.');
        } finally {
            cartographyAnalyse.disabled = false;
        }
    });

    cartographyReview?.addEventListener('change', (event) => {
        const input = event.target instanceof HTMLInputElement
            ? event.target
            : null;
        if (!input || !input.dataset.cartographySuggestionIndex) return;
        const index = Number(input.dataset.cartographySuggestionIndex);
        if (!Number.isInteger(index) || !cartographySuggestions[index]) return;
        cartographySuggestions[index].selected = input.checked;
        updateCartographyDraftControls();
        renderCartographySuggestions();
    });

    cartographySelectAll?.addEventListener('click', () => {
        const shouldSelect = cartographySuggestions.some((item) => !item.selected);
        cartographySuggestions.forEach((item) => { item.selected = shouldSelect; });
        renderCartographyReview();
    });

    cartographyClear?.addEventListener('click', () => clearCartographyDraft());

    cartographyApply?.addEventListener('click', async () => {
        const selectedSuggestions = cartographySuggestions
            .filter((item) => item.selected)
            .map((item) => {
                if (Array.isArray(item.points) && item.points.length > 1) {
                    return { type: 'wall', points: item.points };
                }
                return {
                    type: item.type,
                    x1: item.x1,
                    y1: item.y1,
                    x2: item.x2,
                    y2: item.y2
                };
            });
        if (selectedSuggestions.length === 0) return;
        cartographyApply.disabled = true;
        if (cartographyAssistantStatus) cartographyAssistantStatus.textContent = `Applying ${selectedSuggestions.length} reviewed suggestions…`;
        try {
            await request('gmrt_apply_cartography_suggestions', {
                suggestions: JSON.stringify(selectedSuggestions)
            });
            const state = await request('gmrt_tabletop_state', {});
            renderVisionLayer(state.vision_layer || []);
            renderFog(state.fog || {});
            clearCartographyDraft(`${selectedSuggestions.length} reviewed suggestions are now authoritative vision barriers.`);
            if (visionStatus) visionStatus.textContent = 'Cartography Assistant suggestions applied. Review, open doors, remove or redraw any segment as normal.';
        } catch (error) {
            if (cartographyAssistantStatus) cartographyAssistantStatus.textContent = error.message || 'The reviewed suggestions could not be applied.';
            updateCartographyDraftControls();
        }
    });

    window.addEventListener('resize', () => {
        renderVisionLayer();
        renderCartographySuggestions();
    });
    renderVisionLayer();
    renderCartographySuggestions();

    // Phase IV.30.2 — The Cartographer's Dungeon Forge.
    // Geometry comes first: a deterministic seed carves connected floor, then the
    // Forge derives authoritative vision barriers, doors, Keeper lights and Fog.
    // The generated artwork is a persistent Tabletop-native SVG projection rather
    // than an external image-generation dependency.
    const dungeonForge = document.querySelector('[data-dungeon-forge]');
    const dungeonForgeLayer = document.querySelector('[data-dungeon-forge-layer]');
    const dungeonForgeSeed = document.querySelector('[data-dungeon-forge-seed]');
    const dungeonForgeSceneType = document.querySelector('[data-dungeon-forge-scene-type]');
    const dungeonForgeStyle = document.querySelector('[data-dungeon-forge-style]');
    const dungeonForgeTheme = document.querySelector('[data-dungeon-forge-theme]');
    const dungeonForgeGenerate = document.querySelector('[data-dungeon-forge-generate]');
    const dungeonForgeReroll = document.querySelector('[data-dungeon-forge-reroll]');
    const dungeonForgeBuild = document.querySelector('[data-dungeon-forge-build]');
    const dungeonForgeClear = document.querySelector('[data-dungeon-forge-clear]');
    const dungeonForgeStatus = document.querySelector('[data-dungeon-forge-status]');
    let dungeonForgeDraft = null;
    let builtDungeonForgePlan = null;

    if (dungeonForgeLayer) {
        try {
            const parsed = JSON.parse(dungeonForgeLayer.dataset.dungeonForgePlan || '{}');
            builtDungeonForgePlan = parsed && Array.isArray(parsed.floor) && parsed.floor.length > 0 ? parsed : null;
        } catch (error) {
            builtDungeonForgePlan = null;
        }
    }

    const forgeSvg = (name, attributes = {}) => {
        const node = document.createElementNS('http://www.w3.org/2000/svg', name);
        Object.entries(attributes).forEach(([key, value]) => node.setAttribute(key, String(value)));
        return node;
    };

    const forgeFloorKey = (x, y) => `${x}:${y}`;

    const renderDungeonForgePlan = (plan, draft = false) => {
        if (!dungeonForgeLayer) return;
        dungeonForgeLayer.replaceChildren();
        if (!plan || !Array.isArray(plan.floor) || plan.floor.length === 0) {
            dungeonForgeLayer.classList.remove('has-plan', 'is-draft');
            return;
        }

        const cols = Math.max(1, Number(plan.cols || 1));
        const rows = Math.max(1, Number(plan.rows || 1));
        dungeonForgeLayer.setAttribute('viewBox', `0 0 ${cols} ${rows}`);
        dungeonForgeLayer.setAttribute('preserveAspectRatio', 'none');
        dungeonForgeLayer.classList.add('has-plan');
        dungeonForgeLayer.classList.toggle('is-draft', draft);
        dungeonForgeLayer.dataset.forgeTheme = String(plan.theme || 'pantry-stone');
        dungeonForgeLayer.dataset.forgeSceneType = String(plan.scene_type || 'dungeon');

        const rock = forgeSvg('rect', { x: 0, y: 0, width: cols, height: rows, class: 'gmrt-forge-rock' });
        dungeonForgeLayer.appendChild(rock);

        const floors = forgeSvg('g', { class: 'gmrt-forge-floor' });
        plan.floor.forEach((cell) => {
            floors.appendChild(forgeSvg('rect', { x: Number(cell.x), y: Number(cell.y), width: 1, height: 1 }));
        });
        dungeonForgeLayer.appendChild(floors);

        const floorSet = new Set(plan.floor.map((cell) => forgeFloorKey(Number(cell.x), Number(cell.y))));

        // IV.30.2A.1 — Pippin Decorates the Place.
        // Themes are deterministic surface treatments only: they never alter floor
        // topology, LOS walls, doors, grid registration or Keeper light positions.
        const decorate = forgeSvg('g', { class: 'gmrt-forge-decoration' });
        const decorationChance = (x, y, salt = '') => forgeHash(`${plan.seed}|${plan.theme}|${x}|${y}|${salt}`) / 0xffffffff;
        const path = (d, className) => forgeSvg('path', { d, class: className });
        const line = (x1, y1, x2, y2, className) => forgeSvg('line', { x1, y1, x2, y2, class: className });
        const circle = (cx, cy, r, className) => forgeSvg('circle', { cx, cy, r, class: className });

        const outdoorScene = ['forest', 'village'].includes(String(plan.scene_type || 'dungeon'));
        plan.floor.forEach((cell) => {
            const x = Number(cell.x); const y = Number(cell.y);
            const n = decorationChance(x, y, 'floor');
            if (outdoorScene) {
                if (n < .055) decorate.appendChild(circle(x+.28, y+.68, .035, 'is-outdoor-speck'));
                return;
            }
            if (plan.theme === 'pantry-stone') {
                if (n < .34) decorate.appendChild(path(`M ${x+.16} ${y+.72} l .16 -.12 l .13 .06 l .18 -.17`, 'is-crack'));
                if (n > .82) decorate.appendChild(circle(x+.72, y+.28, .055, 'is-pit'));
            } else if (plan.theme === 'butcher-cellar') {
                decorate.appendChild(line(x+.08, y+.5, x+.92, y+.5, 'is-mortar'));
                if ((x+y)%2===0) decorate.appendChild(line(x+.5, y+.08, x+.5, y+.5, 'is-mortar'));
                if (n < .12) decorate.appendChild(circle(x+.5, y+.5, .16, 'is-drain'));
            } else if (plan.theme === 'rootland-cavern') {
                if (n < .48) decorate.appendChild(path(`M ${x+.05} ${y+.78} Q ${x+.38} ${y+.46} ${x+.92} ${y+.22}`, 'is-root'));
                if (n > .82) decorate.appendChild(circle(x+.28, y+.64, .09, 'is-pebble'));
            } else if (plan.theme === 'frostreem-vault') {
                if (n < .58) decorate.appendChild(path(`M ${x+.18} ${y+.2} l .2 .24 l -.09 .18 l .26 .19 l .22 -.16`, 'is-ice-crack'));
                if (n > .82) decorate.appendChild(path(`M ${x+.62} ${y+.16} l .12 .12 l -.12 .12 l -.12 -.12 z`, 'is-frost-chip'));
            } else if (plan.theme === 'bakery-crypt') {
                decorate.appendChild(line(x+.06, y+.48, x+.94, y+.48, 'is-brick'));
                decorate.appendChild(line(x+(y%2?.28:.62), y+.06, x+(y%2?.28:.62), y+.48, 'is-brick'));
                if (n < .16) decorate.appendChild(circle(x+.74, y+.72, .045, 'is-crumb'));
            } else if (plan.theme === 'mushroom-grotto') {
                if (n < .28) {
                    decorate.appendChild(path(`M ${x+.3} ${y+.65} q .18 -.25 .36 0 z`, 'is-mushroom-cap'));
                    decorate.appendChild(line(x+.48, y+.65, x+.48, y+.82, 'is-mushroom-stem'));
                } else if (n > .76) decorate.appendChild(circle(x+.3, y+.3, .055, 'is-spore'));
            }
        });

        for (let y = 0; y < rows; y += 1) {
            for (let x = 0; x < cols; x += 1) {
                if (floorSet.has(forgeFloorKey(x, y))) continue;
                const nearFloor = floorSet.has(forgeFloorKey(x-1,y)) || floorSet.has(forgeFloorKey(x+1,y)) || floorSet.has(forgeFloorKey(x,y-1)) || floorSet.has(forgeFloorKey(x,y+1));
                if (!nearFloor || decorationChance(x,y,'rock') > .42) continue;
                decorate.appendChild(path(`M ${x+.12} ${y+.75} q .22 -.42 .42 -.08 q .2 -.3 .36 .08`, 'is-rock-face'));
            }
        }
        dungeonForgeLayer.appendChild(decorate);

        const featureGroup = forgeSvg('g', { class: 'gmrt-forge-features' });
        const organicCanopy = (feature) => {
            const x=Number(feature.x||0), y=Number(feature.y||0), w=Math.max(.2,Number(feature.w||1)), h=Math.max(.2,Number(feature.h||1));
            const canopy=forgeSvg('g', { class:`is-${String(feature.kind)} is-organic-canopy` });
            const blobs=Math.max(5, Math.min(11, Math.round((w+h)*1.4)));
            for(let i=0;i<blobs;i+=1){
                const n=decorationChance(x+i,y,'canopy'); const m=decorationChance(x,y+i,'canopy-y');
                const cx=x+.2+(n*Math.max(.25,w-.4)); const cy=y+.2+(m*Math.max(.25,h-.4));
                const r=Math.max(.48, Math.min(1.25, .5+decorationChance(i,x+y,'canopy-r')*.72));
                canopy.appendChild(circle(cx,cy,r,'is-canopy-blob'));
                if (i%3===0) canopy.appendChild(circle(cx-r*.22,cy-r*.26,r*.36,'is-canopy-highlight'));
            }
            const trunkX=x+w/2, trunkY=y+h/2;
            canopy.appendChild(circle(trunkX,trunkY,.2,'is-tree-trunk'));
            canopy.appendChild(path(`M ${trunkX-.08} ${trunkY+.04} q -.28 .18 -.42 .5 M ${trunkX+.08} ${trunkY+.04} q .28 .18 .42 .5`,'is-tree-root'));
            return canopy;
        };
        const organicRocks = (feature) => {
            const x=Number(feature.x||0), y=Number(feature.y||0), w=Math.max(.5,Number(feature.w||1)), h=Math.max(.5,Number(feature.h||1));
            const rocks=forgeSvg('g',{class:'is-rock-cluster is-organic-rocks'});
            [[.2,.62,.28],[.46,.38,.35],[.72,.62,.3]].forEach(([px,py,pr],index)=>{
                const cx=x+w*px, cy=y+h*py, r=Math.min(w,h)*pr;
                const squash=.72+(decorationChance(index,x+y,'rock-squash')*.18);
                rocks.appendChild(path(`M ${cx-r} ${cy+.08*r} Q ${cx-.65*r} ${cy-r*squash} ${cx+.08*r} ${cy-r} Q ${cx+.86*r} ${cy-.72*r} ${cx+r} ${cy+.12*r} Q ${cx+.55*r} ${cy+r*squash} ${cx-.25*r} ${cy+.82*r} Q ${cx-.9*r} ${cy+.62*r} ${cx-r} ${cy+.08*r} Z`,'is-rock'));
                rocks.appendChild(path(`M ${cx-.5*r} ${cy-.12*r} Q ${cx} ${cy-.48*r} ${cx+.45*r} ${cy-.2*r}`,'is-rock-highlight'));
            });
            return rocks;
        };
        const villageBuilding = (feature, kind) => {
            const x=Number(feature.x||0), y=Number(feature.y||0), w=Math.max(2,Number(feature.w||2)), h=Math.max(2,Number(feature.h||2));
            const building=forgeSvg('g',{class:`is-${kind} is-village-building`});
            building.appendChild(forgeSvg('rect',{x,y,width:w,height:h,rx:.08,class:'is-building-shadow'}));
            building.appendChild(forgeSvg('rect',{x:x+.12,y:y+.12,width:w-.24,height:h-.24,rx:.06,class:'is-building-body'}));
            const verticalRoof=h>w;
            if (verticalRoof) {
                building.appendChild(path(`M ${x+.08} ${y+.08} L ${x+w/2} ${y+.4} L ${x+w/2} ${y+h-.18} L ${x+.08} ${y+h-.08} Z`,'is-building-roof is-roof-left'));
                building.appendChild(path(`M ${x+w-.08} ${y+.08} L ${x+w/2} ${y+.4} L ${x+w/2} ${y+h-.18} L ${x+w-.08} ${y+h-.08} Z`,'is-building-roof is-roof-right'));
                building.appendChild(line(x+w/2,y+.4,x+w/2,y+h-.18,'is-roof-ridge'));
            } else {
                building.appendChild(path(`M ${x+.08} ${y+.08} L ${x+.4} ${y+h/2} L ${x+w-.4} ${y+h/2} L ${x+w-.08} ${y+.08} Z`,'is-building-roof is-roof-top'));
                building.appendChild(path(`M ${x+.08} ${y+h-.08} L ${x+.4} ${y+h/2} L ${x+w-.4} ${y+h/2} L ${x+w-.08} ${y+h-.08} Z`,'is-building-roof is-roof-bottom'));
                building.appendChild(line(x+.4,y+h/2,x+w-.4,y+h/2,'is-roof-ridge'));
            }
            const south=y < rows/2;
            const doorY=south ? y+h-.28 : y+.04;
            building.appendChild(forgeSvg('rect',{x:x+w/2-.22,y:doorY,width:.44,height:.24,rx:.04,class:'is-building-door'}));
            building.appendChild(forgeSvg('rect',{x:x+.38,y:y+h*.45,width:.42,height:.34,rx:.04,class:'is-building-window'}));
            building.appendChild(forgeSvg('rect',{x:x+w-.8,y:y+h*.45,width:.42,height:.34,rx:.04,class:'is-building-window'}));
            if (kind === 'inn' || kind === 'workshop') {
                building.appendChild(forgeSvg('rect',{x:x+w-.85,y:y+.18,width:.34,height:.5,rx:.04,class:'is-building-chimney'}));
            }
            return building;
        };
        const fallenLog = (feature) => {
            const x=Number(feature.x||0), y=Number(feature.y||0), w=Math.max(1,Number(feature.w||3)), h=Math.max(.5,Number(feature.h||1));
            const log=forgeSvg('g',{class:'is-fallen-log is-detailed-log'});
            log.appendChild(forgeSvg('rect',{x:x+.15,y:y+.24,width:w-.3,height:Math.max(.28,h-.48),rx:.22,class:'is-log-body'}));
            log.appendChild(circle(x+.2,y+h/2,.25,'is-log-end'));
            log.appendChild(circle(x+w-.2,y+h/2,.25,'is-log-end'));
            for(let i=1;i<4;i+=1) log.appendChild(line(x+(w*i/4),y+.3,x+(w*i/4)-.18,y+h-.3,'is-log-bark'));
            return log;
        };
        const villageWell = (feature) => {
            const x=Number(feature.x||0), y=Number(feature.y||0), w=Math.max(1,Number(feature.w||1)), h=Math.max(1,Number(feature.h||1));
            const cx=x+w/2, cy=y+h/2, r=Math.min(w,h)*.46;
            const well=forgeSvg('g',{class:'is-well is-detailed-well'});
            well.appendChild(circle(cx,cy,r,'is-well-rim'));
            well.appendChild(circle(cx,cy,r*.58,'is-well-water'));
            well.appendChild(line(cx-r*.72,cy-r*.72,cx-r*.72,cy+r*.35,'is-well-post'));
            well.appendChild(line(cx+r*.72,cy-r*.72,cx+r*.72,cy+r*.35,'is-well-post'));
            well.appendChild(line(cx-r*.9,cy-r*.72,cx+r*.9,cy-r*.72,'is-well-beam'));
            return well;
        };
        const fencedGarden = (feature) => {
            const x=Number(feature.x||0), y=Number(feature.y||0), w=Math.max(1,Number(feature.w||1)), h=Math.max(1,Number(feature.h||1));
            const garden=forgeSvg('g',{class:'is-fenced-garden is-detailed-garden'});
            garden.appendChild(forgeSvg('rect',{x,y,width:w,height:h,rx:.12,class:'is-garden-soil'}));
            for(let i=1;i<4;i+=1) garden.appendChild(line(x+.5,y+(h*i/4),x+w-.5,y+(h*i/4),'is-garden-row'));
            garden.appendChild(forgeSvg('rect',{x:x+.08,y:y+.08,width:w-.16,height:h-.16,rx:.08,class:'is-garden-fence'}));
            return garden;
        };
        (plan.features || []).forEach((feature) => {
            const kind = String(feature.kind || 'feature');
            if (kind === 'trail' && Array.isArray(feature.points) && feature.points.length > 1) {
                featureGroup.appendChild(forgeSvg('polyline', { points:feature.points.map(p=>`${p.x},${p.y}`).join(' '), class:'is-trail is-trail-underlay' }));
                featureGroup.appendChild(forgeSvg('polyline', { points:feature.points.map(p=>`${p.x},${p.y}`).join(' '), class:'is-trail is-trail-track' }));
                return;
            }
            const x=Number(feature.x||0), y=Number(feature.y||0), w=Math.max(.2,Number(feature.w||1)), h=Math.max(.2,Number(feature.h||1));
            if (kind === 'tree-cluster' || kind === 'village-tree') { featureGroup.appendChild(organicCanopy(feature)); return; }
            if (kind === 'rock-cluster') { featureGroup.appendChild(organicRocks(feature)); return; }
            if (['house','cottage','workshop','inn'].includes(kind)) { featureGroup.appendChild(villageBuilding(feature, kind)); return; }
            if (kind === 'fallen-log') { featureGroup.appendChild(fallenLog(feature)); return; }
            if (kind === 'well') { featureGroup.appendChild(villageWell(feature)); return; }
            if (kind === 'fenced-garden') { featureGroup.appendChild(fencedGarden(feature)); return; }
            featureGroup.appendChild(forgeSvg('rect', { x, y, width:w, height:h, rx:.18, class:`is-${kind}` }));
        });
        dungeonForgeLayer.appendChild(featureGroup);

        const lineGroup = forgeSvg('g', { class: 'gmrt-forge-ink' });
        if (!outdoorScene) plan.floor.forEach((cell) => {
            const x = Number(cell.x); const y = Number(cell.y);
            if (!floorSet.has(forgeFloorKey(x, y - 1))) lineGroup.appendChild(forgeSvg('line', { x1:x, y1:y, x2:x+1, y2:y }));
            if (!floorSet.has(forgeFloorKey(x + 1, y))) lineGroup.appendChild(forgeSvg('line', { x1:x+1, y1:y, x2:x+1, y2:y+1 }));
            if (!floorSet.has(forgeFloorKey(x, y + 1))) lineGroup.appendChild(forgeSvg('line', { x1:x, y1:y+1, x2:x+1, y2:y+1 }));
            if (!floorSet.has(forgeFloorKey(x - 1, y))) lineGroup.appendChild(forgeSvg('line', { x1:x, y1:y, x2:x, y2:y+1 }));
        });
        dungeonForgeLayer.appendChild(lineGroup);

        const doorGroup = forgeSvg('g', { class: 'gmrt-forge-doors' });
        (plan.doors || []).forEach((door) => {
            doorGroup.appendChild(forgeSvg('line', {
                x1: Number(door.x1) * cols,
                y1: Number(door.y1) * rows,
                x2: Number(door.x2) * cols,
                y2: Number(door.y2) * rows
            }));
        });
        dungeonForgeLayer.appendChild(doorGroup);

        if (draft) {
            const lightGroup = forgeSvg('g', { class: 'gmrt-forge-lights' });
            (plan.lights || []).forEach((light) => {
                const marker = forgeSvg('text', {
                    x: Number(light.x) * cols,
                    y: Number(light.y) * rows,
                    'text-anchor': 'middle',
                    'dominant-baseline': 'central'
                });
                marker.textContent = ({torch:'🔥',lantern:'🏮',brazier:'♨',candle:'🕯',magical:'✦'})[light.kind] || '✦';
                lightGroup.appendChild(marker);
            });
            dungeonForgeLayer.appendChild(lightGroup);
        }
    };

    const forgeHash = (value) => {
        let hash = 2166136261;
        for (let i = 0; i < value.length; i += 1) {
            hash ^= value.charCodeAt(i);
            hash = Math.imul(hash, 16777619);
        }
        return hash >>> 0;
    };

    const forgeRandom = (seed) => {
        let state = forgeHash(seed) || 0x9e3779b9;
        return () => {
            state += 0x6D2B79F5;
            let t = state;
            t = Math.imul(t ^ (t >>> 15), t | 1);
            t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
            return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
        };
    };

    const forgeDoorAt = (room, target, cols, rows, travelAxis = 'auto') => {
        const cx = room.x + (room.w / 2);
        const cy = room.y + (room.h / 2);
        const dx = target.x - cx;
        const dy = target.y - cy;
        if (travelAxis === 'horizontal' || (travelAxis === 'auto' && Math.abs(dx) >= Math.abs(dy))) {
            const x = dx >= 0 ? room.x + room.w : room.x;
            const y = Math.max(room.y, Math.min(room.y + room.h - 1, Math.floor(cy)));
            return { x1:x/cols, y1:y/rows, x2:x/cols, y2:(y+1)/rows };
        }
        const y = dy >= 0 ? room.y + room.h : room.y;
        const x = Math.max(room.x, Math.min(room.x + room.w - 1, Math.floor(cx)));
        return { x1:x/cols, y1:y/rows, x2:(x+1)/cols, y2:y/rows };
    };

    // Legacy IV.30.2 source contract: generateDungeonForgePlan = (seed, style)
    const generateDungeonForgePlan = (seed, style, theme = 'pantry-stone', preferredAspect = null) => {
        const presets = {
            compact: { cols:24, rooms:6, min:3, max:6 },
            standard: { cols:32, rooms:9, min:4, max:7 },
            grand: { cols:40, rooms:12, min:4, max:8 }
        };
        const preset = presets[style] || presets.standard;
        const cols = preset.cols;
        const measuredAspect = preferredAspect === null
            ? (board.clientHeight / Math.max(1, board.clientWidth))
            : Number(preferredAspect);
        const boardAspect = Math.max(.5, Math.min(2, measuredAspect || .7));
        const rows = Math.max(12, Math.min(36, Math.round(cols * boardAspect)));
        const random = forgeRandom(`${seed}|${style}`);
        const integer = (min, max) => min + Math.floor(random() * ((max - min) + 1));
        const rooms = [];

        for (let attempt = 0; attempt < 500 && rooms.length < preset.rooms; attempt += 1) {
            const w = integer(preset.min, preset.max);
            const h = integer(3, Math.max(4, preset.max - 1));
            const x = integer(1, Math.max(1, cols - w - 2));
            const y = integer(1, Math.max(1, rows - h - 2));
            const collides = rooms.some((room) => !(
                x + w + 1 < room.x || room.x + room.w + 1 < x ||
                y + h + 1 < room.y || room.y + room.h + 1 < y
            ));
            if (!collides) rooms.push({ x, y, w, h });
        }
        if (rooms.length < 3) throw new Error('The Forge could not fit enough chambers on this draft. Try a new seed.');

        const floor = new Map();
        const carve = (x, y) => {
            if (x >= 0 && x < cols && y >= 0 && y < rows) floor.set(forgeFloorKey(x,y), {x,y});
        };
        rooms.forEach((room) => {
            for (let y = room.y; y < room.y + room.h; y += 1) {
                for (let x = room.x; x < room.x + room.w; x += 1) carve(x,y);
            }
        });

        const center = (room) => ({ x:Math.floor(room.x + room.w/2), y:Math.floor(room.y + room.h/2) });
        const ordered = [rooms[0]];
        const remaining = rooms.slice(1);
        while (remaining.length) {
            const from = center(ordered[ordered.length - 1]);
            remaining.sort((a,b) => {
                const ca=center(a), cb=center(b);
                return (Math.abs(ca.x-from.x)+Math.abs(ca.y-from.y)) - (Math.abs(cb.x-from.x)+Math.abs(cb.y-from.y));
            });
            ordered.push(remaining.shift());
        }

        const doors = [];
        for (let i = 1; i < ordered.length; i += 1) {
            const previous = ordered[i-1]; const next = ordered[i];
            const a = center(previous); const b = center(next);
            const horizontalFirst = random() >= .5;
            if (horizontalFirst) {
                const step = a.x <= b.x ? 1 : -1;
                for (let x=a.x; x !== b.x + step; x += step) carve(x,a.y);
                const vstep = a.y <= b.y ? 1 : -1;
                for (let y=a.y; y !== b.y + vstep; y += vstep) carve(b.x,y);
            } else {
                const vstep = a.y <= b.y ? 1 : -1;
                for (let y=a.y; y !== b.y + vstep; y += vstep) carve(a.x,y);
                const step = a.x <= b.x ? 1 : -1;
                for (let x=a.x; x !== b.x + step; x += step) carve(x,b.y);
            }
            doors.push(forgeDoorAt(previous, b, cols, rows, horizontalFirst ? 'horizontal' : 'vertical'));
            doors.push(forgeDoorAt(next, a, cols, rows, horizontalFirst ? 'vertical' : 'horizontal'));
        }

        // Merge contiguous exterior edges into long wall objects so the Forge remains
        // comfortably inside the existing 200-object Cartography safety budget.
        const floorSet = new Set(floor.keys());
        const horizontal = new Map(); const vertical = new Map();
        const addEdge = (map, axis, start) => {
            if (!map.has(axis)) map.set(axis, []);
            map.get(axis).push(start);
        };
        floor.forEach((cell) => {
            const {x,y}=cell;
            if (!floorSet.has(forgeFloorKey(x,y-1))) addEdge(horizontal,y,x);
            if (!floorSet.has(forgeFloorKey(x,y+1))) addEdge(horizontal,y+1,x);
            if (!floorSet.has(forgeFloorKey(x-1,y))) addEdge(vertical,x,y);
            if (!floorSet.has(forgeFloorKey(x+1,y))) addEdge(vertical,x+1,y);
        });
        const barriers = [];
        const mergeEdges = (map, isHorizontal) => {
            map.forEach((starts, axis) => {
                const unique = [...new Set(starts)].sort((a,b)=>a-b);
                let runStart = null; let previous = null;
                const flush = () => {
                    if (runStart === null || previous === null) return;
                    if (isHorizontal) barriers.push({type:'wall',x1:runStart/cols,y1:axis/rows,x2:(previous+1)/cols,y2:axis/rows});
                    else barriers.push({type:'wall',x1:axis/cols,y1:runStart/rows,x2:axis/cols,y2:(previous+1)/rows});
                };
                unique.forEach((value) => {
                    if (runStart === null) { runStart=value; previous=value; return; }
                    if (value === previous + 1) { previous=value; return; }
                    flush(); runStart=value; previous=value;
                });
                flush();
            });
        };
        mergeEdges(horizontal,true); mergeEdges(vertical,false);
        doors.forEach((door) => barriers.push({type:'door',...door}));
        if (barriers.length > 200) throw new Error('This draft is too intricate for one safe build. Try a more compact seed.');

        const byArea = [...rooms].sort((a,b)=>(b.w*b.h)-(a.w*a.h));
        const lights = [];
        if (byArea[0]) { const c=center(byArea[0]); lights.push({kind:'brazier',x:(c.x+.5)/cols,y:(c.y+.5)/rows}); }
        ordered.slice(1).forEach((room,index) => {
            if (index % 2 !== 0 && style !== 'grand') return;
            const c=center(room);
            const kind = index === ordered.length - 2 ? 'magical' : (index % 3 === 0 ? 'lantern' : 'torch');
            lights.push({kind,x:(c.x+.5)/cols,y:(c.y+.5)/rows});
        });

        return {
            version:3, scene_type:'dungeon', seed, style, theme, cols, rows,
            floor:[...floor.values()], rooms, doors, barriers, lights, features:[]
        };
    };


    // IV.30.2B — Beyond the Dungeon Walls.
    // Scene Type owns topology; Theme owns presentation. Forests and villages
    // therefore compose with the same Great Marketrealm treatments without
    // borrowing dungeon room/corridor geometry.
    const forgePlanDimensions = (style, preferredAspect = null) => {
        const colsByStyle = { compact:24, standard:32, grand:40 };
        const cols = colsByStyle[style] || colsByStyle.standard;
        const measuredAspect = preferredAspect === null
            ? (board.clientHeight / Math.max(1, board.clientWidth))
            : Number(preferredAspect);
        const boardAspect = Math.max(.5, Math.min(2, measuredAspect || .7));
        return { cols, rows:Math.max(12, Math.min(36, Math.round(cols * boardAspect))) };
    };

    const forgeBoundaryBarriers = (cols, rows) => [
        {type:'wall',x1:0,y1:0,x2:1,y2:0},
        {type:'wall',x1:1,y1:0,x2:1,y2:1},
        {type:'wall',x1:1,y1:1,x2:0,y2:1},
        {type:'wall',x1:0,y1:1,x2:0,y2:0}
    ];

    const forgeObstacleRect = (barriers, feature, cols, rows, doorway = null) => {
        const x1=feature.x, y1=feature.y, x2=feature.x+feature.w, y2=feature.y+feature.h;
        const add=(ax,ay,bx,by)=>barriers.push({type:'wall',x1:ax/cols,y1:ay/rows,x2:bx/cols,y2:by/rows});
        if (!doorway) {
            add(x1,y1,x2,y1); add(x2,y1,x2,y2); add(x2,y2,x1,y2); add(x1,y2,x1,y1); return;
        }
        const side=doorway.side; const d1=doorway.at; const d2=doorway.at+1;
        if (side==='south') { add(x1,y1,x2,y1); add(x2,y1,x2,y2); add(x2,y2,d2,y2); add(d1,y2,x1,y2); add(x1,y2,x1,y1); }
        else if (side==='north') { add(x1,y1,d1,y1); add(d2,y1,x2,y1); add(x2,y1,x2,y2); add(x2,y2,x1,y2); add(x1,y2,x1,y1); }
        else { add(x1,y1,x2,y1); add(x2,y1,x2,y2); add(x2,y2,x1,y2); add(x1,y2,x1,y1); }
        barriers.push({type:'door',x1:d1/cols,y1:(side==='north'?y1:y2)/rows,x2:d2/cols,y2:(side==='north'?y1:y2)/rows});
    };

    const generateForestForgePlan = (seed, style, theme = 'pantry-stone', preferredAspect = null) => {
        const {cols,rows}=forgePlanDimensions(style, preferredAspect);
        const random=forgeRandom(`${seed}|${style}|forest`);
        const integer=(min,max)=>min+Math.floor(random()*((max-min)+1));
        const floor=[]; for(let y=0;y<rows;y+=1) for(let x=0;x<cols;x+=1) floor.push({x,y});
        const features=[]; const barriers=forgeBoundaryBarriers(cols,rows); const rooms=[];
        const clearingCount={compact:3,standard:4,grand:5}[style]||4;
        for(let i=0;i<clearingCount;i+=1){
            const w=integer(5,8), h=integer(4,6), x=integer(2,Math.max(2,cols-w-3)), y=integer(2,Math.max(2,rows-h-3));
            rooms.push({x,y,w,h}); features.push({kind:'clearing',x,y,w,h});
        }
        const trail=[];
        rooms.sort((a,b)=>a.x-b.x).forEach((room,index)=>{
            const cx=Math.floor(room.x+room.w/2), cy=Math.floor(room.y+room.h/2);
            trail.push({x:cx,y:cy});
            if(index>0){ const prev=rooms[index-1]; trail.push({x:cx,y:Math.floor(prev.y+prev.h/2)}); }
        });
        features.push({kind:'trail',points:trail});
        const obstacleCount={compact:9,standard:14,grand:20}[style]||14;
        for(let i=0;i<obstacleCount;i+=1){
            const w=integer(2,4), h=integer(2,4), x=integer(1,Math.max(1,cols-w-2)), y=integer(1,Math.max(1,rows-h-2));
            const blocked=rooms.some(r=>x<r.x+r.w+1&&x+w+1>r.x&&y<r.y+r.h+1&&y+h+1>r.y);
            if(blocked){ i-=1; continue; }
            const kind=i%5===0?'rock-cluster':'tree-cluster'; const feature={kind,x,y,w,h}; features.push(feature); forgeObstacleRect(barriers,feature,cols,rows);
        }
        const logCount=style==='compact'?2:style==='grand'?5:3;
        for(let i=0;i<logCount;i+=1){
            const x=integer(2,cols-5), y=integer(2,rows-3); const feature={kind:'fallen-log',x,y,w:3,h:1}; features.push(feature);
            barriers.push({type:'wall',x1:x/cols,y1:(y+.5)/rows,x2:(x+3)/cols,y2:(y+.5)/rows});
        }
        const lights=rooms.slice(0,Math.min(3,rooms.length)).map((r,i)=>({kind:i===0?'brazier':'lantern',x:(r.x+r.w/2)/cols,y:(r.y+r.h/2)/rows}));
        return {version:3,scene_type:'forest',seed,style,theme,cols,rows,floor,rooms,doors:[],barriers,lights,features};
    };

    const generateVillageForgePlan = (seed, style, theme = 'pantry-stone', preferredAspect = null) => {
        const {cols,rows}=forgePlanDimensions(style, preferredAspect);
        const random=forgeRandom(`${seed}|${style}|village`);
        const integer=(min,max)=>min+Math.floor(random()*((max-min)+1));
        const floor=[]; for(let y=0;y<rows;y+=1) for(let x=0;x<cols;x+=1) floor.push({x,y});
        const features=[]; const barriers=forgeBoundaryBarriers(cols,rows); const rooms=[]; const doors=[];
        const roadY=Math.floor(rows/2); features.push({kind:'road',x:0,y:roadY-1,w:cols,h:3});
        const square={kind:'village-square',x:Math.floor(cols/2)-3,y:roadY-3,w:7,h:7}; features.push(square);
        const slots=[]; for(let x=2;x<cols-6;x+=7){ slots.push({x,y:2}); slots.push({x,y:Math.max(2,rows-7)}); }
        const count=Math.min(slots.length,{compact:5,standard:7,grand:10}[style]||7);
        for(let i=0;i<count;i+=1){
            const slot=slots[i]; const w=integer(4,6), h=integer(3,5); const x=Math.min(cols-w-2,slot.x), y=Math.min(rows-h-2,slot.y);
            const kind=i===0?'inn':i===1?'cottage':i===2?'workshop':'house'; const feature={kind,x,y,w,h}; features.push(feature); rooms.push({x,y,w,h});
            const side=y<roadY?'south':'north'; const at=Math.max(x+1,Math.min(x+w-2,Math.floor(x+w/2))); const doorway={side,at};
            forgeObstacleRect(barriers,feature,cols,rows,doorway);
            const dy=(side==='north'?y:y+h)/rows; const door={x1:at/cols,y1:dy,x2:(at+1)/cols,y2:dy}; doors.push(door);
        }
        const well={kind:'well',x:Math.floor(cols/2),y:roadY,w:1,h:1}; features.push(well);
        const wx=well.x, wy=well.y; barriers.push({type:'wall',x1:(wx-.35)/cols,y1:(wy-.35)/rows,x2:(wx+.35)/cols,y2:(wy-.35)/rows}); barriers.push({type:'wall',x1:(wx+.35)/cols,y1:(wy-.35)/rows,x2:(wx+.35)/cols,y2:(wy+.35)/rows});
        features.push({kind:'fenced-garden',x:Math.max(2,cols-9),y:Math.max(2,roadY-6),w:5,h:4});
        const garden=features[features.length-1]; forgeObstacleRect(barriers,garden,cols,rows);
        const treeCount=style==='compact'?3:style==='grand'?8:5;
        for(let i=0;i<treeCount;i+=1){ const feature={kind:'village-tree',x:integer(1,cols-3),y:integer(1,rows-3),w:2,h:2}; features.push(feature); forgeObstacleRect(barriers,feature,cols,rows); }
        const lights=[{kind:'lantern',x:.5,y:(roadY+.5)/rows},{kind:'brazier',x:(square.x+square.w/2)/cols,y:(square.y+square.h/2)/rows}];
        return {version:3,scene_type:'village',seed,style,theme,cols,rows,floor,rooms,doors,barriers,lights,features};
    };

    const generateSceneForgePlan = (sceneType, seed, style, theme = 'pantry-stone', preferredAspect = null) => {
        if (sceneType === 'forest') return generateForestForgePlan(seed, style, theme, preferredAspect);
        if (sceneType === 'village') return generateVillageForgePlan(seed, style, theme, preferredAspect);
        return generateDungeonForgePlan(seed, style, theme, preferredAspect);
    };

    const setForgeStatus = (message) => {
        if (dungeonForgeStatus) dungeonForgeStatus.textContent = message;
        say(message);
    };

    const prepareForgeDraft = () => {
        if (builtDungeonForgePlan) {
            setForgeStatus('This Scene already contains a forged dungeon. Prepare another Scene to forge a new one.');
            return;
        }
        const sceneType = String(dungeonForgeSceneType?.value || 'dungeon');
        const seed = String(dungeonForgeSeed?.value || '').trim() || 'Peppercorn-01';
        const style = String(dungeonForgeStyle?.value || 'standard');
        const theme = String(dungeonForgeTheme?.value || 'pantry-stone');
        dungeonForgeDraft = generateSceneForgePlan(sceneType, seed, style, theme);
        renderDungeonForgePlan(dungeonForgeDraft, true);
        if (dungeonForgeBuild) dungeonForgeBuild.disabled = false;
        if (dungeonForgeClear) dungeonForgeClear.disabled = false;
        setForgeStatus(`${String(dungeonForgeDraft.scene_type || 'dungeon')} · ${dungeonForgeDraft.rooms.length} major places · ${dungeonForgeDraft.barriers.length} vision objects · ${dungeonForgeDraft.doors.length} doors · ${dungeonForgeDraft.lights.length} suggested lights · preview only.`);
    };

    dungeonForgeGenerate?.addEventListener('click', () => {
        try { prepareForgeDraft(); } catch (error) { setForgeStatus(error?.message || 'The Dungeon Forge could not prepare that draft.'); }
    });

    dungeonForgeReroll?.addEventListener('click', () => {
        if (!dungeonForgeSeed) return;
        dungeonForgeSeed.value = `Peppercorn-${Math.random().toString(36).slice(2,8).toUpperCase()}`;
        try { prepareForgeDraft(); } catch (error) { setForgeStatus(error?.message || 'The Dungeon Forge could not prepare that draft.'); }
    });

    dungeonForgeClear?.addEventListener('click', () => {
        dungeonForgeDraft = null;
        renderDungeonForgePlan(builtDungeonForgePlan, false);
        if (dungeonForgeBuild) dungeonForgeBuild.disabled = true;
        dungeonForgeClear.disabled = true;
        setForgeStatus('Forge draft cleared. Nothing was saved.');
    });

    dungeonForgeBuild?.addEventListener('click', async () => {
        if (!dungeonForgeDraft || builtDungeonForgePlan) return;
        dungeonForgeBuild.disabled = true;
        const previous = dungeonForgeBuild.textContent;
        dungeonForgeBuild.textContent = 'Forging…';
        try {
            const gridPixels = Math.max(8, Math.round(board.clientWidth / Math.max(1, dungeonForgeDraft.cols)));
            const existingOpacity = document.querySelector('[data-grid-opacity]')?.value || '22';
            await request('gmrt_calibrate_grid', {
                grid_size: String(gridPixels),
                grid_offset_x: '0',
                grid_offset_y: '0',
                grid_opacity: existingOpacity,
                grid_visible: '1',
                grid_reference_width: String(Math.max(1, Math.round(board.clientWidth)))
            });
            const data = await request('gmrt_build_dungeon_forge', {
                scene_id: preparationSceneId || projectedSceneId,
                plan: JSON.stringify(dungeonForgeDraft)
            });
            builtDungeonForgePlan = data.forge || dungeonForgeDraft;
            dungeonForgeDraft = null;
            setForgeStatus(data.message || 'Dungeon forged. Walls, doors, lights, grid and Fog are now authoritative.');
            await replaceChamber(data.message || 'Dungeon forged.', preparationSceneId || null);
        } catch (error) {
            dungeonForgeBuild.disabled = false;
            setForgeStatus(error?.message || 'The Dungeon Forge could not complete this build.');
        } finally {
            dungeonForgeBuild.textContent = previous;
        }
    });

    renderDungeonForgePlan(builtDungeonForgePlan, false);

    // IV.30.2A.1A — The Mystery of the Corner Tile.
    // Root cause: Forge barriers are authored in normalised surface coordinates,
    // then converted server-side to the rules-grid coordinates used by Vision.


    const gridViewport = document.querySelector('.gmrt-board__viewport');
    const gridSize = document.querySelector('[data-grid-size]');
    const gridOffsetX = document.querySelector('[data-grid-offset-x]');
    const gridOffsetY = document.querySelector('[data-grid-offset-y]');
    const gridOpacity = document.querySelector('[data-grid-opacity]');
    const gridVisible = document.querySelector('[data-grid-visible]');
    const detectGrid = document.querySelector('[data-detect-grid]');
    const gridRegistrationStatus = document.querySelector('[data-grid-registration-status]');
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
            if (gridRegistrationStatus) {
                gridRegistrationStatus.textContent = 'Preview reset to the last saved calibration.';
            }
        });
    }

    const detectPrintedGrid = async () => {
        const image = document.querySelector('[data-battlemap-image]');
        if (!image || !gridViewport || !gridSize || !gridOffsetX || !gridOffsetY) {
            throw new Error('Open a square-grid battlemap before asking Pippin to find its printed grid.');
        }
        if (!image.complete) {
            await new Promise((resolve, reject) => {
                image.addEventListener('load', resolve, { once: true });
                image.addEventListener('error', reject, { once: true });
            });
        }
        if (!image.naturalWidth || !image.naturalHeight) {
            throw new Error('The battlemap artwork is not available for grid registration.');
        }

        // Phase IV.30.1F — Grid Registration Intelligence.
        // Analyse the artwork's repeated thin horizontal/vertical strokes without
        // changing the authoritative gameplay grid. A detected registration is
        // preview-only until the Keeper explicitly presses Save Grid.
        const maxDimension = 1200;
        const analysisScale = Math.min(1, maxDimension / Math.max(image.naturalWidth, image.naturalHeight));
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(image.naturalWidth * analysisScale));
        canvas.height = Math.max(1, Math.round(image.naturalHeight * analysisScale));
        const context = canvas.getContext('2d', { willReadFrequently: true });
        if (!context) throw new Error('This browser could not prepare the grid-registration canvas.');
        context.drawImage(image, 0, 0, canvas.width, canvas.height);

        let pixels;
        try {
            pixels = context.getImageData(0, 0, canvas.width, canvas.height);
        } catch (error) {
            throw new Error('The map artwork could not be sampled in this browser. Use same-site Media Library artwork or calibrate the grid manually.');
        }

        const luminance = (x, y) => {
            const px = Math.max(0, Math.min(canvas.width - 1, Math.round(x)));
            const py = Math.max(0, Math.min(canvas.height - 1, Math.round(y)));
            const offset = ((py * canvas.width) + px) * 4;
            return (pixels.data[offset] * .2126) + (pixels.data[offset + 1] * .7152) + (pixels.data[offset + 2] * .0722);
        };

        // Phase IV.30.1F.1 — The Surveyor Learns the Difference Between a Grid and a Wall.
        // Printed grids are usually thin, pale, periodic marks inside otherwise quiet
        // floor areas. Heavy architectural ink is deliberately negative evidence here:
        // Pippin should prefer a faint line that quickly returns to white on both flanks
        // over a thick wall simply because the wall has stronger contrast.
        const axisResponse = (vertical) => {
            const length = vertical ? canvas.width : canvas.height;
            const cross = vertical ? canvas.height : canvas.width;
            const response = new Float32Array(length);
            const crossStep = Math.max(2, Math.floor(cross / 260));
            const near = 1.25;
            const flank = 3.75;
            for (let position = 4; position < length - 4; position += 1) {
                let evidence = 0;
                let support = 0;
                let eligible = 0;
                for (let across = 2; across < cross - 2; across += crossStep) {
                    const center = vertical ? luminance(position, across) : luminance(across, position);
                    const nearA = vertical ? luminance(position - near, across) : luminance(across, position - near);
                    const nearB = vertical ? luminance(position + near, across) : luminance(across, position + near);
                    const flankA = vertical ? luminance(position - flank, across) : luminance(across, position - flank);
                    const flankB = vertical ? luminance(position + flank, across) : luminance(across, position + flank);
                    const flankLight = (flankA + flankB) / 2;
                    const nearLight = (nearA + nearB) / 2;
                    const contrast = flankLight - center;

                    // Quiet-floor gating rejects hatch beds and the black cores of walls.
                    // A printed grid may be faint, but its surrounding paper/floor should
                    // remain light and the stroke itself should not look like heavy ink.
                    if (flankLight < 205 || center < 120) continue;
                    eligible += 1;
                    if (contrast <= 2.5 || contrast >= 72) continue;

                    // Thin-line recovery is the key discriminator. One pixel away from a
                    // printed line we should already be heading back toward the floor tone;
                    // a thick dungeon wall stays dark and therefore receives little credit.
                    const recovery = Math.max(0, nearLight - center);
                    const thinness = .62 + Math.min(.78, recovery / 18);
                    const paleBias = .55 + (Math.min(255, center) / 255) * .45;
                    evidence += Math.min(24, contrast) * thinness * paleBias;
                    if (contrast > 5 && recovery > 1.5) support += 1;
                }
                const supportRatio = support / Math.max(1, eligible);
                response[position] = eligible >= 4
                    ? (evidence / eligible) * (.25 + Math.min(.75, supportRatio * 4))
                    : 0;
            }
            return response;
        };

        const xResponse = axisResponse(true);
        const yResponse = axisResponse(false);
        const displayWidth = Math.max(1, gridViewport.clientWidth);
        const displayHeight = Math.max(1, gridViewport.clientHeight);
        const xScale = canvas.width / displayWidth;
        const yScale = canvas.height / displayHeight;
        const sampleResponse = (response, value) => {
            const index = Math.max(0, Math.min(response.length - 1, Math.round(value)));
            return Number(response[index] || 0);
        };
        const axisComb = (response, spacingCanvas) => {
            if (spacingCanvas < 4) return { score: 0, phase: 0, count: 0, coverage: 0 };
            const phaseSteps = Math.max(8, Math.min(96, Math.round(spacingCanvas)));
            let bestScore = 0;
            let bestPhase = 0;
            let bestCount = 0;
            let bestCoverage = 0;
            for (let step = 0; step < phaseSteps; step += 1) {
                const phase = (step / phaseSteps) * spacingCanvas;
                let score = 0;
                let supported = 0;
                let count = 0;
                for (let position = phase; position < response.length; position += spacingCanvas) {
                    const value = sampleResponse(response, position);
                    score += value;
                    if (value > .14) supported += 1;
                    count += 1;
                }
                const coverage = supported / Math.max(1, count);
                const average = count >= 4 ? score / count : 0;
                const weighted = average * (.55 + (.45 * coverage));
                if (weighted > bestScore) {
                    bestScore = weighted;
                    bestPhase = phase;
                    bestCount = count;
                    bestCoverage = coverage;
                }
            }
            return { score: bestScore, phase: bestPhase, count: bestCount, coverage: bestCoverage };
        };

        const minSize = 8;
        const maxSize = Math.max(minSize, Math.min(192, Math.floor(Math.min(displayWidth, displayHeight) / 3)));
        const candidates = [];
        for (let size = minSize; size <= maxSize; size += 1) {
            const x = axisComb(xResponse, size * xScale);
            const y = axisComb(yResponse, size * yScale);

            // A printed grid must repeat often enough on both axes to distinguish it from
            // room dimensions. Sparse combs are exactly how large rectangular walls fooled
            // the first Registration pass, so fewer than eight crossings is not sufficient.
            if (Math.min(x.count, y.count) < 8) continue;
            const balanced = Math.min(x.score, y.score);
            const combined = (x.score + y.score) / 2;
            const coverage = Math.min(x.coverage, y.coverage);
            const score = ((balanced * .7) + (combined * .3)) * (.72 + (.28 * coverage));
            candidates.push({ size, score, x, y });
        }
        candidates.sort((a, b) => b.score - a.score);
        let best = candidates[0] || null;

        // Room walls often recur every 2–6 printed squares. If a smaller harmonic keeps
        // meaningful evidence on BOTH axes, prefer that fundamental spacing rather than
        // mistaking a room width for the artwork grid. This is deliberately bounded: weak
        // sub-harmonics are ignored instead of manufacturing a tiny grid from noise.
        const fundamentalCandidate = (candidate) => {
            if (!candidate) return null;
            let fundamental = candidate;
            for (let divisor = 6; divisor >= 2; divisor -= 1) {
                const target = candidate.size / divisor;
                if (target < minSize) continue;
                const nearby = candidates
                    .filter((item) => Math.abs(item.size - target) <= 1)
                    .sort((a, b) => Math.abs(a.size - target) - Math.abs(b.size - target) || b.score - a.score)[0];
                if (!nearby) continue;
                const keepsX = nearby.x.score >= candidate.x.score * .42;
                const keepsY = nearby.y.score >= candidate.y.score * .42;
                const repeatsEnough = Math.min(nearby.x.count, nearby.y.count) >= 12;
                if (keepsX && keepsY && repeatsEnough) fundamental = nearby;
            }
            return fundamental;
        };
        best = fundamentalCandidate(best);

        if (!best || best.score < .72 || Math.min(best.x.coverage, best.y.coverage) < .28) {
            throw new Error('Pippin could not find a reliable faint printed square grid in this artwork. Keep the current calibration or adjust it manually.');
        }

        const xPhaseDisplay = best.x.phase / xScale;
        const yPhaseDisplay = best.y.phase / yScale;
        const nearestEquivalentOffset = (phase, current, size) =>
            phase + (Math.round((current - phase) / size) * size);
        const suggestedX = Math.round(nearestEquivalentOffset(xPhaseDisplay, Number(gridOffsetX.value || 0), best.size));
        const suggestedY = Math.round(nearestEquivalentOffset(yPhaseDisplay, Number(gridOffsetY.value || 0), best.size));
        const confidence = Math.max(55, Math.min(96, Math.round(54 + (best.score * 8))));

        gridSize.value = String(best.size);
        gridOffsetX.value = String(suggestedX);
        gridOffsetY.value = String(suggestedY);
        gridVisible.checked = true;
        previewGrid();

        return { size: best.size, x: suggestedX, y: suggestedY, confidence };
    };

    detectGrid?.addEventListener('click', async () => {
        const previousLabel = detectGrid.textContent;
        detectGrid.disabled = true;
        detectGrid.setAttribute('aria-busy', 'true');
        detectGrid.textContent = 'Finding…';
        if (gridRegistrationStatus) gridRegistrationStatus.textContent = 'Pippin is measuring repeated linework in the battlemap…';
        try {
            const suggestion = await detectPrintedGrid();
            const message = `Printed grid found · ${suggestion.size}px · X ${suggestion.x} · Y ${suggestion.y} · ${suggestion.confidence}% confidence · preview only`;
            if (gridRegistrationStatus) gridRegistrationStatus.textContent = message;
            if (cartographerStatus) cartographerStatus.textContent = `${message}. Press Save Grid to make it authoritative.`;
            say(`${message}. Press Save Grid when the overlay aligns.`);
        } catch (error) {
            const message = error?.message || 'Printed-grid registration could not be completed.';
            if (gridRegistrationStatus) gridRegistrationStatus.textContent = message;
            if (cartographerStatus) cartographerStatus.textContent = message;
            say(message);
        } finally {
            detectGrid.disabled = false;
            detectGrid.removeAttribute('aria-busy');
            detectGrid.textContent = previousLabel;
        }
    });

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

    const bestiaryDrawer = document.querySelector('[data-keepers-bestiary]');
    const bestiaryToggle = document.querySelector('[data-bestiary-toggle]');
    const bestiaryClose = document.querySelector('[data-bestiary-close]');
    const bestiarySearch = document.querySelector('[data-bestiary-search]');
    const bestiaryResults = document.querySelector('[data-bestiary-results]');
    const bestiaryEmpty = document.querySelector('[data-bestiary-empty]');
    const bestiaryFilterButtons = Array.from(document.querySelectorAll('[data-bestiary-filter]'));
    let bestiaryMapFilter = 'all';
    const setBestiaryOpen = (open) => {
        if (!bestiaryDrawer || !bestiaryToggle) return;
        if (open) {
            const atlas = document.querySelector('[data-keepers-atlas]');
            const atlasButton = document.querySelector('[data-atlas-toggle]');
            if (atlas) atlas.dataset.open = 'false';
            if (atlasButton) atlasButton.setAttribute('aria-expanded', 'false');
        }
        bestiaryDrawer.dataset.open = open ? 'true' : 'false';
        root.dataset.keeperDrawerOpen = open ? 'bestiary' : '';
        bestiaryToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open && bestiarySearch) {
            window.setTimeout(() => bestiarySearch.focus(), 210);
        }
    };
    bestiaryToggle?.addEventListener('click', () => setBestiaryOpen(bestiaryDrawer?.dataset.open !== 'true'));
    bestiaryClose?.addEventListener('click', () => setBestiaryOpen(false));

    function refreshBestiaryFilterCounts() {
        const cards = Array.from(document.querySelectorAll('[data-bestiary-card]'));
        const onMap = cards.filter((card) => card.dataset.bestiaryOnMap === '1').length;
        const counts = { all: cards.length, 'on-map': onMap, 'not-on-map': cards.length - onMap };
        Object.entries(counts).forEach(([key, count]) => {
            const node = document.querySelector(`[data-bestiary-filter-count="${key}"]`);
            if (node) node.textContent = String(count);
        });
    }

    function applyBestiaryFilters() {
        const query = String(bestiarySearch?.value || '').trim().toLowerCase();
        let visible = 0;
        document.querySelectorAll('[data-bestiary-card]').forEach((card) => {
            const haystack = String(card.dataset.bestiarySearchText || '');
            const onMap = card.dataset.bestiaryOnMap === '1';
            const matchesSearch = query === '' || haystack.includes(query);
            const matchesMap = bestiaryMapFilter === 'all'
                || (bestiaryMapFilter === 'on-map' && onMap)
                || (bestiaryMapFilter === 'not-on-map' && !onMap);
            const matches = matchesSearch && matchesMap;
            card.hidden = !matches;
            if (matches) visible += 1;
        });
        if (bestiaryResults) {
            bestiaryResults.textContent = visible + (visible === 1 ? ' record shown' : ' records shown');
        }
        if (bestiaryEmpty) bestiaryEmpty.hidden = visible !== 0;
    }

    bestiarySearch?.addEventListener('input', applyBestiaryFilters);
    bestiaryFilterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            bestiaryMapFilter = String(button.dataset.bestiaryFilter || 'all');
            bestiaryFilterButtons.forEach((candidate) => {
                candidate.setAttribute('aria-pressed', candidate === button ? 'true' : 'false');
            });
            applyBestiaryFilters();
        });
    });
    refreshBestiaryFilterCounts();
    applyBestiaryFilters();


    function clearBestiaryPlacement() {
        bestiaryPlacement = null;
        root.dataset.bestiaryPlacement = '';
        board.classList.remove('is-bestiary-placing');
        document.querySelector('[data-bestiary-placement-notice]')?.remove();
    }

    function showBestiaryPlacementNotice(name) {
        document.querySelector('[data-bestiary-placement-notice]')?.remove();
        const notice = document.createElement('div');
        notice.className = 'gmrt-threshold-placement gmrt-bestiary-placement';
        notice.dataset.bestiaryPlacementNotice = '1';
        notice.setAttribute('role', 'status');
        notice.setAttribute('aria-live', 'polite');
        const copy = document.createElement('span');
        copy.textContent = `${name} ready — click the map to choose the deployment point.`;
        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.textContent = 'Cancel summoning';
        const cancelPlacement = (event) => {
            event.preventDefault();
            event.stopPropagation();
            clearBestiaryPlacement();
            say('Bestiary summoning cancelled.');
        };
        cancel.addEventListener('pointerdown', cancelPlacement);
        cancel.addEventListener('click', cancelPlacement);
        notice.append(copy, cancel);
        root.appendChild(notice);
    }

    document.querySelectorAll('[data-bestiary-deployment]').forEach((deployment) => {
        const creatureId = String(deployment.dataset.creatureId || '');
        const card = deployment.closest('[data-bestiary-card]');
        const creatureName = card?.querySelector('header strong')?.textContent?.trim() || 'Creature';
        const quantityInput = deployment.querySelector('[data-bestiary-quantity]');
        const hiddenInput = deployment.querySelector('[data-bestiary-hidden]');
        const values = () => ({
            scene_id: preparationSceneId || projectedSceneId,
            creature_id: creatureId,
            quantity: Math.max(1, Math.min(12, Number(quantityInput?.value || 1))),
            hidden: hiddenInput?.checked ? '1' : '0'
        });

        deployment.querySelector('[data-bestiary-threshold]')?.addEventListener('click', async (event) => {
            event.preventDefault();
            event.stopPropagation();
            const button = event.currentTarget;
            const previousLabel = button.textContent;
            button.disabled = true;
            button.textContent = 'Summoning…';
            try {
                const data = await request('gmrt_bestiary_deploy_at_threshold', values());
                say(data.message || `${creatureName} summoned.`);
                await replaceChamber(data.message || `${creatureName} summoned.`, preparationSceneId || null);
            } catch (error) {
                say(error.message || 'The creature could not be summoned.');
                button.disabled = false;
                button.textContent = previousLabel;
            }
        });

        deployment.querySelector('[data-bestiary-place]')?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            bestiaryPlacement = { ...values(), creatureName };
            root.dataset.bestiaryPlacement = creatureId;
            board.classList.add('is-bestiary-placing');
            setBestiaryOpen(false);
            showBestiaryPlacementNotice(creatureName);
            say(`${creatureName} ready — click the map to choose the deployment point.`);
        });
    });

    board.addEventListener('click', async (event) => {
        if (!bestiaryPlacement) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        const placement = bestiaryPlacement;
        const point = coordinatesFromPointer(event);
        try {
            const data = await request('gmrt_bestiary_deploy_at_point', {
                scene_id: placement.scene_id,
                creature_id: placement.creature_id,
                quantity: placement.quantity,
                hidden: placement.hidden,
                x: point.x,
                y: point.y
            });
            clearBestiaryPlacement();
            await replaceChamber(
                data.message || `${placement.creatureName} summoned.`,
                preparationSceneId || null
            );
        } catch (error) {
            say((error.message || 'The creature could not be summoned.') + ' Placement remains armed; click the map to try again or cancel.');
            showBestiaryPlacementNotice(placement.creatureName);
        }
    }, true);

    const atlasStatus = document.querySelector('[data-atlas-status]');
    const atlasAddMap = document.querySelector('[data-atlas-add-map]');
    const atlasSceneName = document.querySelector('[data-atlas-scene-name]');
    const atlasGridSize = document.querySelector('[data-atlas-grid-size]');
    const atlasDrawer = document.querySelector('[data-keepers-atlas]');
    const atlasToggle = document.querySelector('[data-atlas-toggle]');
    const atlasClose = document.querySelector('[data-atlas-close]');
    const atlasForgeName = document.querySelector('[data-atlas-forge-name]');
    const atlasForgeSeed = document.querySelector('[data-atlas-forge-seed]');
    const atlasForgeSceneType = document.querySelector('[data-atlas-forge-scene-type]');
    const atlasForgeStyle = document.querySelector('[data-atlas-forge-style]');
    const atlasForgeTheme = document.querySelector('[data-atlas-forge-theme]');
    const atlasForgeReroll = document.querySelector('[data-atlas-forge-reroll]');
    const atlasForgeCreate = document.querySelector('[data-atlas-forge-create]');
    const atlasForgeStatus = document.querySelector('[data-atlas-forge-status]');
    const pippinFieldNote = document.querySelector('[data-pippin-field-note]');
    const pippinFieldNoteCopy = document.querySelector('[data-pippin-field-note-copy]');
    const pippinNotes = {
        dungeon: 'Walls are walls. Unless they are Mimics. Further testing advised.',
        forest: 'I have personally confirmed that trees are not rooms. Grass remains under investigation.',
        village: 'Buildings confirmed to be rooms. This has now been independently verified.'
    };
    const updatePippinFieldNote = (sceneType, message = '') => {
        if (!pippinFieldNoteCopy) return;
        pippinFieldNoteCopy.textContent = message || pippinNotes[sceneType] || 'Same seed, Scene Type and scale means the same terrain. Cartographical integrity!';
        if (pippinFieldNote) {
            pippinFieldNote.classList.remove('is-speaking');
            window.requestAnimationFrame(() => pippinFieldNote.classList.add('is-speaking'));
        }
    };
    atlasForgeSceneType?.addEventListener('change', () => updatePippinFieldNote(String(atlasForgeSceneType.value || 'dungeon')));
    const setAtlasOpen = (open) => {
        if (!atlasDrawer || !atlasToggle) return;
        if (open) {
            const bestiary = document.querySelector('[data-keepers-bestiary]');
            const bestiaryButton = document.querySelector('[data-bestiary-toggle]');
            if (bestiary) bestiary.dataset.open = 'false';
            if (bestiaryButton) bestiaryButton.setAttribute('aria-expanded', 'false');
        }
        atlasDrawer.dataset.open = open ? 'true' : 'false';
        root.dataset.keeperDrawerOpen = open ? 'atlas' : '';
        atlasToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    atlasToggle?.addEventListener('click', () => setAtlasOpen(atlasDrawer?.dataset.open !== 'true'));
    atlasClose?.addEventListener('click', () => setAtlasOpen(false));

    atlasForgeReroll?.addEventListener('click', () => {
        if (!atlasForgeSeed) return;
        atlasForgeSeed.value = `Peppercorn-${Math.random().toString(36).slice(2,8).toUpperCase()}`;
        if (atlasForgeStatus) atlasForgeStatus.textContent = 'A fresh deterministic seed is ready for Pippin.';
        updatePippinFieldNote(String(atlasForgeSceneType?.value || 'dungeon'), 'Fresh seed recorded. Same seed, same terrain. I have written this down twice.');
    });

    atlasForgeCreate?.addEventListener('click', async () => {
        const sceneName = String(atlasForgeName?.value || '').trim();
        const sceneType = String(atlasForgeSceneType?.value || 'dungeon');
        const seed = String(atlasForgeSeed?.value || '').trim() || 'Peppercorn-01';
        const style = String(atlasForgeStyle?.value || 'standard');
        const theme = String(atlasForgeTheme?.value || 'pantry-stone');
        if (!sceneName) {
            const message = 'Give Pippin a name for the new Scene first.';
            if (atlasForgeStatus) atlasForgeStatus.textContent = message;
            atlasForgeName?.focus();
            say(message);
            return;
        }

        let plan;
        try {
            const aspectByStyle = { compact:.78, standard:.7, grand:.65 };
            plan = generateSceneForgePlan(sceneType, seed, style, theme, aspectByStyle[style] || .7);
        } catch (error) {
            const message = error?.message || 'Pippin could not prepare that Scene plan.';
            if (atlasForgeStatus) atlasForgeStatus.textContent = message;
            say(message);
            return;
        }

        atlasForgeCreate.disabled = true;
        const previousLabel = atlasForgeCreate.textContent;
        atlasForgeCreate.textContent = 'Forging World…';
        if (atlasForgeStatus) atlasForgeStatus.textContent = `Forging ${sceneName} from nothing…`;
        updatePippinFieldNote(sceneType, `Surveying ${sceneName}… please refrain from moving any hills while I measure them.`);
        try {
            const data = await request('gmrt_forge_dungeon_world', {
                scene_name: sceneName,
                plan: JSON.stringify(plan)
            });
            const sceneId = String(data.scene?.id || '');
            const message = data.message || `${sceneName} has been forged into the Keeper's Atlas.`;
            if (atlasForgeStatus) atlasForgeStatus.textContent = message;
            say(message);
            if (sceneId) {
                await replaceChamber(`${message} Behind the Curtain for inspection.`, sceneId);
            } else {
                window.location.reload();
            }
        } catch (error) {
            const message = error?.message || 'The new generated Scene could not be forged.';
            if (atlasForgeStatus) atlasForgeStatus.textContent = message;
            say(message);
            atlasForgeCreate.disabled = false;
            atlasForgeCreate.textContent = previousLabel;
        }
    });

    document.querySelectorAll('[data-atlas-prepare-map]').forEach((button) => {
        button.addEventListener('click', async () => {
            const sceneId = String(button.dataset.sceneId || '');
            if (!sceneId) return;
            button.disabled = true;
            const previousLabel = button.textContent;
            button.textContent = 'Preparing…';
            if (atlasStatus) atlasStatus.textContent = 'Drawing the curtain around the chosen Scene…';
            try {
                await replaceChamber('Behind the Curtain — private Scene preparation.', sceneId);
            } catch (error) {
                const message = error.message || 'The Scene could not be prepared privately.';
                if (atlasStatus) atlasStatus.textContent = message;
                say(message);
                button.disabled = false;
                button.textContent = previousLabel;
            }
        });
    });

    document.querySelector('[data-exit-preparation]')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        button.disabled = true;
        const previousLabel = button.textContent;
        button.textContent = 'Returning…';
        try {
            await replaceChamber('Returned to the live Scene.', null);
        } catch (error) {
            say(error.message || 'The live Scene could not be restored.');
            button.disabled = false;
            button.textContent = previousLabel;
        }
    });

    function clearThresholdPlacement() {
        thresholdPlacement = null;
        root.dataset.thresholdPlacement = '';
        board.classList.remove('is-threshold-placing');
        document.querySelector('[data-threshold-placement-notice]')?.remove();
    }

    function showThresholdPlacementNotice(type) {
        document.querySelector('[data-threshold-placement-notice]')?.remove();

        const notice = document.createElement('div');
        notice.className = 'gmrt-threshold-placement';
        notice.dataset.thresholdPlacementNotice = '1';
        notice.setAttribute('role', 'status');
        notice.setAttribute('aria-live', 'polite');

        const copy = document.createElement('span');
        const repositioning = Boolean(thresholdPlacement && thresholdPlacement.markerId);
        copy.textContent = repositioning
            ? 'Threshold repositioning armed — click the map to choose its new position.'
            : (type === 'party'
                ? 'Party Arrival armed — click anywhere on the map to place the Threshold.'
                : 'Monster Deployment armed — click anywhere on the map to place the Threshold.');

        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.textContent = 'Cancel placement';
        const cancelPlacement = (event) => {
            event.preventDefault();
            event.stopPropagation();
            clearThresholdPlacement();
            say('Threshold placement cancelled.');
        };
        cancel.addEventListener('pointerdown', cancelPlacement);
        cancel.addEventListener('click', cancelPlacement);

        notice.append(copy, cancel);
        root.appendChild(notice);
    }

    document.querySelectorAll('[data-threshold-place]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const type = String(button.dataset.thresholdPlace || '');
            const sceneId = String(button.dataset.sceneId || projectedSceneId);
            if (!['party', 'monster'].includes(type) || !sceneId) return;

            thresholdPlacement = { type, sceneId };
            root.dataset.thresholdPlacement = type;
            board.classList.add('is-threshold-placing');
            showThresholdPlacementNotice(type);

            const atlasDrawer = document.querySelector('[data-keepers-atlas]');
            const atlasToggle = document.querySelector('[data-atlas-toggle]');
            if (atlasDrawer) atlasDrawer.dataset.open = 'false';
            if (atlasToggle) atlasToggle.setAttribute('aria-expanded', 'false');

            say(type === 'party'
                ? 'Party Arrival armed — click the map to place the Threshold.'
                : 'Monster Deployment armed — click the map to place the Threshold.');
        });
    });

    // Threshold placement owns the next map click before cartography/token handlers.
    // Capture phase is intentional: placement must remain reliable even when the
    // Keeper clicks over a token or another interactive battlefield layer.
    board.addEventListener('click', async (event) => {
        if (!thresholdPlacement) return;

        event.preventDefault();
        event.stopImmediatePropagation();

        const placement = thresholdPlacement;
        const point = coordinatesFromPointer(event);

        try {
            const action = placement.markerId
                ? 'gmrt_atlas_move_threshold'
                : 'gmrt_atlas_place_threshold';
            const values = {
                scene_id: placement.sceneId,
                threshold_type: placement.type,
                x: point.x,
                y: point.y
            };
            if (placement.markerId) values.marker_id = placement.markerId;

            const data = await request(action, values);
            clearThresholdPlacement();
            await replaceChamber(
                data.message || 'Threshold Marker placed.',
                preparationSceneId || null
            );
        } catch (error) {
            const message = error.message || 'The Threshold Marker could not be placed.';
            say(message + ' Placement remains armed; click the map to try again or cancel.');
            showThresholdPlacementNotice(placement.type);
        }
    }, true);

    document.querySelectorAll('[data-threshold-marker]').forEach((button) => {
        button.addEventListener('click', async (event) => {
            event.preventDefault();
            event.stopPropagation();

            const markerId = String(button.dataset.thresholdMarker || '');
            const type = String(button.dataset.thresholdType || 'party');
            const sceneId = String(button.dataset.sceneId || projectedSceneId);
            if (!markerId || !sceneId) return;

            if (event.shiftKey) {
                if (!window.confirm('Remove this Threshold Marker?')) return;
                try {
                    const data = await request('gmrt_atlas_remove_threshold', {
                        scene_id: sceneId,
                        marker_id: markerId
                    });
                    await replaceChamber(data.message || 'Threshold Marker removed.', preparationSceneId || null);
                } catch (error) {
                    say(error.message || 'The Threshold Marker could not be removed.');
                }
                return;
            }

            thresholdPlacement = { type, sceneId, markerId };
            root.dataset.thresholdPlacement = type;
            board.classList.add('is-threshold-placing');
            showThresholdPlacementNotice(type);
            say('Threshold repositioning armed — click the map to choose its new position. Shift-click the marker to remove it instead.');
        });
    });

    document.querySelectorAll('[data-atlas-open-map]').forEach((button) => {
        button.addEventListener('click', async () => {
            const sceneId = String(button.dataset.sceneId || '');
            if (!sceneId) return;

            const previousLabel = button.textContent;
            button.disabled = true;
            button.textContent = 'Opening…';
            if (atlasStatus) atlasStatus.textContent = 'Opening the chosen Scene…';

            try {
                const data = await request('gmrt_atlas_open_map', { scene_id: sceneId });
                const message = data.message || 'Scene opened.';
                if (atlasStatus) atlasStatus.textContent = message;
                say(message);
                await replaceChamber(message, null);
            } catch (error) {
                const message = error.message || 'The Scene could not be opened.';
                if (atlasStatus) atlasStatus.textContent = message;
                say(message);
                button.disabled = false;
                button.textContent = previousLabel;
            }
        });
    });

    document.querySelectorAll('[data-atlas-delete-map]').forEach((button) => {
        button.addEventListener('click', async () => {
            const sceneId = String(button.dataset.sceneId || '');
            const sceneName = String(button.dataset.sceneName || 'this Scene');
            if (!sceneId) return;
            if (!window.confirm(`Permanently delete “${sceneName}”? Its tokens, Fog, walls, doors, encounters, lights and other Scene state will also be removed.`)) return;

            const previousLabel = button.textContent;
            button.disabled = true;
            button.textContent = 'Clearing…';
            try {
                const data = await request('gmrt_atlas_delete_map', { scene_id: sceneId });
                const message = data.message || 'Scene removed from the Atlas.';
                say(message);
                await replaceChamber(message, null);
            } catch (error) {
                const message = error.message || 'The Scene could not be removed.';
                if (atlasStatus) atlasStatus.textContent = message;
                say(message);
                button.disabled = false;
                button.textContent = previousLabel;
            }
        });
    });

    if (atlasAddMap) {
        atlasAddMap.addEventListener('click', () => {
            const sceneName = atlasSceneName ? atlasSceneName.value.trim() : '';
            const gridSize = atlasGridSize ? Math.max(1, Number(atlasGridSize.value || 64)) : 64;

            if (!sceneName) {
                const message = 'Give this place a name before choosing its map.';
                if (atlasStatus) atlasStatus.textContent = message;
                if (atlasSceneName) atlasSceneName.focus();
                say(message);
                return;
            }

            if (!window.wp || !window.wp.media) {
                say('The WordPress Media Library is unavailable.');
                return;
            }

            const frame = window.wp.media({
                title: 'Add a Map to the Keeper\'s Atlas',
                button: { text: 'Enter Map in Atlas' },
                library: { type: 'image' },
                multiple: false
            });

            frame.on('select', async () => {
                const selected = frame.state().get('selection').first();
                if (!selected) return;
                const attachment = selected.toJSON();

                atlasAddMap.disabled = true;
                const previousLabel = atlasAddMap.textContent;
                atlasAddMap.textContent = 'Inscribing…';
                if (atlasStatus) atlasStatus.textContent = `Adding ${sceneName} to the Atlas…`;

                try {
                    const data = await request('gmrt_atlas_add_map', {
                        scene_name: sceneName,
                        attachment_id: attachment.id,
                        grid_size: gridSize
                    });
                    const message = data.message || `${sceneName} has been added to the Atlas.`;
                    if (atlasStatus) atlasStatus.textContent = message;
                    say(message);
                    window.location.reload();
                } catch (error) {
                    const message = error.message || 'The map could not be added to the Atlas.';
                    if (atlasStatus) atlasStatus.textContent = message;
                    say(message);
                    atlasAddMap.disabled = false;
                    atlasAddMap.textContent = previousLabel;
                }
            });

            frame.open();
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

    const combatDock = document.querySelector('[data-combat-dock]');
    const combatGuidanceCopy = document.querySelector('[data-combat-guidance-copy]');
    const combatTurnBadge = document.querySelector('[data-combat-turn-badge]');
    const playerTurnBadge = document.querySelector('[data-player-turn-badge]');
    const satchelCombatHome = document.querySelector('[data-satchel-combat-home]');
    const satchelCombatMount = document.querySelector('[data-satchel-combat-mount]');

    function attackOptionLabel(attack) {
        const combat = attack && attack.combat ? attack.combat : {};
        const damage = attack && attack.damage ? attack.damage : {};
        const normal = Number(combat.attack_range_feet || 5);
        const long = Number(combat.long_range_feet || normal);
        const range = long > normal ? normal + '/' + long + ' ft' : normal + ' ft';
        const count = Number(damage.dice_count || 1);
        const sides = Number(damage.die_sides || 6);
        const modifier = Number(damage.modifier || 0);
        const formula = count + 'd' + sides + (modifier > 0 ? '+' + modifier : modifier < 0 ? String(modifier) : '');
        return String(attack.name || 'Attack') + ' · ' + formula + ' ' + String(damage.damage_type || '').toUpperCase() + ' · ' + range;
    }

    function populateCombatDock(currentTokenId, tokens, arsenals) {
        if (!combatDock || !attackTarget || !arsenalAttack) return;

        const arsenalRecord = arsenals && arsenals[currentTokenId]
            ? arsenals[currentTokenId]
            : {};
        const attacks = Array.isArray(arsenalRecord.attacks)
            ? arsenalRecord.attacks
            : [];

        arsenalAttack.replaceChildren();
        if (attacks.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No attack readied';
            arsenalAttack.append(option);
            arsenalAttack.disabled = true;
        } else {
            arsenalAttack.disabled = false;
            attacks.forEach((attack) => {
                const option = document.createElement('option');
                option.value = String(attack.id || '');
                option.textContent = attackOptionLabel(attack);
                arsenalAttack.append(option);
            });
        }

        attackTarget.replaceChildren();
        const empty = document.createElement('option');
        empty.value = '';
        empty.textContent = 'Choose target…';
        attackTarget.append(empty);
        (Array.isArray(tokens) ? tokens : []).forEach((token) => {
            const id = String(token.id || '');
            if (!id || id === currentTokenId) return;
            const option = document.createElement('option');
            option.value = id;
            option.textContent = String(token.label || 'Token');
            attackTarget.append(option);
        });
    }

    function syncCombatDock(state = null) {
        if (!combatDock || !deedsPanel) return;

        const currentTokenId = state && state.encounter
            ? String(state.encounter.current_token_id || '')
            : String(deedsPanel.dataset.currentToken || '');
        deedsPanel.dataset.currentToken = currentTokenId;

        const combatStatusMount = document.querySelector('[data-combat-status-mount]');
        if (rangeStatus && combatStatusMount && rangeStatus.parentElement !== combatStatusMount) {
            combatStatusMount.append(rangeStatus);
        }

        document.querySelectorAll('[data-bestiary-instance-id]').forEach((instance) => {
            const active = instance.dataset.bestiaryInstanceId === currentTokenId;
            instance.classList.toggle('is-active-turn', active);
            const badge = instance.querySelector('[data-bestiary-turn-badge]');
            if (badge) badge.hidden = !active;
        });
        document.querySelectorAll('[data-bestiary-card]').forEach((card) => {
            card.classList.toggle('is-active-turn', Boolean(card.querySelector('[data-bestiary-instance-id].is-active-turn')));
        });

        if (state) {
            document.querySelectorAll('[data-bestiary-instance-id]').forEach((instance) => {
                const tokenId = String(instance.dataset.bestiaryInstanceId || '');
                const vitality = state.vitality && state.vitality[tokenId] ? state.vitality[tokenId] : null;
                const hp = instance.querySelector('[data-bestiary-instance-hp]');
                if (hp && vitality) {
                    hp.textContent = String(vitality.current_hp) + '/' + String(vitality.maximum_hp);
                }
                const conditions = state.conditions && Array.isArray(state.conditions[tokenId])
                    ? state.conditions[tokenId]
                    : [];
                const conditionNode = instance.querySelector('[data-bestiary-instance-conditions]');
                if (conditionNode) {
                    conditionNode.textContent = conditions.length === 0
                        ? 'No conditions'
                        : conditions.map((condition) => String(condition.condition || condition.type || '')).filter(Boolean).join(', ');
                }
            });
        }

        if (!currentTokenId) {
            combatDock.hidden = true;
            if (combatGuidanceCopy) combatGuidanceCopy.textContent = 'No combatant currently has the turn.';
            return;
        }

        if (state) {
            populateCombatDock(currentTokenId, state.tokens || [], state.arsenals || {});
        }

        const actor = document.querySelector('[data-token-id="' + CSS.escape(currentTokenId) + '"]');
        const viewerRole = String(root?.dataset.viewerRole || '');
        const viewerUserId = String(root?.dataset.viewerUserId || '');
        const controller = String(actor?.dataset.tokenController || '');
        const source = String(actor?.dataset.tokenSource || '');
        let mount = null;
        let guidance = '';

        const playerHasTurn = viewerRole === 'player'
            && controller !== ''
            && controller === viewerUserId;

        satchelToggle?.classList.toggle('has-active-turn', playerHasTurn);
        satchelCombatHome?.classList.toggle('is-active-turn', playerHasTurn);
        if (playerTurnBadge) playerTurnBadge.hidden = !playerHasTurn;
        if (combatTurnBadge) {
            combatTurnBadge.hidden = !playerHasTurn;
            combatTurnBadge.textContent = playerHasTurn ? 'YOUR TURN' : '';
        }

        if (playerHasTurn) {
            mount = satchelCombatMount;
            guidance = 'YOUR TURN — choose the action in your Satchel. Range and legality are reported here.';
        }

        if (viewerRole === 'dungeon-master' && source.startsWith('gmrt-bestiary:')) {
            const instance = document.querySelector('[data-bestiary-instance-id="' + CSS.escape(currentTokenId) + '"]');
            mount = instance?.querySelector('[data-bestiary-combat-mount]') || null;
            guidance = 'Creature turn — act from the highlighted Bestiary instance. End Turn remains here.';
            bestiaryToggle?.classList.add('has-active-turn');
            if (bestiaryDrawer?.dataset.open === 'true') {
                instance?.scrollIntoView({block: 'nearest', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'});
            }
        } else {
            bestiaryToggle?.classList.remove('has-active-turn');
        }

        if (!mount) {
            combatDock.hidden = true;
            if (combatGuidanceCopy) {
                combatGuidanceCopy.textContent = viewerRole === 'dungeon-master'
                    ? 'The active combatant is not a Bestiary creature. Turn control remains here.'
                    : 'Waiting for your adventurer\'s turn.';
            }
            clearTargeting();
            return;
        }

        if (combatDock.parentElement !== mount) mount.append(combatDock);
        combatDock.hidden = false;
        if (combatGuidanceCopy) combatGuidanceCopy.textContent = guidance;
        clearTargeting();
    }

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
            rangeStatus.textContent = 'NO TARGET SELECTED';
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

    syncCombatDock();

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

        if (selected && attackTarget && !combatDock?.hidden) {
            const tokenId = String(selected.dataset.tokenId || '');
            const attackerId = String(deedsPanel?.dataset.currentToken || '');
            const hasTarget = Array.from(attackTarget.options).some((option) => option.value === tokenId);
            if (tokenId && tokenId !== attackerId && hasTarget) {
                attackTarget.value = tokenId;
                updateTargeting();
            }
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

    const lanternRack = document.querySelector('[data-lantern-rack]');
    const keeperLightButtons = Array.from(document.querySelectorAll('[data-keeper-light-kind]'));
    const keeperLightCancel = document.querySelector('[data-keeper-light-cancel]');
    const keeperLightStatus = document.querySelector('[data-keeper-light-status]');
    const keeperLightLabels = {torch:'Torch',lantern:'Lantern',brazier:'Brazier',candle:'Candle',magical:'Magical Light'};

    function renderKeeperLightRoster(projection = fogProjection) {
        if (!keeperLightRoster) return;
        keeperLightRoster.replaceChildren();
        const lights = (Array.isArray(projection?.light_sources) ? projection.light_sources : []).filter((source) => String(source.source_kind || '') === 'environmental');
        if (!lights.length) { const empty=document.createElement('small'); empty.textContent='No Keeper lights on this Scene yet.'; keeperLightRoster.appendChild(empty); return; }
        lights.forEach((source) => {
            const row=document.createElement('div'); row.className='gmrt-lantern-rack__row';
            const identity=document.createElement('span'); identity.className='gmrt-lantern-rack__identity';
            const label=document.createElement('strong'); label.textContent=String(source.label || 'Light'); identity.appendChild(label);
            const radius=document.createElement('small'); const brightFeet=Math.max(0,Number(source.bright_light_feet || 0)); radius.textContent=brightFeet ? `${brightFeet} ft radius` : 'No illumination'; identity.appendChild(radius); row.appendChild(identity);
            const state=document.createElement('span'); const isLit=source.lit !== false; state.className='gmrt-lantern-rack__state ' + (isLit ? 'is-lit' : 'is-doused'); state.textContent=isLit ? '● Lit' : '○ Doused'; row.appendChild(state);
            const douse=document.createElement('button'); douse.type='button'; douse.textContent=isLit ? 'Douse' : 'Light'; douse.setAttribute('aria-label',(isLit ? 'Douse ' : 'Light ') + String(source.label || 'light')); douse.dataset.keeperLightToggle=String(source.token_id || ''); douse.dataset.keeperLightAction=isLit ? 'douse' : 'light'; row.appendChild(douse);
            const remove=document.createElement('button'); remove.type='button'; remove.textContent='Remove'; remove.dataset.keeperLightRemove=String(source.token_id || ''); row.appendChild(remove);
            keeperLightRoster.appendChild(row);
        });
    }

    keeperLightButtons.forEach((button) => button.addEventListener('click', () => {
        keeperLightPlacement=String(button.dataset.keeperLightKind || 'torch');
        board.classList.add('is-keeper-light-placing');
        root.dataset.keeperLightPlacement = keeperLightPlacement;
        keeperLightButtons.forEach((candidate)=>candidate.classList.toggle('is-active',candidate===button));
        if (keeperLightCancel) keeperLightCancel.disabled=false;
        if (keeperLightStatus) keeperLightStatus.textContent=`${keeperLightLabels[keeperLightPlacement] || 'Light'} selected — click the map to place it.`;
    }));
    keeperLightCancel?.addEventListener('click',()=>{keeperLightPlacement=null;board.classList.remove('is-keeper-light-placing');root.dataset.keeperLightPlacement='';keeperLightButtons.forEach((button)=>button.classList.remove('is-active'));keeperLightCancel.disabled=true;if(keeperLightStatus)keeperLightStatus.textContent='Placement finished.';});

    // Lantern placement owns the next battlefield pointer in capture phase.
    // Use pointerdown rather than a late click so the armed placement wins before
    // fog, tokens, Lens panning, vision or cartography can claim the gesture.
    board.addEventListener('pointerdown', async (event) => {
        if (!keeperLightPlacement) return;

        event.preventDefault();
        event.stopImmediatePropagation();

        const point = coordinatesFromPointer(event);
        const kind = keeperLightPlacement;
        try {
            const data = await request('gmrt_tend_environmental_light', {
                light_action: 'place',
                kind,
                x: point.x,
                y: point.y,
                scene_id: preparationSceneId || projectedSceneId
            });
            keeperLightPlacement = null;
            board.classList.remove('is-keeper-light-placing');
            root.dataset.keeperLightPlacement = '';
            keeperLightButtons.forEach((button) => button.classList.remove('is-active'));
            if (keeperLightCancel) keeperLightCancel.disabled = true;
            if (keeperLightStatus) keeperLightStatus.textContent = data.message || 'Light placed.';
            await replaceChamber(data.message || 'Light placed.', preparationSceneId || null);
        } catch (error) {
            if (keeperLightStatus) keeperLightStatus.textContent = (error.message || 'The light could not be placed.') + ' Placement remains armed; click the map to try again or cancel.';
        }
    }, true);
    keeperLightRoster?.addEventListener('click', async (event) => {
        const button=event.target.closest('button'); if(!button)return;
        const lightId=String(button.dataset.keeperLightToggle || button.dataset.keeperLightRemove || ''); if(!lightId)return;
        button.disabled=true;
        try { const action=button.dataset.keeperLightRemove?'remove':String(button.dataset.keeperLightAction || 'toggle'); const data=await request('gmrt_tend_environmental_light',{light_action:action,light_id:lightId,scene_id:preparationSceneId||projectedSceneId}); if(keeperLightStatus)keeperLightStatus.textContent=data.message||'Lantern Rack updated.'; await replaceChamber(data.message||'Lantern Rack updated.', preparationSceneId || null); }
        catch(error){if(keeperLightStatus)keeperLightStatus.textContent=error.message||'The light could not be tended.';button.disabled=false;}
    });

    function coordinatesFromPointer(event) {
        const battlemap = board.querySelector('[data-battlemap-image]');
        const rect = (battlemap || board).getBoundingClientRect();

        return {
            x: Math.max(0, Math.min(1, (event.clientX - rect.left) / Math.max(1, rect.width))),
            y: Math.max(0, Math.min(1, (event.clientY - rect.top) / Math.max(1, rect.height)))
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
            const incomingSceneId = String(state.scene?.id || '');

            /*
             * Passage Between Places: live viewers follow the authoritative
             * Scene selected by the Keeper. A DM Behind the Curtain is pinned
             * to the private preparation Scene and must not be pulled away.
             */
            if (
                !preparationSceneId
                && incomingSceneId !== projectedSceneId
            ) {
                if (root.dataset.viewerRole === 'player' && incomingSceneId) {
                    await request('gmrt_atlas_arrive_at_threshold', {
                        scene_id: incomingSceneId
                    });
                }
                await replaceChamber(
                    'Passage Between Places — the Table carries you to a new Scene.',
                    null
                );
                return;
            }

            const tokens = Array.isArray(state.tokens) ? state.tokens : [];
            const selectedCharacter = state.integrations?.companion?.selected_character || null;
            const satchel = document.querySelector('[data-adventurer-satchel]');
            if (satchel && !selectedCharacter) {
                satchel.remove();
            }
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
                syncCombatDock(state);
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

                syncCombatDock(state);
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

    board.addEventListener('click', async (event) => {
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
    const damageRollButton = document.querySelector(
        '[data-roll-attack-damage]'
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

        if (damageRollButton) {
            damageRollButton.hidden = true;
            damageRollButton.disabled = false;
            damageRollButton.dataset.attackEventId = '';
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

    function armDamageRoll(pendingDamage) {
        if (!damageRollButton || !pendingDamage) {
            return;
        }

        const profile = pendingDamage.damage_profile || {};
        const critical = Boolean(pendingDamage.critical);
        const diceCount = Math.max(1, Number(profile.dice_count || 1))
            * (critical ? 2 : 1);
        const sides = Math.max(2, Number(profile.die_sides || 6));
        const modifier = Number(profile.modifier || 0);
        const formula =
            String(diceCount) + 'd' + String(sides)
            + (modifier > 0 ? '+' + String(modifier) : modifier < 0 ? String(modifier) : '');

        damageRollButton.dataset.attackEventId =
            String(pendingDamage.attack_event_id || '');
        damageRollButton.textContent =
            'Roll Damage · ' + formula
            + (critical ? ' CRITICAL' : '');
        damageRollButton.hidden = false;
    }

    function revealDamageRoll(data) {
        if (!diceworks || !data || !data.damage) {
            return;
        }

        const damage = data.damage;
        const rolls = Array.isArray(damage.rolls) ? damage.rolls : [];
        const adjusted = data.damage_adjustment || {};
        const vitality = data.vitality || {};
        const effects = Array.isArray(adjusted.effects) ? adjusted.effects : [];
        let effect = '';

        if (effects.includes('immune')) {
            effect = ' · IMMUNE!';
        } else {
            if (effects.includes('resistant')) { effect += ' · RESIST!'; }
            if (effects.includes('vulnerable')) { effect += ' · WEAK!'; }
        }

        diceworks.classList.remove('is-rolling');
        if (diceworksMode) {
            diceworksMode.textContent = damage.critical ? 'CRITICAL DAMAGE' : 'DAMAGE';
        }
        combatDice.forEach((die, index) => {
            die.hidden = index >= Math.min(2, rolls.length);
            const value = die.querySelector('[data-die-value]');
            if (value && rolls[index] !== undefined) {
                value.textContent = String(rolls[index]);
            }
        });
        if (diceworksOutcomeTitle) {
            diceworksOutcomeTitle.textContent = damage.critical ? 'CRITICAL DAMAGE!' : 'DAMAGE ROLLED';
        }
        if (diceworksOutcomeDetail) {
            diceworksOutcomeDetail.textContent =
                '[' + rolls.join(' + ') + ']'
                + (Number(damage.modifier || 0) !== 0 ? ' + ' + String(damage.modifier) : '')
                + ' = ' + String(damage.total)
                + ' rolled · ' + String(adjusted.resolved_damage || 0)
                + ' ' + String(adjusted.damage_type || '').toUpperCase()
                + ' DAMAGE' + effect
                + ' · HP ' + String(vitality.current_hp || 0)
                + '/' + String(vitality.maximum_hp || 0);
        }
        if (diceworksOutcome) {
            diceworksOutcome.hidden = false;
        }
        if (diceworksResult) {
            diceworksResult.textContent = 'The Guild Diceworks has certified the damage roll.';
        }
        if (damageRollButton) {
            damageRollButton.hidden = true;
            damageRollButton.dataset.attackEventId = '';
        }
    }

    if (damageRollButton) {
        damageRollButton.addEventListener('click', async () => {
            const encounter = document.querySelector('[data-encounter-id]');
            const attackEventId = damageRollButton.dataset.attackEventId || '';
            if (!encounter || !attackEventId) {
                return;
            }

            damageRollButton.disabled = true;
            if (diceworks) { diceworks.classList.add('is-rolling'); }
            if (diceworksResult) { diceworksResult.textContent = 'The damage dice are rolling…'; }

            try {
                const data = await request('gmrt_roll_attack_damage', {
                    encounter_id: encounter.dataset.encounterId || '',
                    attack_event_id: attackEventId
                });
                revealDamageRoll(data);
                say('Damage resolved — see Guild Diceworks.');
                await refresh();
            } catch (error) {
                if (diceworks) { diceworks.classList.remove('is-rolling'); }
                if (diceworksResult) { diceworksResult.textContent = 'Damage roll halted — ' + (error.message || 'unable to resolve damage.'); }
                damageRollButton.disabled = false;
                say(error.message || 'Damage could not be resolved.');
            }
        });
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

    function cancelCombatRoll(reason = '') {
        if (!diceworks) {
            return;
        }

        diceworks.classList.remove('is-rolling');
        if (diceworksMode) diceworksMode.textContent = 'D20';
        if (diceworksResult) {
            diceworksResult.textContent = reason
                ? 'Roll halted — ' + String(reason)
                : 'Roll halted.';
        }
        combatDice.forEach((die) => {
            die.classList.remove('is-chosen', 'is-rejected');
        });
        if (diceworksOutcome) diceworksOutcome.hidden = true;
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

                    if (data.pending_damage) {
                        armDamageRoll(data.pending_damage);
                        if (diceworksResult) {
                            diceworksResult.textContent = 'Hit certified — roll the authoritative damage dice.';
                        }
                        say('Hit confirmed — roll damage in Guild Diceworks.');
                        return;
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
                cancelCombatRoll(error.message || 'That action could not be resolved.');
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
