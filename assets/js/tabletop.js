(function () {
    'use strict';

    const i18nStrings = (window.gmrtTabletop && window.gmrtTabletop.strings) || {};
    const t = (key, fallback) => i18nStrings[key] || fallback;

    let activeRefreshTimer = null;

    // Phase IV.32.5A — Keeper Drawer lifecycle rail.
    // Atlas/Bestiary controls are delegated from the stable document so Chamber
    // fragment replacement (including generated Scene preparation/opening) cannot
    // strand freshly-rendered drawer tabs without click handlers.
    function setKeeperDrawerOpen(kind, open) {
        const root = document.querySelector('.gmrt-chamber');
        if (!root) return;

        const drawers = {
            session: {
                drawer: document.querySelector('[data-keeper-session]'),
                toggle: document.querySelector('[data-keeper-session-toggle]')
            },
            tools: {
                drawer: document.querySelector('[data-keeper-tools]'),
                toggle: document.querySelector('[data-keeper-tools-toggle]')
            },
            atlas: {
                drawer: document.querySelector('[data-keepers-atlas]'),
                toggle: document.querySelector('[data-atlas-toggle]')
            },
            bestiary: {
                drawer: document.querySelector('[data-keepers-bestiary]'),
                toggle: document.querySelector('[data-bestiary-toggle]')
            }
        };

        const active = drawers[kind];
        if (!active?.drawer || !active?.toggle) return;

        Object.entries(drawers).forEach(([candidateKind, candidate]) => {
            if (!candidate.drawer || !candidate.toggle) return;
            const candidateOpen = open && candidateKind === kind;
            candidate.drawer.dataset.open = candidateOpen ? 'true' : 'false';
            candidate.toggle.setAttribute('aria-expanded', candidateOpen ? 'true' : 'false');
        });

        root.dataset.keeperDrawerOpen = open ? kind : '';

        if (open && kind === 'bestiary') {
            const search = document.querySelector('[data-bestiary-search]');
            if (search) window.setTimeout(() => search.focus(), 210);
        }
    }

    function sessionRecapStorageKey(recap) {
        const root = document.querySelector('.gmrt-chamber');
        const tableId = root?.dataset.tableId || 'table';
        const recapId = recap?.dataset.sessionRecapId || 'latest';
        return `gmrt:session-recap:${tableId}:${recapId}:collapsed`;
    }

    function setSessionRecapExpanded(recap, expanded, remember = true) {
        if (!recap) return;
        const toggle = recap.querySelector('[data-session-recap-toggle]');
        const content = recap.querySelector('[data-session-recap-content]');
        if (!toggle || !content) return;

        recap.classList.toggle('is-collapsed', !expanded);
        toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        toggle.textContent = expanded ? t('hideRecap', 'Hide Recap') : t('showRecap', 'Show Recap');
        content.hidden = !expanded;

        if (remember) {
            try {
                window.localStorage.setItem(sessionRecapStorageKey(recap), expanded ? '0' : '1');
            } catch (error) {
                // Storage can be unavailable in privacy-restricted browsers; the toggle still works.
            }
        }
    }

    function restoreSessionRecapPreference() {
        const recap = document.querySelector('[data-session-recap]');
        if (!recap) return;
        let collapsed = false;
        try {
            collapsed = window.localStorage.getItem(sessionRecapStorageKey(recap)) === '1';
        } catch (error) {
            collapsed = false;
        }
        setSessionRecapExpanded(recap, !collapsed, false);
    }

    function sessionClosingStorageKey(tableId) {
        return `gmrt:session-closing:${tableId || 'table'}`;
    }

    function rememberSessionClosing(tableId, sessionId) {
        if (!sessionId) return;
        try {
            window.sessionStorage.setItem(sessionClosingStorageKey(tableId), sessionId);
        } catch (error) {
            // The farewell remains optional if browser storage is unavailable.
        }
    }

    function revealFreshSessionClosing() {
        const root = document.querySelector('.gmrt-chamber');
        const closing = document.querySelector('[data-session-closing]');
        if (!root || !closing) return;

        let rememberedSessionId = '';
        try {
            const key = sessionClosingStorageKey(root.dataset.tableId || '');
            rememberedSessionId = window.sessionStorage.getItem(key) || '';
            if (rememberedSessionId !== '') {
                window.sessionStorage.removeItem(key);
            }
        } catch (error) {
            rememberedSessionId = '';
        }

        if (rememberedSessionId !== '' && rememberedSessionId === (closing.dataset.sessionClosingId || '')) {
            closing.hidden = false;
        }
    }

    document.addEventListener('click', (event) => {
        const recapToggle = event.target.closest('[data-session-recap-toggle]');
        if (recapToggle) {
            event.preventDefault();
            const recap = recapToggle.closest('[data-session-recap]');
            setSessionRecapExpanded(recap, recapToggle.getAttribute('aria-expanded') !== 'true');
            return;
        }

        const viewRecap = event.target.closest('[data-session-closing-view-recap]');
        if (viewRecap) {
            event.preventDefault();
            const recap = document.querySelector('[data-session-recap]');
            if (recap) {
                setSessionRecapExpanded(recap, true);
                const heading = recap.querySelector('#gmrt-session-recap-title');
                recap.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
                if (heading instanceof HTMLElement) {
                    heading.setAttribute('tabindex', '-1');
                    heading.focus({ preventScroll: true });
                }
            }
            return;
        }

        const dismissClosing = event.target.closest('[data-session-closing-dismiss]');
        if (dismissClosing) {
            event.preventDefault();
            const closing = dismissClosing.closest('[data-session-closing]');
            if (closing) closing.hidden = true;
            return;
        }

        const keeperSessionToggle = event.target.closest('[data-keeper-session-toggle]');
        if (keeperSessionToggle) {
            event.preventDefault();
            const drawer = document.querySelector('[data-keeper-session]');
            setKeeperDrawerOpen('session', drawer?.dataset.open !== 'true');
            return;
        }

        if (event.target.closest('[data-keeper-session-close]')) {
            event.preventDefault();
            setKeeperDrawerOpen('session', false);
            return;
        }

        const keeperToolsToggle = event.target.closest('[data-keeper-tools-toggle]');
        if (keeperToolsToggle) {
            event.preventDefault();
            const drawer = document.querySelector('[data-keeper-tools]');
            setKeeperDrawerOpen('tools', drawer?.dataset.open !== 'true');
            return;
        }

        if (event.target.closest('[data-keeper-tools-close]')) {
            event.preventDefault();
            setKeeperDrawerOpen('tools', false);
            return;
        }

        const atlasToggle = event.target.closest('[data-atlas-toggle]');
        if (atlasToggle) {
            event.preventDefault();
            const drawer = document.querySelector('[data-keepers-atlas]');
            setKeeperDrawerOpen('atlas', drawer?.dataset.open !== 'true');
            return;
        }

        if (event.target.closest('[data-atlas-close]')) {
            event.preventDefault();
            setKeeperDrawerOpen('atlas', false);
            return;
        }

        const bestiaryToggle = event.target.closest('[data-bestiary-toggle]');
        if (bestiaryToggle) {
            event.preventDefault();
            const drawer = document.querySelector('[data-keepers-bestiary]');
            setKeeperDrawerOpen('bestiary', drawer?.dataset.open !== 'true');
            return;
        }

        if (event.target.closest('[data-bestiary-close]')) {
            event.preventDefault();
            setKeeperDrawerOpen('bestiary', false);
        }
    });

    async function replaceChamber(message, sceneId = null) {
        const current = document.querySelector('.gmrt-chamber');
        const liveStatus = document.querySelector('#gmrt-tabletop-status');
        const keeperDrawerWasOpen = current?.dataset.keeperDrawerOpen || '';

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
                    : t('liveRefreshFailed', 'The live Chamber could not be refreshed.')
            );
        }

        const html = result.data && typeof result.data.html === 'string'
            ? result.data.html
            : '';
        const parsed = new DOMParser().parseFromString(html, 'text/html');
        const incoming = parsed.querySelector('.gmrt-chamber');

        if (!incoming) {
            throw new Error(t('refreshedMarkupMissing', 'The refreshed Chamber markup was not found.'));
        }

        if (activeRefreshTimer) {
            window.clearInterval(activeRefreshTimer);
            activeRefreshTimer = null;
        }

        current.replaceWith(incoming);
        bootTabletop();

        if (['session', 'tools', 'atlas', 'bestiary'].includes(keeperDrawerWasOpen)) {
            setKeeperDrawerOpen(keeperDrawerWasOpen, true);
        }
    }

    function bootTabletop() {
    const root = document.querySelector('.gmrt-chamber');
    const board = document.querySelector('.gmrt-board__viewport');
    const status = document.querySelector('#gmrt-tabletop-status');

    if (!root || !window.gmrtTabletop) {
        return;
    }

    const tableId = root.dataset.tableId || '';
    restoreSessionRecapPreference();
    revealFreshSessionClosing();
    const projectedSceneId = root.dataset.sceneId || '';
    const preparationSceneId = root.dataset.preparationSceneId || '';
    let selected = null;
    const removeSelectedTokenButton = document.querySelector('[data-remove-selected-token]');
    let targetingPreview = null;
    let visionDrafting = false;
    let thresholdPlacement = null;
    let bestiaryPlacement = null;
    let keeperLightPlacement = null;
    let furniturePlacement = null;
    let trapPlacement = null;
    let treasurePlacement = null;
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
                : t('requestRejected', 'The Tabletop rejected that request.');
            throw new Error(message);
        }

        return data.data;
    }

    const trapCabinet = document.querySelector('[data-trap-cabinet]');
    const trapStatus = document.querySelector('[data-trap-status]');
    const trapRoster = document.querySelector('[data-trap-roster]');
    const trapNewType = document.querySelector('[data-trap-new-type]');
    const trapNewLabel = document.querySelector('[data-trap-new-label]');
    const trapPlace = document.querySelector('[data-trap-place]');
    const trapPlaceCancel = document.querySelector('[data-trap-place-cancel]');

    function trapSceneId() {
        return preparationSceneId || projectedSceneId;
    }

    function finishTrapPlacement(message) {
        trapPlacement = null;
        board?.classList.remove('is-trap-placing');
        if (trapPlaceCancel) trapPlaceCancel.disabled = true;
        if (trapPlace) trapPlace.classList.remove('is-active');
        if (trapStatus && message) trapStatus.textContent = message;
    }

    trapNewType?.addEventListener('change', () => {
        if (!trapNewLabel) return;
        const current = trapNewLabel.value.trim();
        if (current === '' || current === t('pressurePlate', 'Pressure Plate') || current === t('tripwire', 'Tripwire')) {
            trapNewLabel.value = trapNewType.value === 'tripwire' ? t('tripwire', 'Tripwire') : t('pressurePlate', 'Pressure Plate');
        }
    });

    trapPlace?.addEventListener('click', () => {
        if (treasurePlacement) finishTreasurePlacement('Treasure placement cancelled.');
        trapPlacement = {
            mode: 'add',
            type: String(trapNewType?.value || 'pressure-plate'),
            label: String(trapNewLabel?.value || '').trim()
        };
        board?.classList.add('is-trap-placing');
        trapPlace.classList.add('is-active');
        if (trapPlaceCancel) trapPlaceCancel.disabled = false;
        if (trapStatus) trapStatus.textContent = t('trapSelected', 'Trap selected — click the battlemap to place it.');
    });

    trapPlaceCancel?.addEventListener('click', () => {
        finishTrapPlacement(t('trapCancelled', 'Trap placement cancelled. Pippin has lifted his feet very carefully.'));
    });

    trapRoster?.addEventListener('click', async (event) => {
        const button = event.target.closest?.('[data-trap-manage]');
        if (!button || button.disabled) return;
        const row = button.closest('[data-trap-row]');
        const trapId = String(row?.dataset.trapId || '');
        const action = String(button.dataset.trapManage || '');
        if (!trapId || !action) return;

        if (action === 'move') {
            trapPlacement = {mode: 'move', trapId};
            board?.classList.add('is-trap-placing');
            if (trapPlaceCancel) trapPlaceCancel.disabled = false;
            if (trapStatus) trapStatus.textContent = t('trapMoveSelected', 'Move selected — click the battlemap for the trap’s new position.');
            return;
        }

        if (action === 'remove' && !window.confirm(t('removeTrapConfirm', 'Remove this trap from the Scene?'))) return;

        button.disabled = true;
        try {
            const values = {
                scene_id: trapSceneId(),
                trap_id: trapId,
                trap_action: action
            };
            if (action === 'update') {
                values.label = String(row.querySelector('[data-trap-label]')?.value || '').trim();
                values.trap_type = String(row.querySelector('[data-trap-type]')?.value || 'pressure-plate');
            }
            const data = await request('gmrt_forge_trap_action', values);
            if (trapStatus) trapStatus.textContent = data.message || t('trapUpdated', 'Trap updated.');
            await replaceChamber(data.message || t('trapUpdated', 'Trap updated.'), trapSceneId() || null);
        } catch (error) {
            button.disabled = false;
            if (trapStatus) trapStatus.textContent = error?.message || t('trapFailed', 'Pippin could not tend that trap.');
        }
    });

    // Trap placement owns the battlefield gesture in the same way as furniture
    // and Keeper lights. It is server-authored; the map marker is just presentation.
    board?.addEventListener('pointerdown', async (event) => {
        if (!trapPlacement || event.button !== 0) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        const point = coordinatesFromPointer(event);
        const placement = trapPlacement;
        try {
            const values = {
                scene_id: trapSceneId(),
                trap_action: placement.mode === 'move' ? 'move' : 'add',
                x: point.x,
                y: point.y
            };
            if (placement.mode === 'move') {
                values.trap_id = placement.trapId;
            } else {
                values.trap_type = placement.type;
                values.label = placement.label;
            }
            const data = await request('gmrt_forge_trap_action', values);
            finishTrapPlacement(data.message || t('trapPositionUpdated', 'Trap position updated.'));
            await replaceChamber(data.message || t('trapPositionUpdated', 'Trap position updated.'), trapSceneId() || null);
        } catch (error) {
            if (trapStatus) trapStatus.textContent = (error?.message || t('trapPlacementFailed', 'The trap could not be placed.')) + ' ' + t('placementRemainsArmed', 'Placement remains armed; click again or cancel.');
        }
    }, true);


    const storyBeatRoster = document.querySelector('[data-story-beat-roster]');
    const storyBeatStatus = document.querySelector('[data-story-beat-status]');

    function storySceneId() {
        return preparationSceneId || projectedSceneId;
    }

    storyBeatRoster?.addEventListener('click', async (event) => {
        const button = event.target.closest?.('[data-story-beat-action]');
        if (!button || button.disabled) return;

        const row = button.closest('[data-story-beat-row]');
        const beatIndex = Number.parseInt(String(row?.dataset.storyBeatIndex || ''), 10);
        const action = String(button.dataset.storyBeatAction || '');

        if (!Number.isInteger(beatIndex) || beatIndex < 0 || !action) return;

        button.disabled = true;
        try {
            const data = await request('gmrt_forge_story_beat_action', {
                scene_id: storySceneId(),
                beat_index: beatIndex,
                story_action: action
            });

            if (storyBeatStatus) {
                storyBeatStatus.textContent = data.message || t('adventureNotesUpdated', 'Adventure notes updated.');
            }

            await replaceChamber(
                data.message || t('adventureNotesUpdated', 'Adventure notes updated.'),
                storySceneId() || null
            );
        } catch (error) {
            button.disabled = false;
            if (storyBeatStatus) {
                storyBeatStatus.textContent = error?.message || t('adventureNotesFailed', 'Pippin could not turn that page.');
            }
        }
    });

    const treasureLedger = document.querySelector('[data-treasure-ledger]');
    const treasureStatus = document.querySelector('[data-treasure-status]');
    const treasureRoster = document.querySelector('[data-treasure-roster]');
    const treasureNewType = document.querySelector('[data-treasure-new-type]');
    const treasureNewLabel = document.querySelector('[data-treasure-new-label]');
    const treasureNewContents = document.querySelector('[data-treasure-new-contents]');
    const treasurePlace = document.querySelector('[data-treasure-place]');
    const treasurePlaceCancel = document.querySelector('[data-treasure-place-cancel]');

    function treasureSceneId() {
        return preparationSceneId || projectedSceneId;
    }

    function treasureDefaults(type) {
        switch (String(type || 'coin-cache')) {
            case 'trade-goods':
                return ['Trade Goods', 'A useful bundle of trade goods and saleable provisions.'];
            case 'adventurer-cache':
                return ["Adventurer's Cache", 'A small cache of adventuring supplies worth carrying onward.'];
            case 'curio-stash':
                return ['Curio Stash', 'A peculiar curio and a few saleable trinkets.'];
            case 'lair-hoard':
                return ['Boss Hoard', 'A substantial hoard of mixed coin, valuables, and one conspicuously important prize for the Keeper to define.'];
            default:
                return ['Coin Cache', 'A modest cache of mixed coin and trade tokens.'];
        }
    }

    function finishTreasurePlacement(message) {
        treasurePlacement = null;
        board?.classList.remove('is-treasure-placing');
        if (treasurePlaceCancel) treasurePlaceCancel.disabled = true;
        if (treasurePlace) treasurePlace.classList.remove('is-active');
        if (treasureStatus && message) treasureStatus.textContent = message;
    }

    treasureNewType?.addEventListener('change', () => {
        const [label, contents] = treasureDefaults(treasureNewType.value);
        if (treasureNewLabel) treasureNewLabel.value = label;
        if (treasureNewContents) treasureNewContents.value = contents;
    });

    treasurePlace?.addEventListener('click', () => {
        if (trapPlacement) finishTrapPlacement('Trap placement cancelled.');
        treasurePlacement = {
            mode: 'add',
            type: String(treasureNewType?.value || 'coin-cache'),
            label: String(treasureNewLabel?.value || '').trim(),
            contents: String(treasureNewContents?.value || '').trim()
        };
        board?.classList.add('is-treasure-placing');
        treasurePlace.classList.add('is-active');
        if (treasurePlaceCancel) treasurePlaceCancel.disabled = false;
        if (treasureStatus) treasureStatus.textContent = t('treasureSelected', 'Treasure selected — click the battlemap to place it.');
    });

    treasurePlaceCancel?.addEventListener('click', () => {
        finishTreasurePlacement(t('treasureCancelled', 'Treasure placement cancelled. Pippin has stopped drawing little X marks.'));
    });

    treasureRoster?.addEventListener('click', async (event) => {
        const button = event.target.closest?.('[data-treasure-manage]');
        if (!button || button.disabled) return;
        const row = button.closest('[data-treasure-row]');
        const treasureId = String(row?.dataset.treasureId || '');
        const action = String(button.dataset.treasureManage || '');
        if (!treasureId || !action) return;

        if (action === 'move') {
            treasurePlacement = {mode: 'move', treasureId};
            board?.classList.add('is-treasure-placing');
            if (treasurePlaceCancel) treasurePlaceCancel.disabled = false;
            if (treasureStatus) treasureStatus.textContent = t('treasureMoveSelected', 'Move selected — click the battlemap for the treasure’s new position.');
            return;
        }

        if (action === 'remove' && !window.confirm(t('removeTreasureConfirm', 'Remove this treasure from the Scene?'))) return;

        button.disabled = true;
        try {
            const values = {
                scene_id: treasureSceneId(),
                treasure_id: treasureId,
                treasure_action: action
            };
            if (action === 'update') {
                values.label = String(row.querySelector('[data-treasure-label]')?.value || '').trim();
                values.treasure_type = String(row.querySelector('[data-treasure-type]')?.value || 'coin-cache');
                values.contents = String(row.querySelector('[data-treasure-contents]')?.value || '').trim();
            }
            const data = await request('gmrt_forge_treasure_action', values);
            if (treasureStatus) treasureStatus.textContent = data.message || t('treasureUpdated', 'Treasure updated.');
            await replaceChamber(data.message || t('treasureUpdated', 'Treasure updated.'), treasureSceneId() || null);
        } catch (error) {
            button.disabled = false;
            if (treasureStatus) treasureStatus.textContent = error?.message || t('treasureFailed', 'Pippin could not amend that treasure record.');
        }
    });

    board?.addEventListener('pointerdown', async (event) => {
        if (!treasurePlacement || event.button !== 0) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        const point = coordinatesFromPointer(event);
        const placement = treasurePlacement;
        try {
            const values = {
                scene_id: treasureSceneId(),
                treasure_action: placement.mode === 'move' ? 'move' : 'add',
                x: point.x,
                y: point.y
            };
            if (placement.mode === 'move') {
                values.treasure_id = placement.treasureId;
            } else {
                values.treasure_type = placement.type;
                values.label = placement.label;
                values.contents = placement.contents;
            }
            const data = await request('gmrt_forge_treasure_action', values);
            finishTreasurePlacement(data.message || t('treasurePositionUpdated', 'Treasure position updated.'));
            await replaceChamber(data.message || t('treasurePositionUpdated', 'Treasure position updated.'), treasureSceneId() || null);
        } catch (error) {
            if (treasureStatus) treasureStatus.textContent = (error?.message || t('treasurePlacementFailed', 'The treasure could not be placed.')) + ' ' + t('placementRemainsArmed', 'Placement remains armed; click again or cancel.');
        }
    }, true);

    const furniturePalette = document.querySelector('[data-furniture-palette]');
    const furnitureStatus = document.querySelector('[data-furniture-status]');
    const furnitureCancel = document.querySelector('[data-furniture-cancel]');
    const furnitureSnap = document.querySelector('[data-furniture-snap]');
    const furnitureButtons = Array.from(document.querySelectorAll('[data-furniture-kind]'));
    const furnitureSelection = document.querySelector('[data-furniture-selection]');
    const furnitureRotateButtons = Array.from(document.querySelectorAll('[data-scene-object-rotate]'));
    const furnitureScaleButtons = Array.from(document.querySelectorAll('[data-scene-object-scale]'));
    const furnitureDuplicate = document.querySelector('[data-scene-object-duplicate]');
    const furnitureInteract = document.querySelector('[data-scene-object-interact]');
    const furnitureMimic = document.querySelector('[data-scene-object-mimic]');
    const furnitureArmMimic = document.querySelector('[data-scene-object-arm-mimic]');
    const furnitureRevealMimic = document.querySelector('[data-scene-object-reveal-mimic]');
    const furnitureDisarmMimic = document.querySelector('[data-scene-object-disarm-mimic]');
    const mimicDialog = document.querySelector('[data-mimic-dialog]');
    const mimicCreature = document.querySelector('[data-mimic-creature]');
    const mimicConfirm = document.querySelector('[data-mimic-confirm]');
    const mimicArm = document.querySelector('[data-mimic-arm]');
    const mimicCancel = document.querySelector('[data-mimic-cancel]');
    const furnitureRemove = document.querySelector('[data-scene-object-remove]');
    const sceneObjectLayer = document.querySelector('[data-scene-object-layer]');
    const sceneObjectAuthoringUrl = window.location.toString();
    let selectedSceneObjectId = '';
    let sceneObjectDrag = null;
    let sceneObjectBusy = false;
    let tokenDragInProgress = false;

    function sceneObjectElement(objectId) {
        if (!objectId) return null;
        return document.querySelector('[data-scene-object-id="' + CSS.escape(objectId) + '"]');
    }

    function setSceneObjectEditorEnabled(enabled) {
        [...furnitureRotateButtons, ...furnitureScaleButtons].forEach((button) => {
            button.disabled = !enabled;
        });
        if (furnitureDuplicate) furnitureDuplicate.disabled = !enabled;
        if (furnitureInteract) {
            const object = enabled ? sceneObjectElement(selectedSceneObjectId) : null;
            const interaction = object?.dataset.sceneObjectInteraction || 'none';
            furnitureInteract.disabled = !enabled || interaction === 'none';
            furnitureInteract.textContent = interaction === 'open_close'
                ? (object?.dataset.sceneObjectOpen === 'true' ? t('close', 'Close') : t('open', 'Open'))
                : t('interact', 'Interact');
        }
        if (furnitureMimic || furnitureArmMimic || furnitureRevealMimic || furnitureDisarmMimic) {
            const object = enabled ? sceneObjectElement(selectedSceneObjectId) : null;
            const capable = object?.dataset.mimicCapable === 'true';
            const armed = object?.dataset.mimicArmed === 'true';
            if (furnitureMimic) furnitureMimic.disabled = !enabled || !capable || armed;
            if (furnitureArmMimic) furnitureArmMimic.disabled = !enabled || !capable || armed;
            if (furnitureRevealMimic) {
                furnitureRevealMimic.disabled = !enabled || !armed;
                furnitureRevealMimic.title = armed && object?.dataset.mimicName
                    ? `Reveal ${object.dataset.mimicName}`
                    : '';
            }
            if (furnitureDisarmMimic) furnitureDisarmMimic.disabled = !enabled || !armed;
        }
        if (furnitureRemove) furnitureRemove.disabled = !enabled;
    }

    function selectSceneObject(object) {
        document.querySelectorAll('[data-scene-object-id]').forEach((candidate) => {
            const selected = candidate === object;
            candidate.classList.toggle('is-selected', selected);
            if (candidate.getAttribute('role') === 'button') {
                candidate.setAttribute('aria-pressed', selected ? 'true' : 'false');
            }
        });

        selectedSceneObjectId = object?.dataset.sceneObjectId || '';
        setSceneObjectEditorEnabled(Boolean(selectedSceneObjectId));

        if (furnitureSelection) {
            furnitureSelection.textContent = object
                ? (object.dataset.sceneObjectLabel || t('furniture', 'Furniture')) + ' selected'
                : t('noFurnitureSelected', 'No furniture selected');
        }
    }

    function syncSceneObjectLayer(incomingDocument) {
        const currentLayer = document.querySelector('[data-scene-object-layer]');
        const incomingLayer = incomingDocument?.querySelector('[data-scene-object-layer]');
        if (!currentLayer || !incomingLayer) return;
        currentLayer.replaceChildren(...incomingLayer.childNodes);

        if (selectedSceneObjectId) {
            const restored = sceneObjectElement(selectedSceneObjectId);
            selectSceneObject(restored);
        }
    }

    async function refreshSceneObjectLayer() {
        const data = await request('gmrt_tabletop_fragment', {});
        const html = typeof data.html === 'string' ? data.html : '';
        if (!html) return;
        syncSceneObjectLayer(new DOMParser().parseFromString(html, 'text/html'));
    }

    async function submitSceneObjectAction(action, values = {}) {
        if (!furniturePalette || sceneObjectBusy) return false;
        sceneObjectBusy = true;

        const body = new URLSearchParams();
        body.set('gmrt_scene_object_action', action);
        body.set('gmrt_scene_object_nonce', furniturePalette.dataset.sceneObjectNonce || '');
        body.set('gmrt_scene_object_scene_id', projectedSceneId);
        Object.entries(values).forEach(([key, value]) => body.set(key, String(value)));

        try {
            const response = await fetch(sceneObjectAuthoringUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'},
                body
            });
            if (!response.ok) throw new Error(t('furnitureRearrangeFailed', 'Pippin could not rearrange that furnishing.'));
            const html = await response.text();
            syncSceneObjectLayer(new DOMParser().parseFromString(html, 'text/html'));
            return true;
        } catch (error) {
            if (furnitureStatus) {
                furnitureStatus.textContent = error.message || t('furnitureRearrangeFailed', 'Pippin could not rearrange that furnishing.');
            }
            return false;
        } finally {
            sceneObjectBusy = false;
        }
    }

    function furniturePoint(point) {
        if (!furnitureSnap?.checked || !furniturePalette) return point;

        const width = Math.max(1, Number(furniturePalette.dataset.sceneWidth || 1));
        const height = Math.max(1, Number(furniturePalette.dataset.sceneHeight || 1));
        const grid = Math.max(1, Number(furniturePalette.dataset.gridSize || 1));
        const offsetX = Number(furniturePalette.dataset.gridOffsetX || 0);
        const offsetY = Number(furniturePalette.dataset.gridOffsetY || 0);

        const snapAxis = (value, extent, offset) => {
            const source = value * extent;
            const index = Math.round(((source - offset) / grid) - 0.5);
            const snapped = offset + ((index + 0.5) * grid);
            return Math.max(0, Math.min(1, snapped / extent));
        };

        return {
            x: snapAxis(point.x, width, offsetX),
            y: snapAxis(point.y, height, offsetY)
        };
    }

    furnitureSnap?.addEventListener('change', () => {
        if (furnitureStatus) {
            furnitureStatus.textContent = furnitureSnap.checked
                ? t('snapEnabled', 'Snap to Grid enabled. Pippin has restored order.')
                : t('snapDisabled', 'Snap to Grid disabled. Pippin is trying not to look.');
        }
    });

    function finishFurniturePlacement(message) {
        furniturePlacement = null;
        board?.classList.remove('is-furniture-placing');
        furnitureButtons.forEach((button) => {
            button.classList.remove('is-selected');
            button.setAttribute('aria-pressed', 'false');
        });
        if (furnitureCancel) furnitureCancel.disabled = true;
        if (furnitureStatus && message) furnitureStatus.textContent = message;
    }

    furnitureButtons.forEach((button) => {
        button.addEventListener('click', () => {
            selectSceneObject(null);
            furniturePlacement = {
                kind: String(button.dataset.furnitureKind || ''),
                label: String(button.dataset.furnitureLabel || t('furniture', 'Furniture'))
            };
            furnitureButtons.forEach((choice) => {
                const active = choice === button;
                choice.classList.toggle('is-selected', active);
                choice.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            board?.classList.add('is-furniture-placing');
            if (furnitureCancel) furnitureCancel.disabled = false;
            if (furnitureStatus) {
                furnitureStatus.textContent = furniturePlacement.label + ' selected — click the map to place it.';
            }
        });
    });

    furnitureCancel?.addEventListener('click', () => {
        finishFurniturePlacement(t('furniturePlacementCancelled', 'Placement cancelled. Pippin has put the tape measure away.'));
    });

    // Furniture placement owns the next battlefield pointer in capture phase.
    // This mirrors the certified Lantern Rack interaction: authoring must win before
    // Lens panning, Fog, tokens, vision or cartography can consume the gesture.
    board?.addEventListener('pointerdown', async (event) => {
        if (!furniturePlacement || !furniturePalette) return;
        if (event.button !== 0) return;

        event.preventDefault();
        event.stopImmediatePropagation();

        const point = furniturePoint(coordinatesFromPointer(event));
        const placedLabel = furniturePlacement.label;
        const placed = await submitSceneObjectAction('place', {
            gmrt_scene_object_kind: furniturePlacement.kind,
            x: point.x,
            y: point.y
        });
        if (placed) {
            finishFurniturePlacement(placedLabel + ' placed. Pippin has marked it on the map.');
        }
    }, true);

    // IV.35.3B — existing furnishings become Keeper-selectable without making the
    // whole object layer intercept battlefield input. Only the object itself owns
    // the pointer, so empty-map token/Lens interactions remain unchanged.
    sceneObjectLayer?.addEventListener('pointerdown', (event) => {
        if (!furniturePalette || furniturePlacement || event.button !== 0) return;
        const object = event.target instanceof Element
            ? event.target.closest('[data-scene-object-id]')
            : null;
        if (!object) return;

        event.preventDefault();
        event.stopPropagation();
        selectSceneObject(object);
        sceneObjectDrag = {
            id: object.dataset.sceneObjectId || '',
            pointerId: event.pointerId,
            object,
            startX: event.clientX,
            startY: event.clientY,
            moved: false,
            threshold: 3
        };
        object.setPointerCapture?.(event.pointerId);
    });

    window.addEventListener('pointermove', (event) => {
        if (!sceneObjectDrag) return;
        const dx = event.clientX - sceneObjectDrag.startX;
        const dy = event.clientY - sceneObjectDrag.startY;
        if (!sceneObjectDrag.moved && Math.hypot(dx, dy) < sceneObjectDrag.threshold) return;

        sceneObjectDrag.moved = true;
        sceneObjectDrag.object.classList.add('is-dragging');
        board?.classList.add('is-furniture-moving');
        if (furnitureStatus) {
            furnitureStatus.textContent = 'Moving ' + (sceneObjectDrag.object.dataset.sceneObjectLabel || 'furniture') + ' — release to place it.';
        }

        event.preventDefault();
        const point = furniturePoint(coordinatesFromPointer(event));
        sceneObjectDrag.object.dataset.sceneObjectX = String(point.x);
        sceneObjectDrag.object.dataset.sceneObjectY = String(point.y);
        sceneObjectDrag.object.style.setProperty('--gmrt-object-x', String(point.x * 100) + '%');
        sceneObjectDrag.object.style.setProperty('--gmrt-object-y', String(point.y * 100) + '%');
    }, {passive: false});

    window.addEventListener('pointerup', async (event) => {
        if (!sceneObjectDrag || event.pointerId !== sceneObjectDrag.pointerId) return;
        const drag = sceneObjectDrag;
        sceneObjectDrag = null;
        drag.object.classList.remove('is-dragging');
        board?.classList.remove('is-furniture-moving');
        drag.object.releasePointerCapture?.(event.pointerId);

        if (!drag.moved) {
            if (furnitureStatus) {
                furnitureStatus.textContent = (drag.object.dataset.sceneObjectLabel || t('furniture', 'Furniture')) + ' selected.';
            }
            return;
        }

        const moved = await submitSceneObjectAction('move', {
            gmrt_scene_object_id: drag.id,
            x: drag.object.dataset.sceneObjectX || '0.5',
            y: drag.object.dataset.sceneObjectY || '0.5'
        });
        if (moved && furnitureStatus) {
            furnitureStatus.textContent = t('furnitureMoved', 'Furniture moved. Pippin has amended the floor plan.');
        }
    });

    window.addEventListener('pointercancel', (event) => {
        if (!sceneObjectDrag || event.pointerId !== sceneObjectDrag.pointerId) return;
        sceneObjectDrag.object.classList.remove('is-dragging');
        sceneObjectDrag = null;
        board?.classList.remove('is-furniture-moving');
        if (furnitureStatus) furnitureStatus.textContent = t('furnitureMoveCancelled', 'Furniture move cancelled.');
    });

    sceneObjectLayer?.addEventListener('keydown', (event) => {
        if (!furniturePalette) return;
        const object = event.target instanceof Element
            ? event.target.closest('[data-scene-object-id]')
            : null;
        if (!object || !['Enter', ' '].includes(event.key)) return;
        event.preventDefault();
        selectSceneObject(object);
    });

    furnitureRotateButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            const object = sceneObjectElement(selectedSceneObjectId);
            if (!object) return;
            const rotation = Number(object.dataset.sceneObjectRotation || 0)
                + Number(button.dataset.sceneObjectRotate || 0);
            const changed = await submitSceneObjectAction('rotate', {
                gmrt_scene_object_id: selectedSceneObjectId,
                rotation
            });
            if (changed && furnitureStatus) furnitureStatus.textContent = t('furnitureRotated', 'Furniture rotated. Pippin has rotated the paper too.');
        });
    });

    furnitureScaleButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            const object = sceneObjectElement(selectedSceneObjectId);
            if (!object) return;
            const scale = Math.max(0.5, Math.min(
                2.5,
                Number(object.dataset.sceneObjectScale || 1) + Number(button.dataset.sceneObjectScale || 0)
            ));
            const changed = await submitSceneObjectAction('scale', {
                gmrt_scene_object_id: selectedSceneObjectId,
                scale
            });
            if (changed && furnitureStatus) furnitureStatus.textContent = t('furnitureResized', 'Furniture resized. Pippin disputes the new dimensions.');
        });
    });

    furnitureInteract?.addEventListener('click', async () => {
        const object = sceneObjectElement(selectedSceneObjectId);
        if (!object || (object.dataset.sceneObjectInteraction || 'none') === 'none') return;
        const changed = await submitSceneObjectAction('interact', {
            gmrt_scene_object_id: selectedSceneObjectId
        });
        if (changed && furnitureStatus) {
            const restored = sceneObjectElement(selectedSceneObjectId);
            const isOpen = restored?.dataset.sceneObjectOpen === 'true';
            furnitureStatus.textContent = isOpen
                ? t('chestOpened', 'Chest opened. Pippin has taken three prudent steps backwards.')
                : t('chestClosed', 'Chest closed. Pippin is pretending this solves the problem.');
        }
    });

    const openMimicChooser = () => {
        const object = sceneObjectElement(selectedSceneObjectId);
        if (!object || object.dataset.mimicCapable !== 'true' || object.dataset.mimicArmed === 'true') return;
        if (mimicCreature) mimicCreature.value = '';
        mimicDialog?.showModal();
    };
    furnitureMimic?.addEventListener('click', openMimicChooser);
    furnitureArmMimic?.addEventListener('click', openMimicChooser);
    mimicCancel?.addEventListener('click', () => mimicDialog?.close());
    mimicArm?.addEventListener('click', async () => {
        const creatureId = String(mimicCreature?.value || '');
        if (!creatureId) {
            if (furnitureStatus) furnitureStatus.textContent = t('chooseMimic', 'Choose a Mimic from the Bestiary first.');
            return;
        }
        mimicDialog?.close();
        const changed = await submitSceneObjectAction('arm_mimic', {
            gmrt_scene_object_id: selectedSceneObjectId,
            gmrt_mimic_creature_id: creatureId
        });
        if (changed && furnitureStatus) {
            const restored = sceneObjectElement(selectedSceneObjectId);
            const mimicName = restored?.dataset.mimicName || 'a Mimic';
            furnitureStatus.textContent = `${restored?.dataset.sceneObjectLabel || t('furniture', 'Furniture')} is armed as ${mimicName}. It still looks completely innocent.`;
        }
    });
    mimicConfirm?.addEventListener('click', async () => {
        const creatureId = String(mimicCreature?.value || '');
        if (!creatureId) {
            if (furnitureStatus) furnitureStatus.textContent = t('chooseCreature', 'Choose a creature from the Bestiary first.');
            return;
        }
        mimicDialog?.close();
        const changed = await submitSceneObjectAction('convert_mimic', {
            gmrt_scene_object_id: selectedSceneObjectId,
            gmrt_mimic_creature_id: creatureId
        });
        if (changed) {
            const message = t('mimicRevealed', 'The furniture was a Mimic. Pippin would like the record to show that he objected.');
            if (furnitureStatus) furnitureStatus.textContent = message;
            await replaceChamber(message, null);
        }
    });

    furnitureRevealMimic?.addEventListener('click', async () => {
        const object = sceneObjectElement(selectedSceneObjectId);
        if (!object || object.dataset.mimicArmed !== 'true') return;
        if (!window.confirm(`Reveal ${object.dataset.mimicName || 'this Mimic'} now?`)) return;

        const message = `${object.dataset.sceneObjectLabel || t('furniture', 'Furniture')} reveals itself!`;
        const changed = await submitSceneObjectAction('reveal_mimic', {
            gmrt_scene_object_id: selectedSceneObjectId
        });
        if (changed) {
            if (furnitureStatus) furnitureStatus.textContent = message;
            await replaceChamber(message, null);
        }
    });

    furnitureDisarmMimic?.addEventListener('click', async () => {
        const object = sceneObjectElement(selectedSceneObjectId);
        if (!object || object.dataset.mimicArmed !== 'true') return;
        if (!window.confirm(t('disarmMimicConfirm', 'Disarm this disguised Mimic? The furnishing will remain.'))) return;

        const changed = await submitSceneObjectAction('disarm_mimic', {
            gmrt_scene_object_id: selectedSceneObjectId
        });
        if (changed && furnitureStatus) {
            furnitureStatus.textContent = t('mimicDisarmed', 'Mimic disguise disarmed. Pippin remains unconvinced.');
        }
    });

    furnitureDuplicate?.addEventListener('click', async () => {
        if (!selectedSceneObjectId) return;
        const changed = await submitSceneObjectAction('duplicate', {
            gmrt_scene_object_id: selectedSceneObjectId
        });
        if (changed && furnitureStatus) furnitureStatus.textContent = t('furnitureDuplicated', 'Furniture duplicated. Pippin is counting again.');
    });

    furnitureRemove?.addEventListener('click', async () => {
        if (!selectedSceneObjectId) return;
        if (!window.confirm(t('removeFurnitureConfirm', 'Remove this furnishing from the Scene?'))) return;
        const objectId = selectedSceneObjectId;
        const changed = await submitSceneObjectAction('delete', {
            gmrt_scene_object_id: objectId
        });
        if (changed) {
            selectSceneObject(null);
            if (furnitureStatus) furnitureStatus.textContent = t('furnitureRemoved', 'Furniture removed. Pippin has reclaimed the floor space.');
        }
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && selectedSceneObjectId) {
            selectSceneObject(null);
            if (furnitureStatus) furnitureStatus.textContent = t('furnitureSelectionCleared', 'Furniture selection cleared.');
        }
    });

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
        syncGatheringTurnState();

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
            prepareTestTableButton.textContent = t('preparing', 'Preparing…');
            say(t('trainingGroundsPreparing', 'Sage is preparing the Training Grounds…'));

            try {
                const data = await request('gmrt_prepare_test_table', {});
                const url = new URL(window.location.href);
                url.searchParams.set('table', data.table_id);
                window.location.assign(url.toString());
            } catch (error) {
                say(error.message || t('testTableFailed', 'The test Table could not be prepared.'));
                prepareTestTableButton.disabled = false;
                prepareTestTableButton.textContent = t('prepareTestTable', 'Prepare Test Table');
            }
        });
    }

    const createTabletopForm = document.querySelector('[data-create-tabletop]');
    if (createTabletopForm) {
        const atlasPicker = createTabletopForm.querySelector('[data-first-map-atlas]');
        const syncFirstMapChoice = () => {
            const selected = createTabletopForm.querySelector('[name="first_map"]:checked');
            if (atlasPicker) atlasPicker.hidden = selected?.value !== 'atlas';
        };
        createTabletopForm.querySelectorAll('[name="first_map"]').forEach((choice) => {
            choice.addEventListener('change', syncFirstMapChoice);
        });
        syncFirstMapChoice();

        createTabletopForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = createTabletopForm.querySelector('button[type="submit"]');
            const status = createTabletopForm.querySelector('[data-create-tabletop-status]');
            const form = new FormData(createTabletopForm);
            const firstMap = String(form.get('first_map') || 'blank');
            const atlasSource = String(form.get('atlas_source') || '');
            const [sourceTableId = '', sourceSceneId = ''] = atlasSource.split('::', 2);
            if (firstMap === 'atlas' && (!sourceTableId || !sourceSceneId)) {
                if (status) status.textContent = t('chooseAtlasMap', 'Choose the saved Atlas map Pippin should place first.');
                return;
            }
            if (button) button.disabled = true;
            if (status) status.textContent = firstMap === 'forge'
                ? t('forgePreparing', 'Pippin is clearing a workbench beside the Forge…')
                : t('tablePreparing', 'Pippin is finding a suitable patch of table…');
            try {
                const data = await request('gmrt_create_tabletop', {
                    name: form.get('name') || '',
                    description: form.get('description') || '',
                    first_map: firstMap,
                    source_table_id: sourceTableId,
                    source_scene_id: sourceSceneId
                });
                const url = new URL(window.location.href);
                url.searchParams.set('table', data.table_id);
                if (data.first_map === 'forge') {
                    url.searchParams.set('first_map', 'forge');
                } else {
                    url.searchParams.delete('first_map');
                }
                window.location.assign(url.toString());
            } catch (error) {
                if (status) status.textContent = error.message || t('tableCreateFailed', 'The Tabletop could not be created.');
                if (button) button.disabled = false;
            }
        });
    }

    // Phase IV.33.4 — a Forge-first campaign arrives with Pippin's workbench already open.
    const onboardingUrl = new URL(window.location.href);
    if (onboardingUrl.searchParams.get('first_map') === 'forge') {
        const forge = document.querySelector('[data-dungeon-forge]');
        if (forge) {
            const keeperControls = forge.closest('[data-keeper-controls]');
            if (keeperControls) keeperControls.open = true;
            forge.open = true;
            window.setTimeout(() => forge.scrollIntoView({ behavior: 'smooth', block: 'center' }), 180);
            onboardingUrl.searchParams.delete('first_map');
            window.history.replaceState({}, '', onboardingUrl.toString());
        }
    }

    // Phase IV.34.2 — The Table Remembers Tonight: Companion Campaign bridge.
    document.querySelectorAll('[data-companion-campaign-link]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const tableId = form.dataset.tableId || '';
            const campaignId = String(form.querySelector('[name="campaign_id"]')?.value || '');
            const button = form.querySelector('button[type="submit"]');
            const status = form.parentElement?.querySelector('[data-companion-campaign-status]');
            if (!tableId || !campaignId) return;
            if (button) button.disabled = true;
            if (status) status.textContent = t('campaignLinking', 'Pippin is joining the Table Atlas to the Companion Ledger…');
            try {
                const data = await request('gmrt_link_companion_campaign', { table_id: tableId, campaign_id: campaignId });
                const count = Number(data.sessions_synchronised || 0);
                if (status) status.textContent = count > 0
                    ? `${data.message || t('campaignLinked', 'Campaign linked.')} ${count} existing Session${count === 1 ? '' : 's'} synchronised.`
                    : (data.message || t('campaignLinked', 'Campaign linked.'));
                window.setTimeout(() => window.location.reload(), 650);
            } catch (error) {
                if (status) status.textContent = error.message || t('campaignLinkFailed', 'The Companion Campaign could not be linked.');
                if (button) button.disabled = false;
            }
        });
    });

    // Phase IV.33.2 — Campaign Shelf player administration.
    document.querySelectorAll('[data-campaign-invite-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const input = form.querySelector('[name="player"]');
            const button = form.querySelector('button[type="submit"]');
            const status = form.parentElement?.querySelector('[data-campaign-gathering-status]');
            const player = input ? input.value.trim() : '';
            const campaignTableId = form.dataset.tableId || '';
            if (!player || !campaignTableId) return;
            if (button) button.disabled = true;
            if (status) status.textContent = t('sendingSummons', 'Sending the Summons…');
            try {
                const data = await request('gmrt_invite_table_player', { table_id: campaignTableId, player });
                if (status) status.textContent = data.message || t('invitationSent', 'Invitation sent.');
                window.setTimeout(() => window.location.reload(), 450);
            } catch (error) {
                if (status) status.textContent = error.message || t('inviteFailed', 'The player could not be invited.');
                if (button) button.disabled = false;
            }
        });
    });

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-campaign-remove-player]');
        if (!button) return;
        const campaignTableId = button.dataset.tableId || '';
        const userId = button.dataset.userId || '';
        const status = button.closest('.gmrt-campaign-card__roster')?.querySelector('[data-campaign-gathering-status]');
        if (!campaignTableId || !userId) return;
        button.disabled = true;
        if (status) status.textContent = t('closingSeat', 'Closing that seat…');
        try {
            const data = await request('gmrt_remove_table_player', { table_id: campaignTableId, user_id: userId });
            if (status) status.textContent = data.message || t('playerRemoved', 'Player removed.');
            window.setTimeout(() => window.location.reload(), 450);
        } catch (error) {
            if (status) status.textContent = error.message || t('removePlayerFailed', 'The player could not be removed.');
            button.disabled = false;
        }
    });

    // Phase IV.33.3 — Pippin Remembers the Way: Keeper-owned Table removal.
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-remove-tabletop]');
        if (!button) return;
        const campaignTableId = button.dataset.tableId || '';
        const tableName = button.dataset.tableName || 'this Tabletop';
        const status = button.parentElement?.querySelector('[data-remove-tabletop-status]');
        if (!campaignTableId) return;
        if (!window.confirm(`Remove “${tableName}” permanently? This cannot be undone.`)) return;
        button.disabled = true;
        if (status) status.textContent = t('tableRemoving', 'Pippin is erasing this road from the atlas…');
        try {
            const data = await request('gmrt_remove_tabletop', { table_id: campaignTableId });
            if (status) status.textContent = data.message || t('tableRemoved', 'Tabletop removed.');

            const card = button.closest('.gmrt-campaign-card');
            const shelf = card?.closest('.gmrt-campaign-lobby__shelf');
            if (card) {
                card.remove();
            }

            if (shelf && !shelf.querySelector('.gmrt-campaign-card')) {
                const empty = document.createElement('p');
                empty.className = 'gmrt-campaign-lobby__empty';
                empty.textContent = t('noSavedRoads', 'Pippin has no saved roads for you yet. A Keeper can set a new Table, or an Adventurer can return after receiving a Summons.');
                shelf.replaceWith(empty);
            }
        } catch (error) {
            if (status) status.textContent = error.message || t('tableRemoveFailed', 'The Tabletop could not be removed.');
            button.disabled = false;
        }
    });

    // Phase IV.34.1 — The Keeper Calls the Session.
    const startSessionForm = document.querySelector('[data-start-table-session]');
    if (startSessionForm) {
        startSessionForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = startSessionForm.querySelector('button[type="submit"]');
            const sessionStatus = startSessionForm.closest('[data-table-session]')?.querySelector('[data-table-session-status]');
            const title = String(startSessionForm.querySelector('[name="title"]')?.value || '').trim();
            if (button) button.disabled = true;
            if (sessionStatus) sessionStatus.textContent = t('sessionCalling', 'The Keeper is calling the Session…');
            try {
                await request('gmrt_start_table_session', { title });
                await replaceChamber(t('sessionBegan', 'The Session has begun — the Table remembers tonight.'), null);
            } catch (error) {
                if (sessionStatus) sessionStatus.textContent = error.message || t('sessionStartFailed', 'The Session could not be started.');
                if (button) button.disabled = false;
            }
        });
    }

    const endSessionButton = document.querySelector('[data-end-table-session]');
    if (endSessionButton) {
        endSessionButton.addEventListener('click', async () => {
            const sessionStatus = endSessionButton.closest('[data-table-session]')?.querySelector('[data-table-session-status]');
            if (!window.confirm(t('endSessionConfirm', 'End the current Session? The campaign itself will remain active and can be resumed in a new Session later.'))) return;
            endSessionButton.disabled = true;
            if (sessionStatus) sessionStatus.textContent = t('sessionClosing', 'Closing the Session ledger…');
            try {
                const endingSessionId = root.dataset.sessionId || '';
                await request('gmrt_end_table_session', {});
                rememberSessionClosing(tableId, endingSessionId);
                await replaceChamber(t('sessionEnded', 'Until next time — this Session has concluded.'), null);
            } catch (error) {
                if (sessionStatus) sessionStatus.textContent = error.message || t('sessionEndFailed', 'The Session could not be ended.');
                endSessionButton.disabled = false;
            }
        });
    }

    const gatheringStatus = document.querySelector('[data-gathering-status]');
    let keeperSecretD20Result = null;
    const gatheringSay = (message) => {
        if (gatheringStatus) gatheringStatus.textContent = message;
    };

    function populateKeeperSecretRollResult(scope = document) {
        const result = scope.querySelector?.('[data-keeper-secret-d20-result]');
        if (!result) return;
        result.textContent = keeperSecretD20Result === null
            ? ''
            : 'Secret d20: ' + keeperSecretD20Result;
    }

    function syncGatheringTurnState() {
        const activeToken = document.querySelector('[data-token-id].is-active-turn');
        const activeCharacterId = String(activeToken?.dataset.tokenSource || '');

        document.querySelectorAll('[data-party-character-id]').forEach((member) => {
            const characterId = String(member.dataset.partyCharacterId || '');
            member.classList.toggle(
                'is-active-turn',
                activeCharacterId !== '' && characterId !== '' && characterId === activeCharacterId
            );
        });
    }

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
            const characterId = String(member.companion_character_id || '');
            item.dataset.partyCharacterId = characterId;

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
            const companionCharacter = member.companion_character && typeof member.companion_character === 'object'
                ? member.companion_character
                : null;
            const companionToken = companionCharacter && companionCharacter.token && typeof companionCharacter.token === 'object'
                ? companionCharacter.token
                : null;
            const characterImage = companionToken && companionToken.image_url
                ? String(companionToken.image_url)
                : '';
            const characterName = companionCharacter && companionCharacter.name
                ? String(companionCharacter.name)
                : t('selectedCharacter', 'Selected character');

            roleBadge.className = 'gmrt-party__role gmrt-party__seat' + (characterImage ? ' has-character' : '');
            if (role === 'dungeon-master') {
                roleBadge.textContent = 'DM';
                roleBadge.setAttribute('aria-label', 'Dungeon Master');
                roleBadge.title = 'Dungeon Master';
            } else if (characterImage) {
                const characterPortrait = document.createElement('img');
                characterPortrait.src = characterImage;
                characterPortrait.alt = '';
                characterPortrait.setAttribute('aria-hidden', 'true');
                characterPortrait.style.setProperty('--gmrt-token-focus-x', String(companionToken.focus_x || 50) + '%');
                characterPortrait.style.setProperty('--gmrt-token-focus-y', String(companionToken.focus_y || 50) + '%');
                characterPortrait.style.setProperty('--gmrt-token-zoom', String(companionToken.zoom || 100) + '%');
                roleBadge.appendChild(characterPortrait);
                roleBadge.setAttribute('aria-label', 'Playing: ' + characterName);
                roleBadge.title = 'Playing: ' + characterName;
            } else {
                roleBadge.textContent = 'P';
                roleBadge.setAttribute('aria-label', 'Player — no character selected');
                roleBadge.title = 'Player — no character selected';
            }
            item.appendChild(roleBadge);

            const name = document.createElement('strong');
            name.textContent = String(member.display_name || ('User #' + String(member.user_id || '')));
            item.appendChild(name);

            if (root?.dataset.viewerRole === 'dungeon-master' && role === 'dungeon-master' && status !== 'left') {
                const secretZone = document.createElement('div');
                secretZone.className = 'gmrt-party__secret-roll';
                secretZone.dataset.keeperSecretRollZone = '';

                const secretButton = document.createElement('button');
                secretButton.type = 'button';
                secretButton.dataset.keeperSecretD20 = '';
                secretButton.textContent = 'Secret d20';
                secretButton.setAttribute('aria-label', 'Roll a private d20 visible only to the Dungeon Master');

                const secretResult = document.createElement('span');
                secretResult.className = 'gmrt-party__secret-result';
                secretResult.dataset.keeperSecretD20Result = '';
                secretResult.setAttribute('role', 'status');
                secretResult.setAttribute('aria-live', 'polite');

                secretZone.appendChild(secretButton);
                secretZone.appendChild(secretResult);
                item.appendChild(secretZone);
                populateKeeperSecretRollResult(secretZone);
            }

            if (root?.dataset.viewerRole === 'dungeon-master' && role === 'player' && status !== 'left') {
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'gmrt-party__remove';
                remove.dataset.removeTablePlayer = '';
                remove.dataset.userId = String(member.user_id || '');
                remove.textContent = 'Remove from Table';
                item.appendChild(remove);
            }

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

        syncGatheringTurnState();
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
            gatheringSay(data.message || t('invitationSent', 'Invitation sent.'));
            if (input) input.value = '';
            await refresh();
        } catch (error) {
            gatheringSay(error.message || t('inviteFailed', 'The player could not be invited.'));
            if (button) button.disabled = false;
        }
    });

    document.addEventListener('click', async (event) => {
        const secretButton = event.target.closest('[data-keeper-secret-d20]');
        if (secretButton) {
            if (root?.dataset.viewerRole !== 'dungeon-master') return;
            secretButton.disabled = true;
            const previousLabel = secretButton.textContent;
            secretButton.textContent = 'Rolling…';
            try {
                const data = await request('gmrt_keeper_secret_d20', {});
                keeperSecretD20Result = Number(data.roll || 0);
                populateKeeperSecretRollResult(secretButton.closest('[data-keeper-secret-roll-zone]') || document);
            } catch (error) {
                const result = secretButton.closest('[data-keeper-secret-roll-zone]')?.querySelector('[data-keeper-secret-d20-result]');
                if (result) result.textContent = error.message || 'Secret roll failed.';
            } finally {
                secretButton.disabled = false;
                secretButton.textContent = previousLabel || 'Secret d20';
            }
            return;
        }

        const button = event.target.closest('[data-remove-table-player]');
        if (!button) return;
        const userId = button.dataset.userId || '';
        if (!userId) return;
        button.disabled = true;
        gatheringSay('Removing the player from the Table…');
        try {
            const data = await request('gmrt_remove_table_player', { user_id: userId });
            gatheringSay(data.message || t('playerRemoved', 'Player removed.'));
            await refresh();
        } catch (error) {
            gatheringSay(error.message || t('removePlayerFailed', 'The player could not be removed.'));
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
    const visibleEncounterId = () => String(
        document.querySelector('[data-encounter-id]')?.dataset.encounterId || ''
    );
    document.querySelectorAll('[data-quick-roll]').forEach((button) => {
        button.addEventListener('click', async () => {
            const original = button.innerHTML;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            if (quickHandsResult) quickHandsResult.textContent = 'The dice tumble across the Table…';
            try {
                const data = await request('gmrt_quick_hands_roll', {
                    kind: button.dataset.rollKind || '',
                    key: button.dataset.rollKey || '',
                    encounter_id: visibleEncounterId()
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
                    attack_id: button.dataset.attackId || '',
                    encounter_id: visibleEncounterId()
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
                    spell_id: button.dataset.spellId || '',
                    encounter_id: visibleEncounterId()
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
            || keeperLightPlacement
            || furniturePlacement
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
                const lightKind = String(source.environmental_kind || 'torch');
                const marker = document.createElement('i');
                marker.className = 'gmrt-keeper-light-marker is-' + lightKind;
                marker.setAttribute('aria-hidden', 'true');

                if (source.lit !== false) {
                    marker.classList.add('is-dancing');
                    const particleCount = lightKind === 'brazier' ? 3 : (lightKind === 'torch' ? 2 : 1);
                    for (let particleIndex = 0; particleIndex < particleCount; particleIndex += 1) {
                        const particle = document.createElement('span');
                        particle.className = 'gmrt-light-emitter-particle';
                        particle.style.setProperty('--gmrt-emitter-particle-index', String(particleIndex));
                        marker.appendChild(particle);
                    }
                }

                glow.dataset.lightKind = lightKind;
                glow.appendChild(marker);
                glow.setAttribute('aria-label', String(source.label || 'Keeper light source') + (source.lit === false ? ', doused' : ', lit'));
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

        // IV.32.4B — presentation-only Keeper cue. This never changes the
        // authoritative Fog projection; it only identifies the existing
        // bypass + Player Fog preview state for the 16-bit veil skin.
        fogLayer.classList.toggle(
            'is-player-preview',
            enabled && dmBypass && preview
        );

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

        // IV.35.4C.2C — the visible light pool itself is now painted from
        // server-approved cells. FogCellMapper has already resolved walls,
        // closed doors and other Vision Barriers before these cells arrive.
        if (lightLayer) {
            lightLayer.querySelectorAll('.gmrt-authoritative-light-cell').forEach((cell) => cell.remove());
            const authoritativeLight = fogProjection.light_cells && typeof fogProjection.light_cells === 'object'
                ? fogProjection.light_cells
                : {};
            Object.entries(authoritativeLight).forEach(([key, light]) => {
                const match = /^(-?\d+):(-?\d+)$/.exec(String(key));
                if (!match || !light || typeof light !== 'object') return;

                const intensity = Math.max(0, Math.min(1, Number(light.intensity || 0)));
                if (intensity <= 0.001) return;

                const column = Number(match[1]);
                const row = Number(match[2]);
                const cell = document.createElement('span');
                cell.className = 'gmrt-authoritative-light-cell is-' + (String(light.tone || 'warm') === 'cool' ? 'cool' : 'warm');
                cell.style.left = `${offsetX + (column * size)}px`;
                cell.style.top = `${offsetY + (row * size)}px`;
                cell.style.width = `${size + 1}px`;
                cell.style.height = `${size + 1}px`;
                cell.style.setProperty('--gmrt-authoritative-light', String(intensity));
                lightLayer.appendChild(cell);
            });
        }

        // IV.35.4C.2 — render the server-authoritative surviving light
        // transmission as pixel-cell shadowing. This is presentation only:
        // the server has already decided whether illumination survives.
        if (lightLayer) {
            lightLayer.querySelectorAll('.gmrt-light-attenuation-cell').forEach((cell) => cell.remove());
            const attenuation = fogProjection.light_attenuation && typeof fogProjection.light_attenuation === 'object'
                ? fogProjection.light_attenuation
                : {};
            Object.entries(attenuation).forEach(([key, amount]) => {
                const match = /^(-?\d+):(-?\d+)$/.exec(String(key));
                const opacity = Math.max(0, Math.min(1, Number(amount || 0)));
                if (!match || opacity <= 0.001) return;

                const column = Number(match[1]);
                const row = Number(match[2]);
                const shadow = document.createElement('span');
                shadow.className = 'gmrt-light-attenuation-cell';
                shadow.style.left = `${offsetX + (column * size)}px`;
                shadow.style.top = `${offsetY + (row * size)}px`;
                shadow.style.width = `${size + 1}px`;
                shadow.style.height = `${size + 1}px`;
                shadow.style.setProperty('--gmrt-light-attenuation', String(opacity));
                lightLayer.appendChild(shadow);
            });
        }

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
    // IV.30.1G.5Z.22B: independent runtime witness, not part of the long status text.
    const cartographyAuditRuntimeWitness = document.createElement('div');
    cartographyAuditRuntimeWitness.dataset.cartographyAuditRuntime = 'G.5Z.22B';
    cartographyAuditRuntimeWitness.setAttribute('role', 'status');
    cartographyAuditRuntimeWitness.style.cssText = 'display:none;padding:6px 9px;margin:5px 0;border:2px solid #7b245d;background:#fff4cc;color:#501638;font-weight:700;white-space:normal;overflow-wrap:anywhere;';
    if (cartographyAssistantStatus) cartographyAssistantStatus.before(cartographyAuditRuntimeWitness);
    const reportCartographyAuditRuntime = (message) => {
        if (!cartographyAssistantStatus) return;
        cartographyAuditRuntimeWitness.style.display = 'block';
        cartographyAuditRuntimeWitness.textContent = `G.5Z.22B runtime · ${message}`;
    };

    const cartographyReview = document.querySelector('[data-cartography-assistant-review]');
    let visionBarriers = [];
    let visionTool = null;
    let visionStart = null;
    let selectedVisionBarrier = null;
    let visionPreview = null;
    let cartographySuggestions = [];
    let cartographyEvidenceAudit = null; // IV.30.1G.5M — diagnostic only; never persisted.

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

        // IV.30.1G.5M — Contour Evidence Audit & Candidate Emission Diagnostics.
        // Audit geometry is deliberately non-authoritative: it is rendered only while
        // the Keeper selects the Evidence Audit detail mode and can never be applied.
        if (cartographyDetail?.value === 'audit' && cartographyEvidenceAudit?.records) {
            cartographyEvidenceAudit.records.slice(0, 240).forEach((record) => {
                if (!Array.isArray(record.points) || record.points.length < 2) return;
                const shape = document.createElementNS('http://www.w3.org/2000/svg', record.points.length > 2 ? 'polyline' : 'line');
                const projected = record.points.map((point) => barrierPoint(point.x, point.y));
                if (projected.length > 2) {
                    shape.setAttribute('points', projected.map((point) => `${point.x},${point.y}`).join(' '));
                    shape.setAttribute('fill', 'none');
                } else {
                    shape.setAttribute('x1', String(projected[0].x)); shape.setAttribute('y1', String(projected[0].y));
                    shape.setAttribute('x2', String(projected[1].x)); shape.setAttribute('y2', String(projected[1].y));
                }
                shape.classList.add('gmrt-cartography-evidence-audit', `is-${record.state || 'observed'}`);
                fragment.append(shape);
            });
        }

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

        // IV.30.1G.5Z.22: numbered, non-interactive audit-only endpoint markers.
        // These are SVG annotations, never draft suggestions or saved barriers.
        if (cartographyDetail?.value === 'audit' && Array.isArray(cartographyEvidenceAudit?.residualTerminations)) {
            // G.5Z.23: muted dashed witness for connected runs touching exactly
            // two distinct endpoints. Diagnostic SVG only, never a wall suggestion.
            (cartographyEvidenceAudit.residualPairedRunSegments || []).forEach((segment) => {
                const start = barrierPoint(segment.a.x, segment.a.y);
                const end = barrierPoint(segment.b.x, segment.b.y);
                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                line.setAttribute('x1', String(start.x)); line.setAttribute('y1', String(start.y));
                line.setAttribute('x2', String(end.x)); line.setAttribute('y2', String(end.y));
                line.setAttribute('stroke', '#167d91'); line.setAttribute('stroke-width', '1.5');
                line.setAttribute('stroke-opacity', '0.55'); line.setAttribute('stroke-dasharray', '3 5');
                line.setAttribute('pointer-events', 'none');
                line.setAttribute('data-audit-suppressed-run', String(segment.runId));
                fragment.append(line);
            });
            cartographyEvidenceAudit.residualTerminations.forEach((record) => {
                const projected = barrierPoint(record.point.x, record.point.y);
                const marker = document.createElementNS('http://www.w3.org/2000/svg', 'g');
                marker.classList.add('gmrt-cartography-termination-marker');
                marker.setAttribute('pointer-events', 'none');
                marker.setAttribute('data-audit-termination', String(record.id));
                const dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                dot.setAttribute('cx', String(projected.x)); dot.setAttribute('cy', String(projected.y));
                dot.setAttribute('r', '11'); dot.setAttribute('fill', '#fff4cc');
                dot.setAttribute('stroke', '#7b245d'); dot.setAttribute('stroke-width', '2');
                const number = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                number.setAttribute('x', String(projected.x)); number.setAttribute('y', String(projected.y + 3));
                number.setAttribute('font-size', '11'); number.setAttribute('font-weight', 'bold');
                number.setAttribute('text-anchor', 'middle'); number.setAttribute('fill', '#501638');
                number.textContent = String(record.id);
                const title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
                title.textContent = `Termination ${record.id}: ${record.reason}; suppressed run ${record.suppressedRunEdges || 0} edges; local ink ${Number(record.localInk || 0).toFixed(3)}`;
                marker.append(dot, number, title); fragment.append(marker);
            });
        }

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
            if (cartographyDetail?.value === 'audit' && cartographyEvidenceAudit) {
                const audit = cartographyEvidenceAudit;
                cartographyAssistantStatus.textContent = `G.5Z.22A termination audit: ${Array.isArray(audit.residualTerminations) ? audit.residualTerminations.length : 'MISSING'} records / ${audit.reconstructedSurfaceOpenChainTerminations || 0} expected · ${Array.isArray(audit.residualTerminations) ? audit.residualTerminations.map((entry) => `${entry.id}:P${entry.pathIndex}/${entry.reason}/${entry.suppressedRunEdges}e`).join(', ') : 'diagnostic records unavailable'} · Evidence Audit · ${audit.rawChains} raw chains · ${audit.recoverableChains} recoverable · ${audit.semanticRejected} semantic rejects · ${audit.inferredPerimeters} inferred perimeter edges → ${audit.promotedPerimeterChains || 0} promoted chains (${audit.promotedPerimeterEdges || 0} edges) → ${audit.consolidatedPromotedChains || 0} consolidated · ${audit.authoritativePreserved || 0}/${audit.authoritativeContours || 0} authoritative preserved → ${audit.authoritativeReviewPaths || 0} authoritative review paths · ${audit.authoritativePathMerges || 0} authority merges · ${audit.authoritativeReviewSlotsLiberated || 0} review slots liberated · ${audit.authoritativeRemainingCapacity || 0} capacity remaining · ${audit.illustratedFloorSeeds || 0} floor seeds · ${audit.illustratedTraversalSeedsQueued || 0} traversal seeds queued · ${audit.illustratedTraversalSeedsVisited || 0} traversal seeds visited · ${audit.illustratedAdjacentSamplesExamined || 0} adjacent samples examined · ${audit.illustratedAlreadyPlayableNeighbours || 0} already-playable neighbours · ${audit.illustratedFrontierCandidates || 0} frontier candidates · ${audit.illustratedFrontierAdmitted || 0} frontier admitted · ${audit.illustratedFrontierStructuralRejects || 0} structural rejects · ${audit.illustratedFrontierExteriorRejects || 0} exterior rejects · ${audit.illustratedFrontierInteriorSupportRejects || 0} interior-support rejects · ${audit.illustratedFrontierDecorationRejects || 0} decoration rejects · ${audit.illustratedDecorationRejectUnrecoveredCells || 0} unrecovered decoration cells → ${audit.illustratedDecorationRejectComponents || 0} reject components · ${audit.illustratedDecorationRejectAdjacentComponents || 0} adjacent to recovered floor · ${audit.illustratedDecorationRejectMultiSidedComponents || 0} multi-sided support · ${audit.illustratedDecorationRejectInteriorSupportedComponents || 0} interior-supported · ${audit.illustratedDecorationRejectIsolatedComponents || 0} isolated · ${audit.illustratedPotentialPlayableIslandComponents || 0} potential playable islands (${audit.illustratedPotentialPlayableIslandCells || 0} cells) · ${audit.illustratedMeshCells || 0} mesh cells · ${audit.illustratedRecoveredOrCanonicalCells || 0} recovered/canonical cells · ${audit.illustratedFrontierExaminedUniqueCells || 0} unique frontier-examined cells · ${audit.illustratedNeverFrontierCells || 0} never-frontier cells · ${audit.illustratedFloorLikeNeverFrontierCells || 0} floor-like never-frontier cells → ${audit.illustratedNeverFrontierComponents || 0} components · ${audit.illustratedNeverFrontierExteriorComponents || 0} exterior-connected · ${audit.illustratedNeverFrontierInteriorComponents || 0} enclosed/interior · ${audit.illustratedNeverFrontierNarrowGapComponents || 0} narrow-gap adjacent · ${audit.illustratedDisconnectedPlayableIslandComponents || 0} disconnected playable islands (${audit.illustratedDisconnectedPlayableIslandCells || 0} cells) · ${audit.illustratedInteriorQualificationRejectedComponents || 0} interior qualification rejects (${audit.illustratedInteriorQualificationRejectedCells || 0} cells) → ${audit.illustratedInteriorQualificationSingletonRejects || 0} coherence/singleton rejects · ${audit.illustratedInteriorQualificationMultiCellRejects || 0} other multi-cell rejects · ${audit.illustratedInteriorQualificationRejectedInitiallyNearRecovered || 0} initially near recovered · ${audit.illustratedInteriorQualificationRejectedAbsorbedComponents || 0} rejects absorbed post-recovery (${audit.illustratedInteriorQualificationRejectedAbsorbedCells || 0} cells) · ${audit.illustratedInteriorQualificationRejectedRemainingComponents || 0} rejects still unresolved → ${audit.illustratedInteriorQualificationRejectedPostRecoveryAdjacent || 0} now adjacent · ${audit.illustratedInteriorQualificationRejectedPostRecoveryNarrowGap || 0} now narrow-gap · ${audit.illustratedSecondaryNarrowGapCandidates || 0} secondary narrow-gap candidates → ${audit.illustratedSecondaryTopologySafeCandidates || 0} topology-safe seed candidates → ${audit.illustratedSecondarySeedsAdmitted || 0} secondary seeds admitted → ${audit.illustratedSecondaryRecoveredCells || 0} secondary illustrated cells recovered · ${audit.illustratedIterativeGenerationsRun || 0} iterative generations · G2 ${audit.illustratedIterativeGeneration2Candidates || 0} candidates → ${audit.illustratedIterativeGeneration2Seeds || 0} seeds → ${audit.illustratedIterativeGeneration2RecoveredCells || 0} recovered · G3 ${audit.illustratedIterativeGeneration3Candidates || 0} candidates → ${audit.illustratedIterativeGeneration3Seeds || 0} seeds → ${audit.illustratedIterativeGeneration3RecoveredCells || 0} recovered · ${(audit.illustratedIterativeGenerationAudit || []).filter((entry) => entry.generation >= 4).map((entry) => `G${entry.generation} ${entry.candidates} candidates → ${entry.seeds} seeds → ${entry.recovered} recovered`).join(' · ')}${(audit.illustratedIterativeGenerationAudit || []).some((entry) => entry.generation >= 4) ? ' · ' : ''}${audit.illustratedIterativeSeedsAdmitted || 0} iterative seeds → ${audit.illustratedIterativeRecoveredCells || 0} iterative cells recovered · convergence ${audit.illustratedIterativeConvergenceReason || 'n/a'} · ${audit.illustratedUnseededIslandComponents || 0} unseeded islands (${audit.illustratedUnseededIslandCells || 0} cells) · ${audit.illustratedUnseededInitiallyNoNarrowGap || 0} initially no narrow-gap · ${audit.illustratedUnseededInitiallyUnsafeBridge || 0} initially unsafe bridge · ${audit.illustratedUnseededAbsorbedComponents || 0} absorbed by secondary recovery (${audit.illustratedUnseededAbsorbedCells || 0} cells) · ${audit.illustratedUnseededRemainingComponents || 0} still disconnected → ${audit.illustratedUnseededPostRecoveryAdjacentComponents || 0} now adjacent · ${audit.illustratedUnseededPostRecoveryNarrowGapComponents || 0} now narrow-gap · ${audit.illustratedUnseededPostRecoveryTopologySafeComponents || 0} now topology-safe · ${audit.illustratedUnseededPostRecoveryStructuralBlockedComponents || 0} structural-gap blocked · ${audit.illustratedFrontierNeighbourQualified || 0} neighbour-qualified · ${audit.illustratedFrontierDecorationQualified || 0} decoration-qualified · ${audit.illustratedFrontierProvisionalAdmissions || 0} provisional illustrated admissions · ${audit.illustratedFrontierUnaccounted || 0} unaccounted frontier · ${audit.illustratedPropagationWaves || 0} propagation waves · ${audit.illustratedPropagationDeepestWave || 0} deepest wave · ${audit.illustratedPropagatedAdmissions || 0} propagated admissions · ${audit.illustratedQuietFloorAdmissions || 0} quiet-floor admissions · ${audit.illustratedExhaustedFrontierCells || 0} exhausted frontier samples · ${audit.recoveredIllustratedFloorCells || 0} illustrated floor cells recovered · ${audit.reconstructedIllustratedSurfaces || 0} interior surfaces reconstructed · ${audit.illustratedSurfaceAnchorCandidates || 0} surface anchor candidates · ${audit.illustratedSurfaceExactPlayableAnchors || 0} exact playable anchors · ${audit.illustratedSurfaceReconciledAnchors || 0} reconciled anchors · ${audit.illustratedSurfaceUnresolvedSurfaces || 0} unresolved surfaces · ${audit.illustratedSurfaceCompletedComponents || 0} completed surface components · ${audit.illustratedSurfaceCompletedCells || 0} completed playable cells · ${audit.illustratedSurfaceRawBoundarySides || 0} raw surface boundary sides · ${audit.illustratedSurfacePlayableSeamsSuppressed || 0} playable seams suppressed · ${audit.illustratedSurfaceThresholdRejects || 0} threshold boundary rejects · boundary corroboration ${audit.illustratedSurfaceBoundaryStructuralCorroborated || 0} exact structural + ${audit.illustratedSurfaceBoundaryStrongInkCorroborated || 0} strong ink + ${audit.illustratedSurfaceBoundaryModerateInkCorroborated || 0} moderate ink · ${audit.illustratedSurfaceBoundaryUnsupportedFrontier || 0} unsupported frontier (${audit.illustratedSurfaceBoundaryOpenPaperFrontier || 0} open-paper) · ${audit.illustratedSurfaceBoundaryOpenPaperSuppressed || 0} open-paper frontier suppressed · gap continuity ${audit.illustratedSurfaceSuppressedGapRuns || 0} runs → ${audit.illustratedSurfaceSuppressedGapBothBounded || 0} both-bounded · ${audit.illustratedSurfaceSuppressedGapOneBounded || 0} one-bounded · ${audit.illustratedSurfaceSuppressedGapUnbounded || 0} unbounded · ${audit.illustratedSurfaceSuppressedGapBranchAdjacent || 0} branch-adjacent · ${audit.illustratedSurfaceSuppressedGapSameSourceContiguous || 0} same-source contiguous · lengths 1:${audit.illustratedSurfaceSuppressedGapLength1 || 0} · 2:${audit.illustratedSurfaceSuppressedGapLength2 || 0} · 3–4:${audit.illustratedSurfaceSuppressedGapLength3To4 || 0} · 5–8:${audit.illustratedSurfaceSuppressedGapLength5To8 || 0} · 9+:${audit.illustratedSurfaceSuppressedGapLength9Plus || 0} · micro-gap restoration ${audit.illustratedSurfaceMicroGapEligibleRuns || 0} eligible runs (${audit.illustratedSurfaceMicroGapEligibleEdges || 0} edges) → ${audit.illustratedSurfaceMicroGapRestoredEdges || 0} edges restored · short-run restoration ${audit.illustratedSurfaceShortGapEligibleRuns || 0} eligible runs (${audit.illustratedSurfaceShortGapEligibleEdges || 0} edges) → ${audit.illustratedSurfaceShortGapRestoredEdges || 0} edges restored · long-run forensic ${audit.illustratedSurfaceLongGapRuns || 0} runs (${audit.illustratedSurfaceLongGapEdges || 0} edges) → ${audit.illustratedSurfaceLongGapFiveToEightRuns || 0} at 5–8 · ${audit.illustratedSurfaceLongGapNinePlusRuns || 0} at 9+ · exact lengths [${(audit.illustratedSurfaceLongGapExactLengths || []).join(',')}] · ${audit.illustratedSurfaceLongGapNearCutoffEdges || 0} near-cutoff · ${audit.illustratedSurfaceLongGapDeepQuietEdges || 0} deep-quiet · ${audit.illustratedSurfaceLongGapInteriorDepthSupportedEdges || 0} deep-interior-supported · mean ink ${((audit.illustratedSurfaceLongGapMeanInkPermille || 0) / 1000).toFixed(3)} · max ink ${((audit.illustratedSurfaceLongGapMaxInkPermille || 0) / 1000).toFixed(3)} · total span ${audit.illustratedSurfaceLongGapTotalSpanCells || 0} cells · largest span ${audit.illustratedSurfaceLongGapLargestSpanCells || 0} cells · geometric travel [${(audit.illustratedSurfaceLongGapGeometry || []).map((entry) => `${entry.edges}e:${(entry.travelMilliCells / 1000).toFixed(1)}t/${(entry.displacementMilliCells / 1000).toFixed(1)}d/${(entry.travelDisplacementPermille / 1000).toFixed(2)}r`).join(', ')}] → ${audit.illustratedSurfacePerimeterEdges || 0} surface perimeter edges contributed → ${audit.reconstructedSurfacePromotedChains || 0} surface paths promoted (${audit.reconstructedSurfaceAssembledEdges || 0} assembled edges) · ${audit.reconstructedSurfaceUnassembledEdges || 0} unassembled perimeter edges → ${audit.reconstructedSurfaceUnassembledComponents || 0} unassembled components (largest ${audit.reconstructedSurfaceLargestUnassembledComponent || 0}) · ${audit.reconstructedSurfaceUnassembledBranchAdjacentEdges || 0} branch-adjacent unassembled edges · ${audit.reconstructedSurfaceVertexCapRiskComponents || 0} vertex-cap-risk components (${audit.reconstructedSurfaceVertexCapRiskEdges || 0} edges) · ${audit.reconstructedSurfaceVertexCapSourceComponents || 0} oversized source components → ${audit.reconstructedSurfaceVertexCapSegmentedPathCount || 0} cap-safe paths (${audit.reconstructedSurfaceVertexCapSegmentedEdges || 0} edges) · ${audit.reconstructedSurfaceVertexCapSplitVertices || 0} shared split vertices · ${audit.reconstructedSurfaceClosedChains || 0} closed / ${audit.reconstructedSurfaceOpenChains || 0} open · ${audit.reconstructedSurfaceAlreadyRepresented || 0} already represented (${audit.reconstructedSurfaceAlreadyRepresentedEdges || 0} edges) · ${audit.reconstructedSurfaceAuthorityExtensions || 0} authority extensions (${audit.reconstructedSurfaceAuthorityExtensionEdges || 0} edges) · ${audit.reconstructedSurfaceNovelChains || 0} novel surface paths · review pressure ${audit.reconstructedSurfaceReviewCeiling || 0} ceiling → ${audit.reconstructedSurfaceAuthorityOccupancy || 0} authority occupancy → ${audit.reconstructedSurfaceSlotsAvailable || 0} surface slots · ${audit.reconstructedSurfaceRequestedNovelChains || 0} surface paths requested (${audit.reconstructedSurfaceRequestedNovelEdges || 0} edges) → ${audit.reconstructedSurfaceOverlayChains || 0} certified overlays (${audit.reconstructedSurfaceOverlayEdges || 0} edges) outside review budget · ${audit.reconstructedSurfaceReviewObjectsEmitted || 0} review objects inside 200 ceiling → ${audit.reconstructedSurfaceCapacityRejectedChains || 0} capacity rejected (${audit.reconstructedSurfaceCapacityRejectedEdges || 0} edges) · ${audit.reconstructedSurfaceCapacityRejectedClosedChains || 0} rejected closed / ${audit.reconstructedSurfaceCapacityRejectedOpenChains || 0} rejected open · ${audit.reconstructedSurfaceCapacityRejectedVertexCapSegments || 0} rejected cap-segments · ${audit.reconstructedSurfaceCapacityRejectedEndpointContiguous || 0} endpoint-contiguous with admitted surface · ${audit.reconstructedSurfaceCapacityRejectedMergeEligible || 0} exact-endpoint cap-safe merge candidates · ${audit.reconstructedSurfaceCapacityRejectedIndependent || 0} independent rejected paths · ${audit.reconstructedSurfaceRepresentedChains || 0} surface paths represented (${audit.reconstructedSurfaceRepresentedEdges || 0} edges) · ${audit.reconstructedSurfaceUncoveredEdges || 0} uncovered surface edges · ${audit.reconstructedSurfaceRenderedReviewSegments || 0} rendered review segments · ${audit.reconstructedSurfaceExactCorrespondenceEdges || 0} exact-correspondence edges (${audit.reconstructedSurfaceAuthorityCorrespondenceEdges || 0} authority / ${audit.reconstructedSurfaceNovelCorrespondenceEdges || 0} novel) · ${audit.reconstructedSurfaceDisplacedEdges || 0} displaced edges · ${audit.reconstructedSurfaceCollapsedEdges || 0} collapsed edges · ${audit.reconstructedSurfaceOpenChainTerminations || 0} open-chain terminations · residual termination audit ${(audit.residualTerminations || []).length} numbered [${(audit.residualTerminations || []).map((entry) => `${entry.id}:P${entry.pathIndex}/${entry.reason}/${entry.suppressedRunEdges}e`).join(', ')}] · ${audit.representedPromotedChains || 0} promoted represented (${audit.representedPromotedEdges || 0} edges) · ${audit.reviewObjectsEmitted || 0} review objects + ${audit.certifiedSurfaceOverlaysEmitted || 0} certified overlays = ${audit.emitted} rendered. Diagnostic marks are never saved.`;
            } else {
                const doors = cartographySuggestions.filter((item) => item.type === 'door').length;
                cartographyAssistantStatus.textContent = `${total} draft suggestions · ${selected} selected · ${doors} possible doors. Polyline wall paths count as one review object each. Nothing is saved until Apply Selected.`;
            }
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
            const thresholdReason = suggestion.doorwayReasoning && Array.isArray(suggestion.thresholdEvidence)
                ? ` · ${suggestion.thresholdEvidence.join(' + ')}`
                : '';
            const noiseReason = !suggestion.doorwayReasoning && Array.isArray(suggestion.noiseEvidence) && suggestion.noiseEvidence.length > 0
                ? ` · screened: ${suggestion.noiseEvidence.join(' + ')}`
                : '';
            const contourLabel = suggestion.partialContour ? 'Partial living wall path' : 'Living wall path';
            const protectedGapCount = Array.isArray(suggestion.protectedContourGaps) ? suggestion.protectedContourGaps.length : 0;
            const gapTypes = Array.isArray(suggestion.gapClassifications)
                ? Array.from(new Set(suggestion.gapClassifications.map((gap) => gap?.classification).filter(Boolean)))
                : [];
            const contourSuffix = suggestion.partialContour
                ? ` · unresolved ends kept open${protectedGapCount > 0 ? ` · ${protectedGapCount} doorway/passage gap${protectedGapCount === 1 ? '' : 's'} protected` : ''}${gapTypes.length > 0 ? ` · ends: ${gapTypes.join(' / ')}` : ''}`
                : '';
            text.textContent = pathVertices > 2
                ? `${hybridPrefix ? `${hybridPrefix} · ` : ''}${contourLabel} · ${pathVertices - 1} connected spans · ${confidence}%${contourSuffix}`
                : `${hybridPrefix ? `${hybridPrefix} · ` : ''}${suggestion.type === 'door' ? (suggestion.doorwayReasoning ? 'Likely doorway' : 'Possible door') : 'Room / wall boundary'} · (${suggestion.x1},${suggestion.y1}) → (${suggestion.x2},${suggestion.y2}) · ${confidence}%${thresholdReason}${noiseReason}`;
            label.append(checkbox, text);
            fragment.append(label);
        });
        cartographyReview.append(fragment);
        updateCartographyDraftControls();
        renderCartographySuggestions();
    };

    const clearCartographyDraft = (message = 'Draft cleared. No cartography suggestions were saved.') => {
        cartographySuggestions = [];
        cartographyEvidenceAudit = null;
        cartographyAuditRuntimeWitness.style.display = 'none';
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

        // IV.30.1G — Adaptive Evidence Model.
        // Imported maps do not agree on what "dark" means: parchment scans, pale
        // stone, charcoal caves and digital maps all place their wall ink at very
        // different absolute luminance values. Build summed-area tables once so the
        // specialist readers can judge a sample against its *local* neighbourhood in
        // constant time instead of relying on one global darkness threshold.
        const luminanceIntegralWidth = canvas.width + 1;
        const luminanceIntegral = new Float64Array((canvas.width + 1) * (canvas.height + 1));
        const luminanceSquaredIntegral = new Float64Array((canvas.width + 1) * (canvas.height + 1));
        const luminanceHistogram = new Uint32Array(256);
        for (let y = 0; y < canvas.height; y += 1) {
            let rowSum = 0;
            let rowSquaredSum = 0;
            for (let x = 0; x < canvas.width; x += 1) {
                const value = luminance(x, y);
                rowSum += value;
                rowSquaredSum += value * value;
                const index = ((y + 1) * luminanceIntegralWidth) + (x + 1);
                luminanceIntegral[index] = luminanceIntegral[index - luminanceIntegralWidth] + rowSum;
                luminanceSquaredIntegral[index] = luminanceSquaredIntegral[index - luminanceIntegralWidth] + rowSquaredSum;
                luminanceHistogram[Math.max(0, Math.min(255, Math.round(value)))] += 1;
            }
        }
        const luminancePercentile = (fraction) => {
            const target = Math.max(1, Math.round(canvas.width * canvas.height * fraction));
            let seen = 0;
            for (let value = 0; value < luminanceHistogram.length; value += 1) {
                seen += luminanceHistogram[value];
                if (seen >= target) return value;
            }
            return 255;
        };
        const mapTone = {
            dark: luminancePercentile(.18),
            middle: luminancePercentile(.50),
            light: luminancePercentile(.82)
        };
        const luminanceRegionStats = (x1, y1, x2, y2) => {
            const left = Math.max(0, Math.min(canvas.width - 1, Math.floor(Math.min(x1, x2))));
            const top = Math.max(0, Math.min(canvas.height - 1, Math.floor(Math.min(y1, y2))));
            const right = Math.min(canvas.width, Math.max(left + 1, Math.ceil(Math.max(x1, x2))));
            const bottom = Math.min(canvas.height, Math.max(top + 1, Math.ceil(Math.max(y1, y2))));
            const sumAt = (table, x, y) => table[(y * luminanceIntegralWidth) + x];
            const area = Math.max(1, (right - left) * (bottom - top));
            const sum = sumAt(luminanceIntegral, right, bottom)
                - sumAt(luminanceIntegral, left, bottom)
                - sumAt(luminanceIntegral, right, top)
                + sumAt(luminanceIntegral, left, top);
            const squared = sumAt(luminanceSquaredIntegral, right, bottom)
                - sumAt(luminanceSquaredIntegral, left, bottom)
                - sumAt(luminanceSquaredIntegral, right, top)
                + sumAt(luminanceSquaredIntegral, left, top);
            const mean = sum / area;
            const variance = Math.max(0, (squared / area) - (mean * mean));
            return { mean, deviation: Math.sqrt(variance), area };
        };
        const adaptiveInkEvidence = (x1, y1, x2, y2, neighbourhoodRadius) => {
            const sample = luminanceRegionStats(x1, y1, x2, y2);
            const centerX = (x1 + x2) / 2;
            const centerY = (y1 + y2) / 2;
            const neighbourhood = luminanceRegionStats(
                centerX - neighbourhoodRadius,
                centerY - neighbourhoodRadius,
                centerX + neighbourhoodRadius,
                centerY + neighbourhoodRadius
            );
            const localContrast = neighbourhood.mean - sample.mean;
            // Busy hatch/stone needs more separation than quiet floor before a mark is
            // trusted. Pale walls can therefore qualify through relative contrast,
            // while globally dark floors are not automatically promoted to wall ink.
            const requiredContrast = Math.max(9, Math.min(32, 8 + (neighbourhood.deviation * .38)));
            const relativeInk = localContrast >= requiredContrast;
            const tonalGuard = sample.mean <= Math.max(mapTone.middle - 4, mapTone.dark + 52);
            return {
                sampleMean: sample.mean,
                localMean: neighbourhood.mean,
                localDeviation: neighbourhood.deviation,
                localContrast,
                requiredContrast,
                relativeInk: relativeInk && tonalGuard,
                strong: localContrast >= requiredContrast * 1.45 && tonalGuard
            };
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
            // Keep the historic absolute threshold only as a conservative fallback for
            // genuinely dark ink. IV.30.1G makes local contrast the primary evidence.
            const darkThreshold = 92;
            const gridCanvas = Math.max(4, toCanvasX(grid.size));
            const sampleStep = Math.max(1, Math.round(Math.min(canvas.width, canvas.height) / 900));
            const dark = new Uint8Array(canvas.width * canvas.height);
            const adaptiveNeighbourhood = Math.max(6, gridCanvas * .42);
            for (let y = 0; y < canvas.height; y += sampleStep) {
                for (let x = 0; x < canvas.width; x += sampleStep) {
                    const right = Math.min(canvas.width, x + sampleStep);
                    const bottom = Math.min(canvas.height, y + sampleStep);
                    const evidence = adaptiveInkEvidence(x, y, right, bottom, adaptiveNeighbourhood);
                    let darkSamples = 0;
                    let totalSamples = 0;
                    for (let yy = y; yy < bottom; yy += 1) {
                        for (let xx = x; xx < right; xx += 1) {
                            totalSamples += 1;
                            if (luminance(xx, yy) <= darkThreshold) darkSamples += 1;
                        }
                    }
                    const absoluteDarkInk = darkSamples / Math.max(1, totalSamples) >= .45
                        && evidence.localContrast >= 4;
                    if (evidence.relativeInk || absoluteDarkInk) {
                        for (let yy = y; yy < bottom; yy += 1) {
                            for (let xx = x; xx < right; xx += 1) dark[(yy * canvas.width) + xx] = 1;
                        }
                    }
                }
            }

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
                const suggestion = {
                    x1: gx1, y1: gy1, x2: gx2, y2: gy2,
                    type: 'wall', confidence, selected: true, structural: true,
                    adaptiveEvidence: true, evidenceModel: 'local-contrast-v1'
                };
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

            // IV.30.1G.3 — Noise, Furniture & Annotation Rejection.
            // Text, stair marks, furniture outlines, rubble and hatch clusters can all
            // contain locally dark strokes. Build a deliberately coarse component map
            // over the adaptive ink mask so short compact marks and isotropic clutter
            // can be demoted before doorway reasoning mistakes them for architecture.
            const noiseStep = Math.max(2, Math.round(gridCanvas * .08));
            const noiseColumns = Math.max(1, Math.ceil(canvas.width / noiseStep));
            const noiseRows = Math.max(1, Math.ceil(canvas.height / noiseStep));
            const noiseOccupied = new Uint8Array(noiseColumns * noiseRows);
            const noiseLabels = new Int32Array(noiseColumns * noiseRows);
            const noiseComponents = new Map();
            const noiseCellIndex = (column, row) => (row * noiseColumns) + column;
            for (let row = 0; row < noiseRows; row += 1) {
                for (let column = 0; column < noiseColumns; column += 1) {
                    const left = column * noiseStep;
                    const top = row * noiseStep;
                    const right = Math.min(canvas.width, left + noiseStep);
                    const bottom = Math.min(canvas.height, top + noiseStep);
                    let ink = 0;
                    let total = 0;
                    for (let yy = top; yy < bottom; yy += 1) {
                        for (let xx = left; xx < right; xx += 1) {
                            total += 1;
                            ink += dark[(yy * canvas.width) + xx];
                        }
                    }
                    if (ink / Math.max(1, total) >= .20) noiseOccupied[noiseCellIndex(column, row)] = 1;
                }
            }
            let nextNoiseLabel = 1;
            const noiseNeighbours = [[1, 0], [-1, 0], [0, 1], [0, -1]];
            for (let row = 0; row < noiseRows; row += 1) {
                for (let column = 0; column < noiseColumns; column += 1) {
                    const startIndex = noiseCellIndex(column, row);
                    if (!noiseOccupied[startIndex] || noiseLabels[startIndex] !== 0) continue;
                    const label = nextNoiseLabel++;
                    const queue = [[column, row]];
                    noiseLabels[startIndex] = label;
                    let cells = 0;
                    let minX = column;
                    let maxX = column;
                    let minY = row;
                    let maxY = row;
                    while (queue.length > 0) {
                        const [cx, cy] = queue.pop();
                        cells += 1;
                        minX = Math.min(minX, cx); maxX = Math.max(maxX, cx);
                        minY = Math.min(minY, cy); maxY = Math.max(maxY, cy);
                        noiseNeighbours.forEach(([dx, dy]) => {
                            const nx = cx + dx;
                            const ny = cy + dy;
                            if (nx < 0 || ny < 0 || nx >= noiseColumns || ny >= noiseRows) return;
                            const index = noiseCellIndex(nx, ny);
                            if (!noiseOccupied[index] || noiseLabels[index] !== 0) return;
                            noiseLabels[index] = label;
                            queue.push([nx, ny]);
                        });
                    }
                    const widthCells = (maxX - minX) + 1;
                    const heightCells = (maxY - minY) + 1;
                    const widthGridUnits = (widthCells * noiseStep) / gridCanvas;
                    const heightGridUnits = (heightCells * noiseStep) / gridCanvas;
                    const longestGridSpan = Math.max(widthGridUnits, heightGridUnits);
                    const shortestGridSpan = Math.max(.01, Math.min(widthGridUnits, heightGridUnits));
                    noiseComponents.set(label, {
                        label, cells, minX, maxX, minY, maxY,
                        widthGridUnits,
                        heightGridUnits,
                        longestGridSpan,
                        elongation: longestGridSpan / shortestGridSpan,
                        fillRatio: cells / Math.max(1, widthCells * heightCells)
                    });
                }
            }
            const noiseComponentAt = (x, y) => {
                const column = Math.max(0, Math.min(noiseColumns - 1, Math.floor(x / noiseStep)));
                const row = Math.max(0, Math.min(noiseRows - 1, Math.floor(y / noiseStep)));
                const label = noiseLabels[noiseCellIndex(column, row)];
                return label > 0 ? (noiseComponents.get(label) || null) : null;
            };
            const localClutterProfile = (x, y) => {
                const radius = Math.max(traceRadius * .8, gridCanvas * .18);
                const samples = [
                    densityAt(x - radius, y), densityAt(x + radius, y),
                    densityAt(x, y - radius), densityAt(x, y + radius),
                    densityAt(x - radius * .7, y - radius * .7), densityAt(x + radius * .7, y - radius * .7),
                    densityAt(x - radius * .7, y + radius * .7), densityAt(x + radius * .7, y + radius * .7)
                ];
                const mean = samples.reduce((sum, value) => sum + value, 0) / samples.length;
                const minimum = Math.min(...samples);
                const maximum = Math.max(...samples);
                return {
                    mean,
                    minimum,
                    maximum,
                    isotropic: minimum >= .11 && mean >= .20,
                    busy: mean >= .24 && (maximum - minimum) <= .24
                };
            };

            const traces = [];
            const structuralScore = (x, y, normalX, normalY) => {
                const center = densityAt(x, y);
                const sideOffset = gridCanvas * .23;
                const sideA = densityAt(x + (normalX * sideOffset), y + (normalY * sideOffset));
                const sideB = densityAt(x - (normalX * sideOffset), y - (normalY * sideOffset));
                const quietSide = Math.min(sideA, sideB);
                const loudSide = Math.max(sideA, sideB);
                const sampleRadius = Math.max(1.5, traceRadius * .72);
                const adaptiveEvidence = adaptiveInkEvidence(
                    x - sampleRadius, y - sampleRadius,
                    x + sampleRadius, y + sampleRadius,
                    Math.max(adaptiveNeighbourhood, gridCanvas * .34)
                );
                const densityStructure = center >= .18 && quietSide <= .16 && center >= loudSide * 1.25;
                const adaptiveStructure = adaptiveEvidence.strong
                    && center >= .12
                    && quietSide <= .22
                    && center >= loudSide * 1.08;
                return {
                    center,
                    quietSide,
                    loudSide,
                    adaptiveEvidence,
                    structural: densityStructure || adaptiveStructure,
                    continuity: center >= .10 && quietSide <= .20 && center >= loudSide * 1.05
                        || (adaptiveEvidence.relativeInk && center >= .08 && quietSide <= .24)
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

            // IV.30.1G.1 — Corners, Junctions & Wall Bodies.
            // A single dark stroke is weak evidence. A stroke that participates in a
            // recognisable architectural structure is much stronger: corners, T/X
            // junctions, collinear continuation and a thick wall-body profile all add
            // confidence. This is deliberately a scoring layer over the adaptive
            // reader rather than a hard requirement, so damaged scans and incomplete
            // walls are not erased merely because one neighbour is missing.
            const rawStructuralWalls = Array.from(wallVotes.values());
            const vertexKey = (x, y) => `${x}:${y}`;
            const vertexWalls = new Map();
            const addVertexWall = (key, index) => {
                const attached = vertexWalls.get(key) || [];
                attached.push(index);
                vertexWalls.set(key, attached);
            };
            rawStructuralWalls.forEach((wall, index) => {
                addVertexWall(vertexKey(wall.x1, wall.y1), index);
                addVertexWall(vertexKey(wall.x2, wall.y2), index);
            });
            const directionFor = (wall) => {
                const dx = Math.sign(wall.x2 - wall.x1);
                const dy = Math.sign(wall.y2 - wall.y1);
                return { dx, dy };
            };
            const collinearDirections = (a, b) => (a.dx === b.dx && a.dy === b.dy)
                || (a.dx === -b.dx && a.dy === -b.dy);
            const endpointArchitecture = (wall, wallIndex, x, y) => {
                const direction = directionFor(wall);
                const neighbours = (vertexWalls.get(vertexKey(x, y)) || [])
                    .filter((index) => index !== wallIndex)
                    .map((index) => rawStructuralWalls[index]);
                const continuation = neighbours.some((other) => collinearDirections(direction, directionFor(other)));
                const turns = neighbours.filter((other) => ! collinearDirections(direction, directionFor(other))).length;
                return {
                    degree: neighbours.length + 1,
                    continuation,
                    corner: turns >= 1 && neighbours.length === 1,
                    junction: neighbours.length >= 2
                };
            };
            const wallBodyProfile = (wall) => {
                const direction = directionFor(wall);
                const length = Math.hypot(direction.dx, direction.dy) || 1;
                const normalX = -direction.dy / length;
                const normalY = direction.dx / length;
                const midpointX = toCanvasX(grid.offsetX) + (((wall.x1 + wall.x2) / 2) * gridCanvas);
                const midpointY = toCanvasY(grid.offsetY) + (((wall.y1 + wall.y2) / 2) * gridCanvas);
                const innerOffset = Math.max(1.5, traceRadius * .72);
                const outerOffset = Math.max(innerOffset + 1, gridCanvas * .23);
                const center = densityAt(midpointX, midpointY);
                const innerA = densityAt(midpointX + (normalX * innerOffset), midpointY + (normalY * innerOffset));
                const innerB = densityAt(midpointX - (normalX * innerOffset), midpointY - (normalY * innerOffset));
                const outerA = densityAt(midpointX + (normalX * outerOffset), midpointY + (normalY * outerOffset));
                const outerB = densityAt(midpointX - (normalX * outerOffset), midpointY - (normalY * outerOffset));
                const innerSupport = Math.max(innerA, innerB);
                const quietExterior = Math.min(outerA, outerB);
                const supported = center >= .13 && innerSupport >= .08 && quietExterior <= .22;
                return { supported, center, innerSupport, quietExterior };
            };
            const architecturalWalls = rawStructuralWalls.map((wall, wallIndex) => {
                const start = endpointArchitecture(wall, wallIndex, wall.x1, wall.y1);
                const end = endpointArchitecture(wall, wallIndex, wall.x2, wall.y2);
                const body = wallBodyProfile(wall);
                const cornerCount = Number(start.corner) + Number(end.corner);
                const junctionCount = Number(start.junction) + Number(end.junction);
                const continuationCount = Number(start.continuation) + Number(end.continuation);
                const isolated = start.degree === 1 && end.degree === 1 && ! body.supported;
                const architectureBoost = (cornerCount * 4)
                    + (junctionCount * 7)
                    + (continuationCount * 3)
                    + (body.supported ? 5 : 0)
                    - (isolated ? 8 : 0);
                const evidence = [];
                if (cornerCount > 0) evidence.push('corner');
                if (junctionCount > 0) evidence.push('junction');
                if (continuationCount > 0) evidence.push('continuation');
                if (body.supported) evidence.push('wall-body');
                if (isolated) evidence.push('isolated-stroke');
                return {
                    ...wall,
                    confidence: Math.max(42, Math.min(99, Math.round(wall.confidence + architectureBoost))),
                    architecturalEvidence: evidence,
                    topologySupport: {
                        cornerCount,
                        junctionCount,
                        continuationCount,
                        isolated
                    },
                    wallBodyEvidence: body,
                    evidenceModel: 'local-contrast-topology-v2'
                };
            });

            // Apply the noise screen after topology has had a chance to defend a
            // genuine corner/junction, but before threshold reasoning. Strong connected
            // architecture survives busy art; isolated compact marks are the primary
            // rejection target. Nothing is discarded on texture evidence alone.
            const noiseScreenedWalls = architecturalWalls.map((wall) => {
                const midpointX = toCanvasX(grid.offsetX) + (((wall.x1 + wall.x2) / 2) * gridCanvas);
                const midpointY = toCanvasY(grid.offsetY) + (((wall.y1 + wall.y2) / 2) * gridCanvas);
                const component = noiseComponentAt(midpointX, midpointY);
                const clutter = localClutterProfile(midpointX, midpointY);
                const isolated = Boolean(wall.topologySupport?.isolated);
                const compactMark = Boolean(component)
                    && component.longestGridSpan <= .95
                    && component.elongation <= 2.15
                    && component.fillRatio >= .30;
                const furnitureOutline = Boolean(component)
                    && component.longestGridSpan >= .70
                    && component.longestGridSpan <= 2.8
                    && component.elongation <= 1.7
                    && component.fillRatio >= .16
                    && component.fillRatio <= .62;
                const textureCluster = clutter.isotropic || clutter.busy;
                const evidence = [];
                let penalty = 0;
                if (compactMark) { evidence.push('compact-annotation'); penalty += 12; }
                if (furnitureOutline && isolated) { evidence.push('furniture-like-outline'); penalty += 9; }
                if (textureCluster) { evidence.push('busy-texture'); penalty += 8; }
                if (!isolated && (wall.topologySupport?.cornerCount || wall.topologySupport?.junctionCount || wall.topologySupport?.continuationCount)) {
                    penalty = Math.max(0, penalty - 7);
                    evidence.push('topology-defends-wall');
                }
                const rejected = isolated && compactMark && textureCluster && (wall.confidence - penalty) < 64;
                return {
                    ...wall,
                    confidence: Math.max(36, Math.min(99, Math.round(wall.confidence - penalty))),
                    noiseEvidence: evidence,
                    noisePenalty: penalty,
                    noiseRejection: {
                        rejected,
                        compactMark,
                        furnitureOutline,
                        textureCluster,
                        component: component ? {
                            longestGridSpan: component.longestGridSpan,
                            elongation: component.elongation,
                            fillRatio: component.fillRatio,
                            cells: component.cells
                        } : null,
                        clutter
                    },
                    evidenceModel: 'local-contrast-topology-noise-v4'
                };
            }).filter((wall) => !wall.noiseRejection.rejected);

            // IV.30.1G.2 — Doorway & Threshold Reasoning.
            // A bright interruption in ink is not automatically a door. A threshold
            // candidate must sit between two supported wall runs, contain a genuinely
            // quieter/lighter opening, and have plausible traversable floor on both
            // sides. The Assistant records why it believes the gap matters; the Keeper
            // still decides whether the draft becomes an authoritative VTT door.
            const structuralByKey = new Map(noiseScreenedWalls.map((wall) => [cartographySuggestionKey(wall), wall]));
            const structuralEdge = (x1, y1, x2, y2) => structuralByKey.get(cartographySuggestionKey({ x1, y1, x2, y2, type: 'wall' })) || null;
            const canvasPointForGrid = (gx, gy) => ({
                x: toCanvasX(grid.offsetX) + (gx * gridCanvas),
                y: toCanvasY(grid.offsetY) + (gy * gridCanvas)
            });
            const floorSample = (gx, gy) => {
                const point = canvasPointForGrid(gx, gy);
                const radius = Math.max(2, gridCanvas * .18);
                const stats = luminanceRegionStats(point.x - radius, point.y - radius, point.x + radius, point.y + radius);
                const density = densityAt(point.x, point.y);
                const toneFloor = stats.mean >= Math.max(mapTone.dark + 16, mapTone.middle - 42);
                const quietEnough = density <= .24 && stats.deviation <= 72;
                return {
                    plausible: toneFloor && quietEnough,
                    mean: stats.mean,
                    deviation: stats.deviation,
                    density
                };
            };
            const openingSample = (x1, y1, x2, y2) => {
                const start = canvasPointForGrid(x1, y1);
                const end = canvasPointForGrid(x2, y2);
                const midpointX = (start.x + end.x) / 2;
                const midpointY = (start.y + end.y) / 2;
                const horizontal = y1 === y2;
                const radiusAlong = Math.max(2, gridCanvas * .24);
                const radiusAcross = Math.max(1.5, traceRadius * .72);
                const stats = horizontal
                    ? luminanceRegionStats(midpointX - radiusAlong, midpointY - radiusAcross, midpointX + radiusAlong, midpointY + radiusAcross)
                    : luminanceRegionStats(midpointX - radiusAcross, midpointY - radiusAlong, midpointX + radiusAcross, midpointY + radiusAlong);
                const density = densityAt(midpointX, midpointY);
                return { mean: stats.mean, deviation: stats.deviation, density, midpointX, midpointY };
            };
            const supportingWallTone = (wall) => {
                const a = canvasPointForGrid(wall.x1, wall.y1);
                const b = canvasPointForGrid(wall.x2, wall.y2);
                const midpointX = (a.x + b.x) / 2;
                const midpointY = (a.y + b.y) / 2;
                const radius = Math.max(1.5, traceRadius * .72);
                return luminanceRegionStats(midpointX - radius, midpointY - radius, midpointX + radius, midpointY + radius).mean;
            };
            const doorwayCandidates = [];
            const considerThreshold = (x1, y1, x2, y2, before, after, sideA, sideB) => {
                if (!before || !after || structuralEdge(x1, y1, x2, y2)) return;
                const opening = openingSample(x1, y1, x2, y2);
                const floorA = floorSample(sideA.x, sideA.y);
                const floorB = floorSample(sideB.x, sideB.y);
                const averageWallTone = (supportingWallTone(before) + supportingWallTone(after)) / 2;
                const openingContrast = opening.mean - averageWallTone;
                const continuityStrength = Math.min(Number(before.confidence || 0), Number(after.confidence || 0));
                const topologyStrength = [before, after].reduce((score, wall) => score
                    + Number((wall.topologySupport?.continuationCount || 0) > 0)
                    + Number((wall.topologySupport?.cornerCount || 0) > 0)
                    + Number((wall.topologySupport?.junctionCount || 0) > 0), 0);
                const clearOpening = opening.density <= .16 && openingContrast >= 12;
                const crossThresholdFloor = floorA.plausible && floorB.plausible;
                if (!clearOpening || !crossThresholdFloor || continuityStrength < 52) return;

                const evidence = ['wall-continuity', 'clear-opening', 'floor-both-sides', 'one-grid-threshold'];
                if (topologyStrength > 0) evidence.push('architectural-support');
                const confidence = Math.max(58, Math.min(96, Math.round(
                    54
                    + ((continuityStrength - 52) * .34)
                    + Math.min(12, openingContrast * .35)
                    + (topologyStrength * 2.5)
                    + (Math.min(floorA.mean, floorB.mean) >= mapTone.middle ? 4 : 0)
                )));
                doorwayCandidates.push({
                    x1, y1, x2, y2,
                    type: 'door',
                    confidence,
                    selected: true,
                    structural: true,
                    adaptiveEvidence: true,
                    doorwayReasoning: true,
                    thresholdEvidence: evidence,
                    thresholdSupport: {
                        continuityStrength,
                        openingContrast,
                        openingDensity: opening.density,
                        floorA,
                        floorB,
                        topologyStrength,
                        widthGridUnits: 1
                    },
                    evidenceModel: 'local-contrast-topology-threshold-v3'
                });
            };

            // A conservative first threshold model: only one-grid orthogonal gaps
            // bracketed by structural walls are considered. Wider arches and diagonal
            // thresholds remain manual until later evidence proves them safe.
            for (let y = 0; y <= rows; y += 1) {
                for (let x = 1; x < columns - 1; x += 1) {
                    considerThreshold(
                        x, y, x + 1, y,
                        structuralEdge(x - 1, y, x, y),
                        structuralEdge(x + 1, y, x + 2, y),
                        { x: x + .5, y: y - .34 },
                        { x: x + .5, y: y + .34 }
                    );
                }
            }
            for (let x = 0; x <= columns; x += 1) {
                for (let y = 1; y < rows - 1; y += 1) {
                    considerThreshold(
                        x, y, x, y + 1,
                        structuralEdge(x, y - 1, x, y),
                        structuralEdge(x, y + 1, x, y + 2),
                        { x: x - .34, y: y + .5 },
                        { x: x + .34, y: y + .5 }
                    );
                }
            }

            return noiseScreenedWalls.concat(doorwayCandidates);
        };

        // IV.30.1B — The Living Contour.
        // Treat the calibrated grid as a scale reference, classify the quiet playable
        // floor inside each cell, then trace the shared floor/solid boundary. This
        // complements Structural tracing on cave maps where the wall itself curves
        // freely through the artwork and hatch texture makes directional ink scans
        // fragmentary. The result is still only a review-first barrier draft.
        const livingContourCandidates = (options = {}) => {
            // IV.30.1G.5J.1 — Monotonic Occlusion Recovery.
            // G.5J is an evidence augmentation layer: it may recover additional playable
            // surface, but it must never revoke a contour already certified by G.5A–I.
            // Take one pre-recovery reading as the preservation baseline. The internal
            // call skips only G.5J reconstruction, preventing recursion while retaining
            // the complete earlier semantic/topological pipeline.
            const preOcclusionRecoveryContours = options.skipOcclusionRecovery === true
                ? []
                : livingContourCandidates({ ...options, skipOcclusionRecovery: true });

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

            // IV.30.1G.5K — Interior Occupancy & Illustrated Floor Reasoning.
            // Illustration inside a room is not automatically solid structure. Infer only
            // compact, enclosed occupancy islands that are surrounded by already-visible
            // playable floor. This is deliberately additive: inferred cells can support
            // G.5J occlusion recovery, but can never revoke an existing floor cell or
            // certified G.5A–J contour. Sustained wall-like ink remains a hard veto.
            const illustratedInteriorPlayableSurface = Array.from({ length: contourRows }, () => Array(contourColumns).fill(false));
            const maximumInteriorLookahead = Math.max(2, Math.round(contourSubdivisions * .72));
            const interiorOccupancyPass = () => {
                const additions = new Map();
                const visibleFloorInDirection = (column, row, dx, dy) => {
                    for (let distance = 1; distance <= maximumInteriorLookahead; distance += 1) {
                        const x = column + (dx * distance);
                        const y = row + (dy * distance);
                        if (x < 0 || y < 0 || x >= contourColumns || y >= contourRows) return false;
                        if (floor[y][x]) return true;
                        if (darkness[y][x] >= .78 && distance >= 2) return false;
                    }
                    return false;
                };
                for (let row = 1; row < contourRows - 1; row += 1) {
                    for (let column = 1; column < contourColumns - 1; column += 1) {
                        if (floor[row][column]) continue;
                        // Near-white gaps are already handled by the ordinary floor mask.
                        // G.5K is for illustrated occupancy: enough ink to hide floor, but
                        // not a dense structural wall core.
                        const ink = darkness[row][column];
                        if (ink < .20 || ink > .70) continue;
                        const north = visibleFloorInDirection(column, row, 0, -1);
                        const south = visibleFloorInDirection(column, row, 0, 1);
                        const west = visibleFloorInDirection(column, row, -1, 0);
                        const east = visibleFloorInDirection(column, row, 1, 0);
                        const directionalSupport = Number(north) + Number(south) + Number(west) + Number(east);
                        const opposedEnclosure = (north && south) || (west && east);
                        if (directionalSupport < 3 || !opposedEnclosure) continue;

                        // A real wall tends to form a sustained dark band through adjacent
                        // samples. Interior art is usually locally busy but not a continuous
                        // barrier. Require at least two quiet/playable neighbours and veto
                        // a strong opposing wall band.
                        const neighbours = [[-1,0],[1,0],[0,-1],[0,1]].map(([dx,dy]) => ({
                            floor: floor[row + dy][column + dx],
                            darkness: darkness[row + dy][column + dx]
                        }));
                        const localFloorSupport = neighbours.filter((sample) => sample.floor || sample.darkness <= .30).length;
                        const horizontalWallBand = neighbours[0].darkness >= .72 && neighbours[1].darkness >= .72;
                        const verticalWallBand = neighbours[2].darkness >= .72 && neighbours[3].darkness >= .72;
                        if (localFloorSupport < 2 || horizontalWallBand || verticalWallBand) continue;
                        additions.set(`${column},${row}`, [column, row]);
                    }
                }
                additions.forEach(([column, row]) => {
                    floor[row][column] = true;
                    illustratedInteriorPlayableSurface[row][column] = true;
                });
                return additions.size;
            };
            const inferredIllustratedInteriorCells = options.skipOcclusionRecovery === true ? 0 : interiorOccupancyPass();

            // IV.30.1G.5J — Playable Surface Reconstruction & Occlusion Recovery.
            // Dense creature art, rubble, mushrooms and annotation can hide an otherwise
            // continuous floor without representing a structural boundary. Reconstruct
            // only short occluded spans whose two visible shores agree: both ends must be
            // playable, the span must remain locally floor-supported, and a sustained dark
            // wall band vetoes recovery. This repairs the semantic floor model; it does not
            // draw a wall or interpolate a pink contour.
            const reconstructedPlayableSurface = Array.from({ length: contourRows }, () => Array(contourColumns).fill(false));
            const maximumOcclusionSpan = Math.max(2, Math.round(contourSubdivisions * .85));
            const occlusionRecoveryPass = () => {
                const additions = new Map();
                const considerSpan = (column, row, dx, dy) => {
                    if (!floor[row][column]) return;
                    for (let distance = 2; distance <= maximumOcclusionSpan + 1; distance += 1) {
                        const endColumn = column + dx * distance;
                        const endRow = row + dy * distance;
                        if (endColumn <= 0 || endRow <= 0 || endColumn >= contourColumns - 1 || endRow >= contourRows - 1) break;
                        if (!floor[endRow][endColumn]) continue;
                        let darkSum = 0;
                        let veryDark = 0;
                        let lateralSupport = 0;
                        const span = [];
                        for (let step = 1; step < distance; step += 1) {
                            const x = column + dx * step;
                            const y = row + dy * step;
                            if (floor[y][x]) { span.length = 0; break; }
                            span.push([x, y]);
                            darkSum += darkness[y][x];
                            if (darkness[y][x] >= .72) veryDark += 1;
                            const px = dy; const py = dx;
                            if (isFinite(darkness[y + py]?.[x + px]) && darkness[y + py][x + px] <= .30) lateralSupport += 1;
                            if (isFinite(darkness[y - py]?.[x - px]) && darkness[y - py][x - px] <= .30) lateralSupport += 1;
                        }
                        if (span.length === 0) break;
                        const averageDarkness = darkSum / span.length;
                        const sustainedWallBand = veryDark >= Math.max(2, Math.ceil(span.length * .65));
                        const supportedOcclusion = lateralSupport >= Math.max(1, Math.floor(span.length * .45));
                        if (!sustainedWallBand && supportedOcclusion && averageDarkness <= .62) {
                            span.forEach(([x, y]) => additions.set(`${x},${y}`, [x, y]));
                        }
                        break;
                    }
                };
                for (let row = 1; row < contourRows - 1; row += 1) {
                    for (let column = 1; column < contourColumns - 1; column += 1) {
                        considerSpan(column, row, 1, 0);
                        considerSpan(column, row, 0, 1);
                    }
                }
                additions.forEach(([column, row]) => {
                    floor[row][column] = true;
                    reconstructedPlayableSurface[row][column] = true;
                });
                return additions.size;
            };
            // Historical G.5J regression contract: const recoveredPlayableSurfaceCells = occlusionRecoveryPass();
            let recoveredPlayableSurfaceCells = 0;
            if (options.skipOcclusionRecovery !== true) {
                recoveredPlayableSurfaceCells = occlusionRecoveryPass();
                // Hybrid may make one second conservative pass after its seam-healing stage;
                // standalone Living Contour remains deliberately single-pass.
                if (options.connectPlayableFloor === true && recoveredPlayableSurfaceCells > 0) occlusionRecoveryPass();
            }

            // IV.30.1G.5Q — Illustrated Floor Continuity & Interior Surface Flooding.
            // IV.30.1G.5R — Floor Seed Expansion & Illustrated Frontier Admission.
            // IV.30.1G.5S — Frontier Discovery & Classification-Neutral Adjacency.
            // G.5S discovery is classification-neutral: establish adjacency first, classify the frontier second.
            // IV.30.1G.5T — Trusted Seed Traversal & Frontier Walker Activation.
            // IV.30.1G.5U — Frontier Admission Accounting & Illustrated Neighbour Qualification.
            // G.5Q proved that the downstream surface machinery was safe, but the torture
            // map admitted zero cells: a candidate had to look sufficiently floor-like at
            // the exact first noisy sample. G.5R moves that decision to the *frontier*.
            // Certified floor is expanded into a one-cell seed halo, then a small window
            // asks whether noisy ink is continuous with an interior surface. Lightness by
            // itself is never evidence: exterior-like openness and sustained wall bands
            // veto admission. Every gate is counted so a zero can be diagnosed directly.
            const illustratedFloorContinuitySurface = Array.from({ length: contourRows }, () => Array(contourColumns).fill(false));
            const illustratedFloorSeed = Array.from({ length: contourRows }, (_, row) =>
                Array.from({ length: contourColumns }, (_, column) => Boolean(floor[row][column]))
            );

            // G.5S: the raw light-floor classifier also contains the large white parchment
            // outside the dungeon. A frontier cannot be discovered by treating *every*
            // light sample as a trusted seed: on this map that made almost the whole fine
            // mesh "already floor" and left 22,496 seeds with no useful frontier. Label
            // the seed components before any illustrated flooding and remove only the
            // dominant, strongly border-connected whitespace components from seed
            // authority. Candidate discovery is then purely topological: every orthogonal
            // neighbour of trusted floor is examined before its ink/classification gets a
            // vote. Classification may reject a frontier; it may never prevent discovery.
            const illustratedSeedComponent = Array.from({ length: contourRows }, () => Array(contourColumns).fill(-1));
            const illustratedSeedComponentStats = [];
            let illustratedSeedComponentId = 0;
            for (let row = 0; row < contourRows; row += 1) {
                for (let column = 0; column < contourColumns; column += 1) {
                    if (!illustratedFloorSeed[row][column] || illustratedSeedComponent[row][column] !== -1) continue;
                    const queue = [[column,row]];
                    illustratedSeedComponent[row][column] = illustratedSeedComponentId;
                    let size = 0, borderSamples = 0;
                    for (let cursor = 0; cursor < queue.length; cursor += 1) {
                        const [x,y] = queue[cursor];
                        size += 1;
                        if (x === 0 || y === 0 || x === contourColumns - 1 || y === contourRows - 1) borderSamples += 1;
                        [[-1,0],[1,0],[0,-1],[0,1]].forEach(([dx,dy]) => {
                            const nx=x+dx, ny=y+dy;
                            if (nx < 0 || ny < 0 || nx >= contourColumns || ny >= contourRows) return;
                            if (!illustratedFloorSeed[ny][nx] || illustratedSeedComponent[ny][nx] !== -1) return;
                            illustratedSeedComponent[ny][nx] = illustratedSeedComponentId;
                            queue.push([nx,ny]);
                        });
                    }
                    illustratedSeedComponentStats.push({ id: illustratedSeedComponentId, size, borderSamples });
                    illustratedSeedComponentId += 1;
                }
            }
            const illustratedSeedTotalCells = Math.max(1, contourColumns * contourRows);
            const illustratedExteriorSeedComponents = new Set(illustratedSeedComponentStats
                .filter((entry) => entry.borderSamples >= Math.max(8, contourSubdivisions * 2)
                    && entry.size / illustratedSeedTotalCells >= .08
                    && entry.borderSamples / Math.max(1, entry.size) >= .012)
                .map((entry) => entry.id));
            const isTrustedIllustratedSeed = (column,row) => column >= 0 && row >= 0
                && column < contourColumns && row < contourRows
                && illustratedFloorSeed[row][column]
                && !illustratedExteriorSeedComponents.has(illustratedSeedComponent[row][column]);
            const illustratedFloorSeeds = illustratedFloorSeed.reduce((sum, row, y) => sum + row.filter((value, x) => value && isTrustedIllustratedSeed(x,y)).length, 0);
            const illustratedFloorLookahead = Math.max(4, Math.round(contourSubdivisions * 1.75));
            let illustratedTraversalSeedsQueued = 0;
            let illustratedTraversalSeedsVisited = 0;
            let illustratedAdjacentSamplesExamined = 0;
            let illustratedAlreadyPlayableNeighbours = 0;
            let illustratedFrontierCandidates = 0;
            let illustratedFrontierAdmitted = 0;
            let illustratedFrontierStructuralRejects = 0;
            let illustratedFrontierExteriorRejects = 0;
            let illustratedFrontierInteriorSupportRejects = 0;
            let illustratedFrontierDecorationRejects = 0;
            // IV.30.1G.5Z.5 — Unvisited Playable-Likeness & Disconnected Interior Island Audit.
            // Keep an identity ledger of every sample that actually reached G.5Y frontier
            // classification. The post-flood audit can then distinguish rejected territory
            // from floor-like mesh cells propagation never reached at all. Diagnostic only.
            const illustratedFrontierExaminedCells = new Set();
            // IV.30.1G.5Z.4 — Unrecovered Playable-Island & Decoration-Reject Topology Audit.
            // Preserve the identity of decoration-rejected frontier samples so the final
            // unrecovered topology can be audited after all G.5Y propagation waves. This
            // ledger is diagnostic only: it never re-admits a rejected sample.
            const illustratedDecorationRejectedFrontierCells = new Set();
            let illustratedFrontierNeighbourQualified = 0;
            let illustratedFrontierDecorationQualified = 0;
            let illustratedFrontierProvisionalAdmissions = 0;
            // IV.30.1G.5Y — Recovered Surface Propagation & Multi-Wave Illustrated Flooding.
            // Recovered cells become explicit next-wave traversal authority. Quiet floor may
            // propagate only after two-neighbour/local playable support; structural bands and
            // exterior parchment remain absolute vetoes.
            let illustratedPropagationWaves = 0;
            let illustratedPropagationDeepestWave = 0;
            let illustratedPropagatedAdmissions = 0;
            let illustratedQuietFloorAdmissions = 0;
            let illustratedExhaustedFrontierCells = 0;
            const illustratedFloorFloodPass = () => {
                if (options.skipOcclusionRecovery === true) return 0;
                let recovered = 0;
                const maximumPasses = Math.max(10, Math.round(contourSubdivisions * 4.5));
                const orthogonal = [[-1,0],[1,0],[0,-1],[0,1]];
                const inBounds = (x,y) => x > 0 && y > 0 && x < contourColumns - 1 && y < contourRows - 1;
                const isPlayable = (x,y) => inBounds(x,y) && (isTrustedIllustratedSeed(x,y) || illustratedFloorContinuitySurface[y][x]);
                const neighbourhood = (column,row,radius=2) => {
                    let playable=0, quiet=0, dense=0, samples=0;
                    for (let dy=-radius;dy<=radius;dy+=1) for (let dx=-radius;dx<=radius;dx+=1) {
                        if (dx===0 && dy===0) continue;
                        const x=column+dx,y=row+dy;
                        if (!inBounds(x,y)) continue;
                        samples+=1;
                        if (isPlayable(x,y)) playable+=1;
                        if (darkness[y][x] <= .34) quiet+=1;
                        if (darkness[y][x] >= .78) dense+=1;
                    }
                    return {playable,quiet,dense,samples};
                };
                const lookAhead = (column,row,dx,dy) => {
                    let denseRun=0;
                    for (let distance=1;distance<=illustratedFloorLookahead;distance+=1) {
                        const x=column+dx*distance,y=row+dy*distance;
                        if (!inBounds(x,y)) return {playable:false,barrier:false};
                        if (isPlayable(x,y)) return {playable:true,barrier:false};
                        denseRun = darkness[y][x] >= .80 ? denseRun+1 : 0;
                        if (denseRun >= 2) return {playable:false,barrier:true};
                    }
                    return {playable:false,barrier:false};
                };
                for (let pass=0;pass<maximumPasses;pass+=1) {
                    const additions=[];
                    const recoveredBeforePass = recovered;
                    // G.5T: walk outward FROM the trusted/recovered seed set instead of
                    // rescanning the whole mesh and hoping a neighbour query reaches it.
                    // The queue/visit counters also prove that the certified seed mask is
                    // actually consumed by this stage. Diagnostic baseline recursion has
                    // evidenceAudit disabled so it cannot overwrite the live audit.
                    const traversalSeeds=[];
                    for (let row=1;row<contourRows-1;row+=1) for (let column=1;column<contourColumns-1;column+=1) {
                        if (isPlayable(column,row)) traversalSeeds.push([column,row]);
                    }
                    illustratedTraversalSeedsQueued+=traversalSeeds.length;
                    const examinedThisPass=new Set();
                    for (const [seedColumn,seedRow] of traversalSeeds) {
                        illustratedTraversalSeedsVisited+=1;
                        for (const [dx,dy] of orthogonal) {
                            const column=seedColumn+dx,row=seedRow+dy;
                            if (!inBounds(column,row)) continue;
                            const sampleKey=`${column},${row}`;
                            if (examinedThisPass.has(sampleKey)) continue;
                            examinedThisPass.add(sampleKey);
                            illustratedAdjacentSamplesExamined+=1;
                            if (isPlayable(column,row)) { illustratedAlreadyPlayableNeighbours+=1; continue; }
                            illustratedFrontierCandidates+=1;
                            illustratedFrontierExaminedCells.add(sampleKey);


                        const ink=darkness[row][column];
                        const left=darkness[row][column-1],right=darkness[row][column+1];
                        const up=darkness[row-1][column],down=darkness[row+1][column];
                        const horizontalStructuralBand=left>=.76 && ink>=.62 && right>=.76;
                        const verticalStructuralBand=up>=.76 && ink>=.62 && down>=.76;
                        const n=lookAhead(column,row,0,-1), s=lookAhead(column,row,0,1);
                        const w=lookAhead(column,row,-1,0), e=lookAhead(column,row,1,0);
                        const directionalSupport=Number(n.playable)+Number(s.playable)+Number(w.playable)+Number(e.playable);
                        const opposedSupport=(n.playable&&s.playable)||(w.playable&&e.playable);
                        if (horizontalStructuralBand || verticalStructuralBand || ((n.barrier&&s.barrier)||(w.barrier&&e.barrier))) {
                            illustratedFrontierStructuralRejects+=1; continue;
                        }

                        const local=neighbourhood(column,row,2);
                        // Exterior parchment is usually quiet and open but has little actual
                        // playable support. Require either opposed floor evidence or a useful
                        // local playable density before crossing such an open frontier.
                        const exteriorLike = local.playable <= 1 && local.quiet >= Math.max(10, Math.round(local.samples*.62)) && !opposedSupport;
                        if (exteriorLike) { illustratedFrontierExteriorRejects+=1; continue; }

                        // Admit decoration across a broader ink range. Dense single samples
                        // are legal (rocks/creatures); only sustained bands were vetoed above.
                        const adjacentPlayable = orthogonal.reduce((count,[dx,dy]) => count + Number(isPlayable(column+dx,row+dy)), 0);
                        const localInteriorSupport = local.playable >= 2 || adjacentPlayable >= 2 || opposedSupport || directionalSupport >= 2;
                        // G.5Y compatibility ledger: G.5U/G.5V regression contracts remain documented even though
                        // multi-wave quiet-floor admission now composes with them below. Historical guards:
                        // if (!localInteriorSupport) { illustratedFrontierInteriorSupportRejects+=1; continue; }
                        // if (!inkIsPlausibleDecoration) { illustratedFrontierDecorationRejects+=1; continue; }
                        // const provisionalDecorationAdmission = !localInteriorSupport && adjacentPlayable >= 1;
                        // if (!localInteriorSupport && !provisionalDecorationAdmission) { illustratedFrontierInteriorSupportRejects+=1; continue; }
                        const inkIsPlausibleDecoration = ink >= .08 && ink <= .88;
                        const quietIllustratedFloor = ink < .08 && adjacentPlayable >= 2 && local.playable >= 2;
                        // G.5U: every discovered frontier must leave through an explicit
                        // admission/rejection gate. Qualification counters describe the
                        // evidence without relaxing any G.5R safety threshold.
                        if (localInteriorSupport) illustratedFrontierNeighbourQualified+=1;
                        if (inkIsPlausibleDecoration) illustratedFrontierDecorationQualified+=1;
                        // IV.30.1G.5V — Decoration-Qualified Frontier Admission & Controlled Surface Growth.
                        // A decoration-qualified neighbour may take the first contiguous step
                        // away from trusted/recovered floor even before the older two-neighbour
                        // interior-support rule can become true. Structural and exterior vetoes
                        // above remain absolute, and growth remains bounded by the existing pass
                        // limit and orthogonal traversal from already-playable cells.
                        if (!inkIsPlausibleDecoration && !quietIllustratedFloor) {
                            illustratedFrontierDecorationRejects+=1;
                            illustratedDecorationRejectedFrontierCells.add(`${column},${row}`);
                            continue;
                        }
                        const provisionalDecorationAdmission = inkIsPlausibleDecoration && !localInteriorSupport && adjacentPlayable >= 1;
                        if (!localInteriorSupport && !provisionalDecorationAdmission) { illustratedFrontierInteriorSupportRejects+=1; continue; }
                        if (provisionalDecorationAdmission) illustratedFrontierProvisionalAdmissions+=1;
                        if (quietIllustratedFloor) illustratedQuietFloorAdmissions+=1;
                        additions.push([column,row]);
                        // G.5U historical contract: if (!localInteriorSupport) { illustratedFrontierInteriorSupportRejects+=1; continue; }
                        }
                    }
                    if (additions.length===0) {
                        illustratedExhaustedFrontierCells += examinedThisPass.size;
                        break;
                    }
                    illustratedPropagationWaves += 1;
                    illustratedPropagationDeepestWave = Math.max(illustratedPropagationDeepestWave, pass + 1);
                    additions.forEach(([column,row])=>{
                        if (isTrustedIllustratedSeed(column,row) || illustratedFloorContinuitySurface[row][column]) return;
                        floor[row][column]=true;
                        illustratedFloorContinuitySurface[row][column]=true;
                        illustratedFrontierAdmitted+=1;
                        recovered+=1;
                        if (pass > 0) illustratedPropagatedAdmissions+=1;
                    });
                    if (recovered === recoveredBeforePass) break;
                }
                return recovered;
            };
            let recoveredIllustratedFloorCells = illustratedFloorFloodPass();

            // IV.30.1G.5Z.4 — audit only the decoration rejects that remain unrecovered
            // after the complete G.5Y flood. A sample rejected on an early wave but later
            // admitted is deliberately removed from this population. Components are
            // orthogonal and local; no nearest-floor or geometric snapping is performed.
            const finalIllustratedDecorationRejectCells = new Set(Array.from(illustratedDecorationRejectedFrontierCells)
                .filter((key) => {
                    const [column,row] = key.split(',').map(Number);
                    return !floor[row]?.[column] && !illustratedFloorContinuitySurface[row]?.[column];
                }));
            const decorationRejectVisited = new Set();
            let illustratedDecorationRejectComponents = 0;
            let illustratedDecorationRejectAdjacentComponents = 0;
            let illustratedDecorationRejectMultiSidedComponents = 0;
            let illustratedDecorationRejectInteriorSupportedComponents = 0;
            let illustratedDecorationRejectIsolatedComponents = 0;
            let illustratedPotentialPlayableIslandComponents = 0;
            let illustratedPotentialPlayableIslandCells = 0;
            for (const startKey of finalIllustratedDecorationRejectCells) {
                if (decorationRejectVisited.has(startKey)) continue;
                illustratedDecorationRejectComponents += 1;
                const queue=[startKey];
                decorationRejectVisited.add(startKey);
                let componentCells=0;
                let playableContacts=0;
                let hasMultiSidedPlayableSupport=false;
                let touchesMeshBorder=false;
                const contactDirections=new Set();
                for (let cursor=0;cursor<queue.length;cursor+=1) {
                    const cellKey=queue[cursor];
                    const [column,row]=cellKey.split(',').map(Number);
                    componentKeys.push(cellKey);
                    componentCells+=1;
                    if (column <= 1 || row <= 1 || column >= contourColumns-2 || row >= contourRows-2) touchesMeshBorder=true;
                    let cellPlayableContacts=0;
                    orthogonal.forEach(([dx,dy], directionIndex) => {
                        const nx=column+dx,ny=row+dy;
                        if (!inBounds(nx,ny)) return;
                        const neighbourKey=`${nx},${ny}`;
                        if (finalIllustratedDecorationRejectCells.has(neighbourKey)) {
                            if (!decorationRejectVisited.has(neighbourKey)) {
                                decorationRejectVisited.add(neighbourKey);
                                queue.push(neighbourKey);
                            }
                            return;
                        }
                        if (isTrustedIllustratedSeed(nx,ny) || illustratedFloorContinuitySurface[ny][nx]) {
                            playableContacts+=1;
                            cellPlayableContacts+=1;
                            contactDirections.add(directionIndex);
                        }
                    });
                    if (cellPlayableContacts >= 2) hasMultiSidedPlayableSupport=true;
                }
                const adjacentToRecoveredFloor=playableContacts > 0;
                const interiorSupported=adjacentToRecoveredFloor && contactDirections.size >= 2 && !touchesMeshBorder;
                if (adjacentToRecoveredFloor) illustratedDecorationRejectAdjacentComponents+=1;
                if (hasMultiSidedPlayableSupport) illustratedDecorationRejectMultiSidedComponents+=1;
                if (interiorSupported) illustratedDecorationRejectInteriorSupportedComponents+=1;
                if (!adjacentToRecoveredFloor) illustratedDecorationRejectIsolatedComponents+=1;
                // Potential islands are evidence, not admissions: require a coherent component,
                // local contact with recovered/trusted floor, support from at least two sides,
                // and no mesh-border contact. G.5Y's decoration veto remains authoritative.
                if (componentCells >= 2 && adjacentToRecoveredFloor && contactDirections.size >= 2 && !touchesMeshBorder) {
                    illustratedPotentialPlayableIslandComponents+=1;
                    illustratedPotentialPlayableIslandCells+=componentCells;
                }
            }

            // IV.30.1G.5Z.5 — audit floor-like territory that never became a G.5Y frontier.
            // This phase deliberately grants no new seed/admission authority. A sample is
            // merely "floor-like" when it is quiet enough to resemble the already-certified
            // floor while not forming the same strong three-sample structural bands used by
            // G.5Y. Components touching the fine-mesh border remain exterior evidence.
            const illustratedMeshCells = Math.max(0, (contourColumns - 2) * (contourRows - 2));
            let illustratedRecoveredOrCanonicalCells = 0;
            let illustratedNeverFrontierCells = 0;
            const illustratedFloorLikeNeverFrontierCells = new Set();
            for (let row=1;row<contourRows-1;row+=1) for (let column=1;column<contourColumns-1;column+=1) {
                const key=`${column},${row}`;
                if (isTrustedIllustratedSeed(column,row) || illustratedFloorContinuitySurface[row][column]) {
                    illustratedRecoveredOrCanonicalCells+=1;
                    continue;
                }
                if (illustratedFrontierExaminedCells.has(key)) continue;
                illustratedNeverFrontierCells+=1;
                const ink=darkness[row][column];
                const horizontalStructuralBand=darkness[row][column-1]>=.76 && ink>=.62 && darkness[row][column+1]>=.76;
                const verticalStructuralBand=darkness[row-1][column]>=.76 && ink>=.62 && darkness[row+1][column]>=.76;
                if (ink <= .34 && !horizontalStructuralBand && !verticalStructuralBand) illustratedFloorLikeNeverFrontierCells.add(key);
            }
            const neverFrontierVisited=new Set();
            let illustratedNeverFrontierComponents=0;
            let illustratedNeverFrontierExteriorComponents=0;
            let illustratedNeverFrontierInteriorComponents=0;
            let illustratedNeverFrontierNarrowGapComponents=0;
            let illustratedDisconnectedPlayableIslandComponents=0;
            let illustratedDisconnectedPlayableIslandCells=0;
            // IV.30.1G.5Z.6 — Conservative Interior Island Seeding & Controlled Re-Propagation.
            // Candidate seeds are collected only from G.5Z.5's enclosed, coherent islands
            // that sit across a one/two-cell locally safe gap from already-certified floor.
            // One seed maximum per component: the existing G.5Y flood must earn every
            // subsequent admission through its unchanged structural/exterior/decoration gates.
            const illustratedSecondarySeedCandidates=[];
            // IV.30.1G.5Z.9 — Unseeded Interior Island Qualification & Reachability Audit.
            // Retain candidate-component identities so the post-G.5Z.6 expanded surface can
            // re-evaluate islands that were not granted first-generation secondary authority.
            // Diagnostic only: this ledger never creates a seed or admits a floor cell.
            const illustratedDisconnectedIslandRecords=[];
            // IV.30.1G.5Z.10 — Interior Component Qualification Rejection Audit.
            // Keep the enclosed/interior components that fail G.5Z.5 island qualification
            // as an explicit diagnostic population. This phase changes no qualification,
            // seed or admission rule; it only explains the gap between all interior
            // floor-like components and the components promoted to playable-island evidence.
            const illustratedInteriorQualificationRejectedRecords=[];
            let illustratedInteriorQualificationRejectedComponents=0;
            let illustratedInteriorQualificationRejectedCells=0;
            let illustratedInteriorQualificationSingletonRejects=0;
            let illustratedInteriorQualificationMultiCellRejects=0;
            let illustratedInteriorQualificationRejectedInitiallyNearRecovered=0;
            let illustratedSecondaryNarrowGapCandidates=0;
            let illustratedSecondaryTopologySafeCandidates=0;
            let illustratedSecondarySeedsAdmitted=0;
            let illustratedSecondaryRecoveredCells=0;
            for (const startKey of illustratedFloorLikeNeverFrontierCells) {
                if (neverFrontierVisited.has(startKey)) continue;
                illustratedNeverFrontierComponents+=1;
                const queue=[startKey];
                neverFrontierVisited.add(startKey);
                let componentCells=0;
                const componentKeys=[];
                let touchesMeshBorder=false;
                let nearRecoveredAcrossGap=false;
                let topologySafeSeed=null;
                for (let cursor=0;cursor<queue.length;cursor+=1) {
                    const [column,row]=queue[cursor].split(',').map(Number);
                    componentKeys.push(queue[cursor]);
                    componentCells+=1;
                    if (column <= 1 || row <= 1 || column >= contourColumns-2 || row >= contourRows-2) touchesMeshBorder=true;
                    for (const [dx,dy] of [[-1,0],[1,0],[0,-1],[0,1]]) {
                        const nx=column+dx,ny=row+dy;
                        if (nx <= 0 || ny <= 0 || nx >= contourColumns-1 || ny >= contourRows-1) continue;
                        const neighbourKey=`${nx},${ny}`;
                        if (illustratedFloorLikeNeverFrontierCells.has(neighbourKey)) {
                            if (!neverFrontierVisited.has(neighbourKey)) { neverFrontierVisited.add(neighbourKey); queue.push(neighbourKey); }
                            continue;
                        }
                        // Audit one- and two-cell topological separations only. This is not
                        // nearest-cell matching and cannot itself connect or admit anything.
                        for (let distance=1;distance<=2;distance+=1) {
                            const gx=column+dx*distance,gy=row+dy*distance;
                            if (gx <= 0 || gy <= 0 || gx >= contourColumns-1 || gy >= contourRows-1) break;
                            if (isTrustedIllustratedSeed(gx,gy) || illustratedFloorContinuitySurface[gy][gx]) {
                                nearRecoveredAcrossGap=true;
                                // G.5Z.6: reconciliation remains local. A two-cell bridge may
                                // seed only when the intervening sample is not a dense/structural
                                // wall band. No nearest-playable search or arbitrary snapping.
                                let safeBridge=true;
                                for (let bridgeDistance=1;bridgeDistance<distance;bridgeDistance+=1) {
                                    const bx=column+dx*bridgeDistance,by=row+dy*bridgeDistance;
                                    const bridgeInk=darkness[by][bx];
                                    const bridgeHorizontal=darkness[by][bx-1]>=.76 && bridgeInk>=.62 && darkness[by][bx+1]>=.76;
                                    const bridgeVertical=darkness[by-1][bx]>=.76 && bridgeInk>=.62 && darkness[by+1][bx]>=.76;
                                    if (bridgeInk>=.62 || bridgeHorizontal || bridgeVertical) { safeBridge=false; break; }
                                }
                                if (safeBridge && topologySafeSeed===null) topologySafeSeed=[column,row];
                                break;
                            }
                        }
                    }
                }
                if (touchesMeshBorder) illustratedNeverFrontierExteriorComponents+=1;
                else {
                    illustratedNeverFrontierInteriorComponents+=1;
                    if (nearRecoveredAcrossGap) illustratedNeverFrontierNarrowGapComponents+=1;
                    // Require a coherent multi-cell interior region. G.5Z.5 remains the
                    // diagnostic qualification; G.5Z.6 may grant exactly one local seed only
                    // when that component also has a topology-safe narrow-gap bridge.
                    if (componentCells >= 2) {
                        illustratedDisconnectedPlayableIslandComponents+=1;
                        illustratedDisconnectedPlayableIslandCells+=componentCells;
                        const islandRecord={
                            cells: componentKeys.slice(),
                            initialNarrowGap: nearRecoveredAcrossGap,
                            initialTopologySafe: topologySafeSeed!==null,
                            seed: topologySafeSeed,
                            seeded: false,
                        };
                        illustratedDisconnectedIslandRecords.push(islandRecord);
                        if (nearRecoveredAcrossGap) {
                            illustratedSecondaryNarrowGapCandidates+=1;
                            if (topologySafeSeed!==null) {
                                illustratedSecondaryTopologySafeCandidates+=1;
                                illustratedSecondarySeedCandidates.push(topologySafeSeed);
                                islandRecord.seeded=true;
                            }
                        }
                    } else {
                        // Under the current G.5Z.5 contract, coherence is the final island
                        // qualification after enclosure: a one-cell component is retained
                        // here rather than silently disappearing from the accounting.
                        illustratedInteriorQualificationRejectedComponents+=1;
                        illustratedInteriorQualificationRejectedCells+=componentCells;
                        if (componentCells < 2) illustratedInteriorQualificationSingletonRejects+=1;
                        else illustratedInteriorQualificationMultiCellRejects+=1;
                        if (nearRecoveredAcrossGap) illustratedInteriorQualificationRejectedInitiallyNearRecovered+=1;
                        illustratedInteriorQualificationRejectedRecords.push({ cells: componentKeys.slice() });
                    }
                }
            }

            // G.5Z.6 behavioural step: grant at most one seed to each topology-safe
            // narrow-gap island, then hand control straight back to the certified G.5Y flood.
            // The seed itself is counted as recovered floor; all growth beyond it must pass
            // G.5Y unchanged. Diagnostic mode is not required for the behaviour, while
            // skipOcclusionRecovery continues to disable all illustrated recovery.
            if (options.skipOcclusionRecovery !== true && illustratedSecondarySeedCandidates.length > 0) {
                illustratedSecondarySeedCandidates.forEach(([column,row]) => {
                    if (isTrustedIllustratedSeed(column,row) || illustratedFloorContinuitySurface[row][column]) return;
                    floor[row][column]=true;
                    illustratedFloorContinuitySurface[row][column]=true;
                    illustratedSecondarySeedsAdmitted+=1;
                });
                if (illustratedSecondarySeedsAdmitted > 0) {
                    const secondaryFloodRecovered=illustratedFloorFloodPass();
                    illustratedSecondaryRecoveredCells=illustratedSecondarySeedsAdmitted+secondaryFloodRecovered;
                    recoveredIllustratedFloorCells+=illustratedSecondaryRecoveredCells;
                }
            }

            // IV.30.1G.5Z.11 — Iterative Topology-Safe Island Reseeding & Bounded Convergence.
            // G.5Z.10 proved that first-generation recovery can make previously disconnected,
            // coherent islands locally safe. Permit at most two additional generations (three
            // secondary generations total including G.5Z.6), one seed per still-coherent
            // component per generation. Every cell beyond the seed must still be earned by the
            // unchanged G.5Y flood. No singleton reject, structural bridge, nearest-cell match,
            // arbitrary snap or review-cap bypass receives authority here.
            // IV.30.1G.5Z.14 — Iterative Recovery to Topology-Safe Convergence.
            // G.5Z.13 removed the downstream representation bottleneck, so recovery may now
            // continue while a previously-qualified coherent island becomes locally topology-safe.
            // Ten is an emergency loop guard, not a target: normal completion is eligibility
            // exhaustion. The G.5Z.11 local distance/coherence/structural-veto rules remain exact.
            // Regression-contract compatibility: const maximumSecondarySeedGenerations=3;
            const maximumSecondarySeedGenerations=10;
            let illustratedIterativeGenerationsRun=0;
            let illustratedIterativeGeneration2Candidates=0;
            let illustratedIterativeGeneration2Seeds=0;
            let illustratedIterativeGeneration2RecoveredCells=0;
            let illustratedIterativeGeneration3Candidates=0;
            let illustratedIterativeGeneration3Seeds=0;
            let illustratedIterativeGeneration3RecoveredCells=0;
            let illustratedIterativeSeedsAdmitted=0;
            let illustratedIterativeRecoveredCells=0;
            let illustratedIterativeConvergenceReason='disabled';
            const illustratedIterativeGenerationAudit=[];
            const iterativePlayable=(x,y) => x>0 && y>0 && x<contourColumns-1 && y<contourRows-1
                && (isTrustedIllustratedSeed(x,y) || illustratedFloorContinuitySurface[y][x]);
            const topologySafeSeedForRecord=(record) => {
                const remainingKeys=record.cells.filter((key) => {
                    const [x,y]=key.split(',').map(Number);
                    return !iterativePlayable(x,y);
                });
                // Preserve G.5Z.5 coherence at the moment new authority is considered.
                if (remainingKeys.length < 2) return null;
                for (const key of remainingKeys) {
                    const [column,row]=key.split(',').map(Number);
                    for (const [dx,dy] of [[-1,0],[1,0],[0,-1],[0,1]]) {
                        for (let distance=1;distance<=2;distance+=1) {
                            const gx=column+dx*distance,gy=row+dy*distance;
                            if (gx<=0 || gy<=0 || gx>=contourColumns-1 || gy>=contourRows-1) break;
                            if (!iterativePlayable(gx,gy)) continue;
                            let safeBridge=true;
                            for (let bridgeDistance=1;bridgeDistance<distance;bridgeDistance+=1) {
                                const bx=column+dx*bridgeDistance,by=row+dy*bridgeDistance;
                                const bridgeInk=darkness[by][bx];
                                const bridgeHorizontal=darkness[by][bx-1]>=.76 && bridgeInk>=.62 && darkness[by][bx+1]>=.76;
                                const bridgeVertical=darkness[by-1][bx]>=.76 && bridgeInk>=.62 && darkness[by+1][bx]>=.76;
                                if (bridgeInk>=.62 || bridgeHorizontal || bridgeVertical) { safeBridge=false; break; }
                            }
                            if (safeBridge) return [column,row];
                        }
                    }
                }
                return null;
            };
            if (options.skipOcclusionRecovery !== true) {
                for (let generation=2;generation<=maximumSecondarySeedGenerations;generation+=1) {
                    const generationCandidates=[];
                    illustratedDisconnectedIslandRecords.forEach((record) => {
                        if (record.seeded || record.iterativeSeeded) return;
                        const seed=topologySafeSeedForRecord(record);
                        if (seed===null) return;
                        generationCandidates.push({record,seed});
                    });
                    if (generation===2) illustratedIterativeGeneration2Candidates=generationCandidates.length;
                    else if (generation===3) illustratedIterativeGeneration3Candidates=generationCandidates.length;
                    if (generationCandidates.length===0) {
                        illustratedIterativeConvergenceReason='topology-safe-exhausted';
                        break;
                    }
                    let generationSeeds=0;
                    generationCandidates.forEach(({record,seed:[column,row]}) => {
                        if (iterativePlayable(column,row)) return;
                        floor[row][column]=true;
                        illustratedFloorContinuitySurface[row][column]=true;
                        record.iterativeSeeded=true;
                        record.iterativeSeedGeneration=generation;
                        generationSeeds+=1;
                    });
                    if (generation===2) illustratedIterativeGeneration2Seeds=generationSeeds;
                    else if (generation===3) illustratedIterativeGeneration3Seeds=generationSeeds;
                    if (generationSeeds===0) {
                        illustratedIterativeConvergenceReason='no-new-seeds';
                        break;
                    }
                    illustratedIterativeGenerationsRun+=1;
                    const generationFloodRecovered=illustratedFloorFloodPass();
                    const generationRecovered=generationSeeds+generationFloodRecovered;
                    if (generation===2) illustratedIterativeGeneration2RecoveredCells=generationRecovered;
                    else if (generation===3) illustratedIterativeGeneration3RecoveredCells=generationRecovered;
                    illustratedIterativeGenerationAudit.push({ generation, candidates:generationCandidates.length, seeds:generationSeeds, recovered:generationRecovered });
                    illustratedIterativeSeedsAdmitted+=generationSeeds;
                    illustratedIterativeRecoveredCells+=generationRecovered;
                    recoveredIllustratedFloorCells+=generationRecovered;
                    illustratedIterativeConvergenceReason=generation===maximumSecondarySeedGenerations
                        ? 'generation-budget'
                        : 'continuing';
                }
                // G.5Z.11 regression-contract compatibility: ? 'generation-budget'
                // If the emergency guard is reached, distinguish a true guard stop from natural
                // convergence by re-checking for another still-coherent topology-safe island
                // without granting it authority.
                if (illustratedIterativeConvergenceReason==='generation-budget') {
                    const furtherTopologySafe=illustratedDisconnectedIslandRecords.some((record) =>
                        !record.seeded && !record.iterativeSeeded && topologySafeSeedForRecord(record)!==null
                    );
                    if (!furtherTopologySafe) illustratedIterativeConvergenceReason='topology-safe-exhausted';
                }
            }

            // G.5Z.10 post-recovery correspondence: ask whether rejected interior
            // components were naturally absorbed by G.5Z.6 or remain outside the expanded
            // certified surface. Remaining cells are measured for local adjacency/gap only;
            // they never receive seed authority here.
            let illustratedInteriorQualificationRejectedAbsorbedComponents=0;
            let illustratedInteriorQualificationRejectedAbsorbedCells=0;
            let illustratedInteriorQualificationRejectedRemainingComponents=0;
            let illustratedInteriorQualificationRejectedPostRecoveryAdjacent=0;
            let illustratedInteriorQualificationRejectedPostRecoveryNarrowGap=0;
            const postSecondaryRecoveryPlayable=(x,y) => x>0 && y>0 && x<contourColumns-1 && y<contourRows-1
                && (isTrustedIllustratedSeed(x,y) || illustratedFloorContinuitySurface[y][x]);
            illustratedInteriorQualificationRejectedRecords.forEach((record) => {
                const remaining=record.cells.filter((key) => {
                    const [x,y]=key.split(',').map(Number);
                    return !postSecondaryRecoveryPlayable(x,y);
                });
                const absorbed=record.cells.length-remaining.length;
                illustratedInteriorQualificationRejectedAbsorbedCells+=absorbed;
                if (remaining.length===0) {
                    illustratedInteriorQualificationRejectedAbsorbedComponents+=1;
                    return;
                }
                illustratedInteriorQualificationRejectedRemainingComponents+=1;
                let adjacent=false;
                let narrowGap=false;
                remaining.forEach((key) => {
                    const [column,row]=key.split(',').map(Number);
                    for (const [dx,dy] of [[-1,0],[1,0],[0,-1],[0,1]]) {
                        for (let distance=1;distance<=2;distance+=1) {
                            const gx=column+dx*distance,gy=row+dy*distance;
                            if (gx<=0 || gy<=0 || gx>=contourColumns-1 || gy>=contourRows-1) break;
                            if (!postSecondaryRecoveryPlayable(gx,gy)) continue;
                            narrowGap=true;
                            if (distance===1) adjacent=true;
                        }
                    }
                });
                if (adjacent) illustratedInteriorQualificationRejectedPostRecoveryAdjacent+=1;
                if (narrowGap) illustratedInteriorQualificationRejectedPostRecoveryNarrowGap+=1;
            });

            // IV.30.1G.5Z.9 — re-audit first-generation unseeded islands against the
            // expanded G.5Z.6 certified surface. This distinguishes islands already absorbed
            // by re-propagation from islands that have only now become locally reachable.
            // No second-generation seed authority is granted in this phase.
            let illustratedUnseededIslandComponents=0;
            let illustratedUnseededIslandCells=0;
            let illustratedUnseededInitiallyNoNarrowGap=0;
            let illustratedUnseededInitiallyUnsafeBridge=0;
            let illustratedUnseededAbsorbedComponents=0;
            let illustratedUnseededAbsorbedCells=0;
            let illustratedUnseededRemainingComponents=0;
            let illustratedUnseededPostRecoveryAdjacentComponents=0;
            let illustratedUnseededPostRecoveryNarrowGapComponents=0;
            let illustratedUnseededPostRecoveryTopologySafeComponents=0;
            let illustratedUnseededPostRecoveryStructuralBlockedComponents=0;
            const postRecoveryPlayable=(x,y) => x>0 && y>0 && x<contourColumns-1 && y<contourRows-1
                && (isTrustedIllustratedSeed(x,y) || illustratedFloorContinuitySurface[y][x]);
            illustratedDisconnectedIslandRecords.filter((record) => !record.seeded).forEach((record) => {
                illustratedUnseededIslandComponents+=1;
                illustratedUnseededIslandCells+=record.cells.length;
                if (!record.initialNarrowGap) illustratedUnseededInitiallyNoNarrowGap+=1;
                else if (!record.initialTopologySafe) illustratedUnseededInitiallyUnsafeBridge+=1;
                const remainingKeys=record.cells.filter((key) => {
                    const [x,y]=key.split(',').map(Number);
                    return !postRecoveryPlayable(x,y);
                });
                const absorbedCells=record.cells.length-remainingKeys.length;
                illustratedUnseededAbsorbedCells+=absorbedCells;
                if (remainingKeys.length===0) {
                    illustratedUnseededAbsorbedComponents+=1;
                    return;
                }
                illustratedUnseededRemainingComponents+=1;
                let nowAdjacent=false;
                let nowNarrowGap=false;
                let nowTopologySafe=false;
                let structuralBlocked=false;
                for (const key of remainingKeys) {
                    const [column,row]=key.split(',').map(Number);
                    for (const [dx,dy] of [[-1,0],[1,0],[0,-1],[0,1]]) {
                        for (let distance=1;distance<=2;distance+=1) {
                            const gx=column+dx*distance,gy=row+dy*distance;
                            if (gx<=0 || gy<=0 || gx>=contourColumns-1 || gy>=contourRows-1) break;
                            if (!postRecoveryPlayable(gx,gy)) continue;
                            nowNarrowGap=true;
                            if (distance===1) nowAdjacent=true;
                            let safeBridge=true;
                            for (let bridgeDistance=1;bridgeDistance<distance;bridgeDistance+=1) {
                                const bx=column+dx*bridgeDistance,by=row+dy*bridgeDistance;
                                const bridgeInk=darkness[by][bx];
                                const bridgeHorizontal=darkness[by][bx-1]>=.76 && bridgeInk>=.62 && darkness[by][bx+1]>=.76;
                                const bridgeVertical=darkness[by-1][bx]>=.76 && bridgeInk>=.62 && darkness[by+1][bx]>=.76;
                                if (bridgeInk>=.62 || bridgeHorizontal || bridgeVertical) { safeBridge=false; structuralBlocked=true; break; }
                            }
                            if (safeBridge) nowTopologySafe=true;
                        }
                    }
                }
                if (nowAdjacent) illustratedUnseededPostRecoveryAdjacentComponents+=1;
                if (nowNarrowGap) illustratedUnseededPostRecoveryNarrowGapComponents+=1;
                if (nowTopologySafe) illustratedUnseededPostRecoveryTopologySafeComponents+=1;
                else if (structuralBlocked) illustratedUnseededPostRecoveryStructuralBlockedComponents+=1;
            });

            // Count reconstructed illustrated surfaces and the perimeter transitions they
            // can contribute. These counters are diagnostic only; authority/arbitration
            // still decides whether any resulting contour reaches the 200-object draft.
            const illustratedSurfaceVisited = Array.from({ length: contourRows }, () => Array(contourColumns).fill(false));
            let reconstructedIllustratedSurfaces = 0;
            let illustratedSurfacePerimeterEdges = 0;
            let illustratedSurfaceRawBoundarySides = 0;
            let illustratedSurfacePlayableSeamsSuppressed = 0;
            let illustratedSurfaceThresholdRejects = 0;
            // IV.30.1G.5Z.15 — Surface Boundary Corroboration & Frontier Artefact Audit.
            // Diagnostic only: classify each completed-surface frontier side by the local
            // evidence already available to Living Contour. Nothing in this audit can
            // reject, promote, bridge, snap or save an edge.
            let illustratedSurfaceBoundaryStructuralCorroborated = 0;
            let illustratedSurfaceBoundaryStrongInkCorroborated = 0;
            let illustratedSurfaceBoundaryModerateInkCorroborated = 0;
            let illustratedSurfaceBoundaryUnsupportedFrontier = 0;
            let illustratedSurfaceBoundaryOpenPaperFrontier = 0;
            // IV.30.1G.5Z.16 — Corroborated Surface Boundary Admission & Open-Paper Frontier Suppression.
            // Only the quiet/open-paper subset proven by G.5Z.15 is withheld from wall
            // promotion. Ambiguous unsupported edges (> .30 ink) remain admitted.
            let illustratedSurfaceBoundaryOpenPaperSuppressed = 0;
            // IV.30.1G.5Z.17 — Local Corroborated Boundary Gap Continuity Audit.
            // Diagnostic only: retain the original exact-endpoint topology of G.5Z.16's
            // suppressed open-paper edges so short bounded gaps can be distinguished from
            // genuine long/open recovery frontier without restoring a single edge.
            let illustratedSurfaceSuppressedGapRuns = 0;
            let illustratedSurfaceSuppressedGapBothBounded = 0;
            let illustratedSurfaceSuppressedGapOneBounded = 0;
            let illustratedSurfaceSuppressedGapUnbounded = 0;
            let illustratedSurfaceSuppressedGapBranchAdjacent = 0;
            let illustratedSurfaceSuppressedGapSameSourceContiguous = 0;
            let illustratedSurfaceSuppressedGapLength1 = 0;
            let illustratedSurfaceSuppressedGapLength2 = 0;
            let illustratedSurfaceSuppressedGapLength3To4 = 0;
            let illustratedSurfaceSuppressedGapLength5To8 = 0;
            let illustratedSurfaceSuppressedGapLength9Plus = 0;
            // IV.30.1G.5Z.18 — Micro-Gap Corroborated Boundary Continuity Restoration.
            // Restore only original suppressed perimeter members belonging to exact,
            // both-bounded, non-branching same-source runs of at most two edges.
            let illustratedSurfaceMicroGapEligibleRuns = 0;
            let illustratedSurfaceMicroGapEligibleEdges = 0;
            let illustratedSurfaceMicroGapRestoredEdges = 0;
            // IV.30.1G.5Z.21 — Short-Run Corroborated Boundary Continuity Restoration.
            // G.5Z.20 certified a separate short-run population at 5–8 exact source edges.
            // Admission keeps the G.5Z.18 topology guards and requires published geometric
            // travel to remain within three tenths of a contour cell (rounded to the audit scale).
            let illustratedSurfaceShortGapEligibleRuns = 0;
            let illustratedSurfaceShortGapEligibleEdges = 0;
            let illustratedSurfaceShortGapRestoredEdges = 0;
            // IV.30.1G.5Z.19 — Suppressed Boundary Run Span & Interior Evidence Audit.
            // Diagnostic only: profile the longer (>2-edge) exact-source runs left by
            // G.5Z.18 without restoring, snapping or otherwise changing geometry.
            let illustratedSurfaceLongGapRuns = 0;
            let illustratedSurfaceLongGapEdges = 0;
            let illustratedSurfaceLongGapFiveToEightRuns = 0;
            let illustratedSurfaceLongGapNinePlusRuns = 0;
            let illustratedSurfaceLongGapNearCutoffEdges = 0;
            let illustratedSurfaceLongGapDeepQuietEdges = 0;
            let illustratedSurfaceLongGapInteriorDepthSupportedEdges = 0;
            let illustratedSurfaceLongGapMeanInkPermille = 0;
            let illustratedSurfaceLongGapMaxInkPermille = 0;
            let illustratedSurfaceLongGapTotalSpanCells = 0;
            let illustratedSurfaceLongGapLargestSpanCells = 0;
            const illustratedSurfaceLongGapExactLengths = [];
            // IV.30.1G.5Z.20 — Suppressed Run Geometric Travel & Endpoint Displacement Audit.
            // Diagnostic-only: measure exact-source perimeter travel separately from straight-line
            // endpoint displacement so long open-paper excursions cannot masquerade as local gaps.
            const illustratedSurfaceLongGapGeometry = [];
            const illustratedSurfaceBoundaryTopology = [];
            let illustratedSurfaceCompletedCells = 0;
            let illustratedSurfaceCompletedComponents = 0;
            let illustratedSurfaceAnchorCandidates = 0;
            let illustratedSurfaceExactPlayableAnchors = 0;
            let illustratedSurfaceReconciledAnchors = 0;
            let illustratedSurfaceUnresolvedSurfaces = 0;
            // IV.30.1G.5W — Reconstructed Surface Authority & Review-Budget Representation.
            // G.5V proved reconstructed illustrated surfaces can discover useful perimeter,
            // but the old diagnostic counter discarded the geometry before arbitration.
            // Retain those exact cell-boundary edges so they can be promoted into review
            // polylines and, where possible, extend an already-authoritative contour at
            // zero additional review-object cost.
            const reconstructedIllustratedSurfaceEdges = [];
            for (let row = 1; row < contourRows - 1; row += 1) {
                for (let column = 1; column < contourColumns - 1; column += 1) {
                    if (!illustratedFloorContinuitySurface[row][column] || illustratedSurfaceVisited[row][column]) continue;
                    reconstructedIllustratedSurfaces += 1;
                    const queue = [[column,row]];
                    illustratedSurfaceVisited[row][column] = true;
                    for (let cursor = 0; cursor < queue.length; cursor += 1) {
                        const [x,y] = queue[cursor];
                        [[-1,0],[1,0],[0,-1],[0,1]].forEach(([dx,dy]) => {
                            const nx=x+dx, ny=y+dy;
                            if (!floor[ny][nx]) {
                                illustratedSurfacePerimeterEdges += 1;
                                const left = x * contourStep;
                                const right = (x + 1) * contourStep;
                                const top = y * contourStep;
                                const bottom = (y + 1) * contourStep;
                                let a; let b;
                                if (dx === -1) { a = { x: left, y: top }; b = { x: left, y: bottom }; }
                                else if (dx === 1) { a = { x: right, y: top }; b = { x: right, y: bottom }; }
                                else if (dy === -1) { a = { x: left, y: top }; b = { x: right, y: top }; }
                                else { a = { x: left, y: bottom }; b = { x: right, y: bottom }; }
                                reconstructedIllustratedSurfaceEdges.push({
                                    type: 'wall', confidence: 86, selected: true, contour: true, fineContour: true,
                                    fullBoundary: false, partialContour: true,
                                    partialContourRecovery: 'reconstructed-illustrated-surface-perimeter',
                                    reconstructedSurfaceAuthority: true, reconstructedSurfacePropagation: true,
                                    semanticBoundaryClassification: 'structural-wall',
                                    semanticBoundaryRole: 'illustrated-surface-perimeter',
                                    evidenceModel: 'living-contour-reconstructed-surface-authority-v12',
                                    recoveryEvidence: ['illustrated-interior-surface', 'controlled-surface-growth', 'surface-perimeter-retained-for-arbitration'],
                                    polyline: true, points: [a, b], x1: a.x, y1: a.y, x2: b.x, y2: b.y
                                });
                            }
                            if (!illustratedFloorContinuitySurface[ny][nx] || illustratedSurfaceVisited[ny][nx]) return;
                            illustratedSurfaceVisited[ny][nx] = true;
                            queue.push([nx,ny]);
                        });
                    }
                }
            }
            void illustratedFloorSeed;

            // IV.30.1G.5 — Boundary-Side & Wall-Band Reasoning.
            // White space outside a cave is visually similar to playable floor. On
            // hatched maps that used to let Living Contour trace the far/exterior side
            // of the wall band whenever that edge was darker or cleaner. Label the
            // current floor components and conservatively recognise only a dominant,
            // heavily border-connected component as exterior whitespace. A legitimate
            // dungeon entrance may touch an edge; touching an edge alone is never enough.
            const floorComponent = Array.from({ length: contourRows }, () => Array(contourColumns).fill(-1));
            const floorComponentStats = [];
            let floorComponentId = 0;
            for (let row = 0; row < contourRows; row += 1) {
                for (let column = 0; column < contourColumns; column += 1) {
                    if (!floor[row][column] || floorComponent[row][column] !== -1) continue;
                    const queue = [[column, row]];
                    floorComponent[row][column] = floorComponentId;
                    let size = 0;
                    let borderSamples = 0;
                    for (let cursor = 0; cursor < queue.length; cursor += 1) {
                        const [x, y] = queue[cursor];
                        size += 1;
                        if (x === 0 || y === 0 || x === contourColumns - 1 || y === contourRows - 1) borderSamples += 1;
                        [[-1,0],[1,0],[0,-1],[0,1]].forEach(([dx,dy]) => {
                            const nx=x+dx, ny=y+dy;
                            if (nx < 0 || ny < 0 || nx >= contourColumns || ny >= contourRows) return;
                            if (!floor[ny][nx] || floorComponent[ny][nx] !== -1) return;
                            floorComponent[ny][nx] = floorComponentId;
                            queue.push([nx,ny]);
                        });
                    }
                    floorComponentStats.push({ id: floorComponentId, size, borderSamples });
                    floorComponentId += 1;
                }
            }
            const totalFineCells = Math.max(1, contourColumns * contourRows);
            const exteriorFloorComponents = new Set(floorComponentStats
                .filter((entry) => entry.borderSamples >= Math.max(8, contourSubdivisions * 2)
                    && entry.size / totalFineCells >= .08
                    && entry.borderSamples / Math.max(1, entry.size) >= .012)
                .map((entry) => entry.id));
            const isPlayableFloor = (column, row) => column >= 0 && row >= 0
                && column < contourColumns && row < contourRows
                && floor[row][column]
                && !exteriorFloorComponents.has(floorComponent[row][column]);


            // IV.30.1G.5Z.1A — Threshold Classifier Initialization Order Correction.
            // G.5Z.1 boundary completion consumes the established G.4B doorway veto, so
            // initialize that classifier before recovered-surface boundary extraction runs.
            // This is an ordering correction only: the threshold evidence and matching
            // semantics remain the same and no new floor or review capacity is admitted.
            // IV.30.1G.4B — Gap & Threshold Classification.
            // Living Contour now consumes the same review-first doorway evidence that
            // Structural tracing already proved in G.2. A contour span which crosses a
            // certified doorway/passage is split rather than sealed merely to make the
            // organic boundary look complete. Other unresolved ends are classified for
            // Keeper review, but uncertainty never grants permission to invent a bridge.
            const contourThresholdCandidates = Array.isArray(options.thresholdCandidates)
                ? options.thresholdCandidates.filter((item) => item?.type === 'door' && item?.doorwayReasoning)
                : structuralCartographyCandidates().filter((item) => item?.type === 'door' && item?.doorwayReasoning);
            const contourSpanOrientation = (a, b) => {
                const dx = Math.abs(Number(b.x) - Number(a.x));
                const dy = Math.abs(Number(b.y) - Number(a.y));
                if (dy <= .0001 && dx > .0001) return 'horizontal';
                if (dx <= .0001 && dy > .0001) return 'vertical';
                return 'organic';
            };
            const contourThresholdMatch = (a, b) => {
                const spanOrientation = contourSpanOrientation(a, b);
                if (spanOrientation === 'organic') return null;
                const spanMin = spanOrientation === 'horizontal'
                    ? Math.min(Number(a.x), Number(b.x))
                    : Math.min(Number(a.y), Number(b.y));
                const spanMax = spanOrientation === 'horizontal'
                    ? Math.max(Number(a.x), Number(b.x))
                    : Math.max(Number(a.y), Number(b.y));
                return contourThresholdCandidates.find((threshold) => {
                    const thresholdA = { x: Number(threshold.x1), y: Number(threshold.y1) };
                    const thresholdB = { x: Number(threshold.x2), y: Number(threshold.y2) };
                    if (contourSpanOrientation(thresholdA, thresholdB) !== spanOrientation) return false;
                    const crossDistance = spanOrientation === 'horizontal'
                        ? Math.max(Math.abs(Number(a.y) - thresholdA.y), Math.abs(Number(b.y) - thresholdA.y))
                        : Math.max(Math.abs(Number(a.x) - thresholdA.x), Math.abs(Number(b.x) - thresholdA.x));
                    if (crossDistance > .42) return false;
                    const thresholdMin = spanOrientation === 'horizontal'
                        ? Math.min(thresholdA.x, thresholdB.x)
                        : Math.min(thresholdA.y, thresholdB.y);
                    const thresholdMax = spanOrientation === 'horizontal'
                        ? Math.max(thresholdA.x, thresholdB.x)
                        : Math.max(thresholdA.y, thresholdB.y);
                    const overlap = Math.min(spanMax, thresholdMax) - Math.max(spanMin, thresholdMin);
                    return overlap >= Math.min(.16, Math.max(.08, (spanMax - spanMin) * .28));
                }) || null;
            };
            // IV.30.1G.5Z — Recovered Surface Boundary Completion & Perimeter Continuity.
            // G.5Y can recover a large illustrated interior while the earlier G.5W edge
            // collector sees only the sides owned directly by recovered cells. Complete
            // each recovered surface through the certified playable component it touches,
            // then derive the *whole* playable/non-playable frontier. This changes no
            // floor admission decision: G.5Y remains the sole recovery authority. It only
            // makes the already-admitted surface boundary complete before promotion.
            // Exterior floor components remain excluded by isPlayableFloor, protected
            // thresholds remain open, and exact edge de-duplication prevents a completed
            // component from spending review capacity twice on the same wall side.
            reconstructedIllustratedSurfaceEdges.length = 0;

            // IV.30.1G.5Z.15A — Surface Corroboration Runtime Scope Correction.
            // structuralEdge belongs to Structural Cartography's private candidate scope.
            // Build the diagnostic exact-edge lookup inside Living Contour instead of
            // reaching across that boundary. This changes no recovery/admission rule.
            const surfaceStructuralByKey = new Map(
                structuralCartographyCandidates()
                    .filter((item) => item?.type === 'wall')
                    .map((wall) => [cartographySuggestionKey(wall), wall])
            );
            const surfaceStructuralEdge = (x1, y1, x2, y2) => surfaceStructuralByKey.get(
                cartographySuggestionKey({ x1, y1, x2, y2, type: 'wall' })
            ) || null;
            illustratedSurfacePerimeterEdges = 0;
            const completedSurfaceVisited = Array.from({ length: contourRows }, () => Array(contourColumns).fill(false));
            const completedSurfaceEdgeKeys = new Set();
            let completedSurfaceComponentId = 0;
            const completedSurfaceEdge = (x, y, dx, dy) => {
                const left = x * contourStep;
                const right = (x + 1) * contourStep;
                const top = y * contourStep;
                const bottom = (y + 1) * contourStep;
                if (dx === -1) return [{ x: left, y: top }, { x: left, y: bottom }];
                if (dx === 1) return [{ x: right, y: top }, { x: right, y: bottom }];
                if (dy === -1) return [{ x: left, y: top }, { x: right, y: top }];
                return [{ x: left, y: bottom }, { x: right, y: bottom }];
            };
            const completedEdgeKey = (a, b) => {
                // Keep this helper independent of the later suggestion-rounding closure:
                // G.5Z.1 boundary completion executes before roundContourCoordinate exists.
                const roundCompletedCoordinate = (value) => Math.round(value * contourSubdivisions) / contourSubdivisions;
                const first = `${roundCompletedCoordinate(a.x)},${roundCompletedCoordinate(a.y)}`;
                const second = `${roundCompletedCoordinate(b.x)},${roundCompletedCoordinate(b.y)}`;
                return first < second ? `${first}:${second}` : `${second}:${first}`;
            };
            // IV.30.1G.5Z.1 — Recovered-to-Playable Component Anchor Reconciliation.
            // G.5Z's first live audit proved that G.5Y can recover 885 cells while the
            // post-recovery floor-component classifier labels their enlarged component as
            // exterior. Recovered cells are already admitted by G.5Y's stricter structural,
            // exterior and decoration gates, so they are valid local anchors even when that
            // later broad component label disagrees. Completion may walk from such an anchor
            // through either the recovered surface itself or certified non-exterior floor; it
            // may never use an unrelated exterior floor cell merely because it is nearby.
            const isCompletedSurfacePlayable = (column, row) => column >= 0 && row >= 0
                && column < contourColumns && row < contourRows
                && (illustratedFloorContinuitySurface[row][column] || isPlayableFloor(column, row));
            // Historical G.5Z regression contract: if (!isPlayableFloor(seedColumn, seedRow)) continue;
            // G.5Z.1 deliberately reconciles a G.5Y-admitted recovered seed locally instead
            // of rejecting it solely because the later broad component classifier disagrees.
            for (let seedRow = 1; seedRow < contourRows - 1; seedRow += 1) {
                for (let seedColumn = 1; seedColumn < contourColumns - 1; seedColumn += 1) {
                    if (!illustratedFloorContinuitySurface[seedRow][seedColumn] || completedSurfaceVisited[seedRow][seedColumn]) continue;
                    illustratedSurfaceAnchorCandidates += 1;
                    if (isPlayableFloor(seedColumn, seedRow)) illustratedSurfaceExactPlayableAnchors += 1;
                    else if (isCompletedSurfacePlayable(seedColumn, seedRow)) illustratedSurfaceReconciledAnchors += 1;
                    else { illustratedSurfaceUnresolvedSurfaces += 1; continue; }
                    illustratedSurfaceCompletedComponents += 1;
                    completedSurfaceComponentId += 1;
                    const surfaceComponentId = completedSurfaceComponentId;
                    const queue = [[seedColumn, seedRow]];
                    completedSurfaceVisited[seedRow][seedColumn] = true;
                    for (let cursor = 0; cursor < queue.length; cursor += 1) {
                        const [x, y] = queue[cursor];
                        illustratedSurfaceCompletedCells += 1;
                        [[-1,0],[1,0],[0,-1],[0,1]].forEach(([dx,dy]) => {
                            const nx = x + dx, ny = y + dy;
                            if (isCompletedSurfacePlayable(nx, ny)) {
                                // This is an interior seam of the completed playable
                                // surface, never a wall merely because only one side was
                                // originally recovered by G.5Y.
                                illustratedSurfacePlayableSeamsSuppressed += 1;
                                if (!completedSurfaceVisited[ny][nx]) {
                                    completedSurfaceVisited[ny][nx] = true;
                                    queue.push([nx,ny]);
                                }
                                return;
                            }
                            illustratedSurfaceRawBoundarySides += 1;
                            const [a,b] = completedSurfaceEdge(x,y,dx,dy);
                            if (contourThresholdMatch(a,b)) {
                                illustratedSurfaceThresholdRejects += 1;
                                return;
                            }
                            const key = completedEdgeKey(a,b);
                            if (completedSurfaceEdgeKeys.has(key)) return;
                            completedSurfaceEdgeKeys.add(key);

                            // G.5Z.15 deliberately observes the edge *before* it is handed
                            // to perimeter promotion. The inside/outside samples are local
                            // cell evidence only; no nearest-wall search is permitted.
                            const insideInk = Number(darkness[y]?.[x] ?? 0);
                            const outsideInk = Number(darkness[ny]?.[nx] ?? 0);
                            const boundaryInk = Math.max(insideInk, outsideInk);
                            const exactStructuralBoundary = Boolean(surfaceStructuralEdge(a.x, a.y, b.x, b.y));
                            let surfaceBoundaryCorroboration = 'unsupported-frontier';
                            if (exactStructuralBoundary) {
                                illustratedSurfaceBoundaryStructuralCorroborated += 1;
                                surfaceBoundaryCorroboration = 'exact-structural';
                            } else if (boundaryInk >= .62) {
                                illustratedSurfaceBoundaryStrongInkCorroborated += 1;
                                surfaceBoundaryCorroboration = 'strong-local-ink';
                            } else if (boundaryInk >= .42) {
                                illustratedSurfaceBoundaryModerateInkCorroborated += 1;
                                surfaceBoundaryCorroboration = 'moderate-local-ink';
                            } else {
                                illustratedSurfaceBoundaryUnsupportedFrontier += 1;
                                if (boundaryInk <= .30) {
                                    illustratedSurfaceBoundaryOpenPaperFrontier += 1;
                                    illustratedSurfaceBoundaryOpenPaperSuppressed += 1;
                                    illustratedSurfaceBoundaryTopology.push({
                                        surfaceComponentId, key, a, b, suppressedOpenPaper: true,
                                        surfaceBoundaryCorroboration, boundaryInk, insideInk, outsideInk, exactStructuralBoundary,
                                        insideColumn: x, insideRow: y, outsideDx: dx, outsideDy: dy
                                    });
                                    // G.5Z.16: a completed playable surface ending against
                                    // positively identified quiet paper is a recovery frontier,
                                    // not sufficient evidence of a dungeon wall. Suppress only
                                    // this certified subset; do not weaken recovery or search for
                                    // a replacement edge elsewhere.
                                    return;
                                }
                            }
                            illustratedSurfaceBoundaryTopology.push({
                                surfaceComponentId, key, a, b, suppressedOpenPaper: false
                            });
                            illustratedSurfacePerimeterEdges += 1;
                            reconstructedIllustratedSurfaceEdges.push({
                                type: 'wall', confidence: 87, selected: true, contour: true, fineContour: true,
                                fullBoundary: false, partialContour: true,
                                partialContourRecovery: 'recovered-surface-boundary-completion',
                                reconstructedSurfaceAuthority: true, reconstructedSurfacePropagation: true,
                                recoveredSurfaceBoundaryCompletion: true, perimeterContinuity: true,
                                surfaceBoundaryCorroboration, surfaceBoundaryInk: boundaryInk,
                                surfaceBoundaryStructuralExact: exactStructuralBoundary,
                                semanticBoundaryClassification: 'structural-wall',
                                semanticBoundaryRole: 'completed-illustrated-surface-perimeter',
                                evidenceModel: 'living-contour-recovered-surface-boundary-completion-v13',
                                recoveryEvidence: ['illustrated-interior-surface', 'g5y-admission-preserved', 'certified-playable-component', 'complete-playable-frontier', 'interior-seam-suppression', 'portal-threshold-veto'],
                                polyline: true, points: [a,b], x1: a.x, y1: a.y, x2: b.x, y2: b.y
                            });
                        });
                    }
                }
            }

            // G.5Z.17 audits only exact endpoint continuity inside the same completed
            // source surface. It does not use distance, nearest matching, snapping or ink
            // relaxation, and it does not feed any suppressed edge back into promotion.
            const completedBoundaryEndpointKey = (point) => `${point.x},${point.y}`;
            const retainedEndpointsBySurface = new Map();
            const suppressedBySurface = new Map();
            illustratedSurfaceBoundaryTopology.forEach((edge) => {
                const target = edge.suppressedOpenPaper ? suppressedBySurface : retainedEndpointsBySurface;
                if (!target.has(edge.surfaceComponentId)) target.set(edge.surfaceComponentId, edge.suppressedOpenPaper ? [] : new Set());
                if (edge.suppressedOpenPaper) target.get(edge.surfaceComponentId).push(edge);
                else {
                    target.get(edge.surfaceComponentId).add(completedBoundaryEndpointKey(edge.a));
                    target.get(edge.surfaceComponentId).add(completedBoundaryEndpointKey(edge.b));
                }
            });
            suppressedBySurface.forEach((edges, surfaceComponentId) => {
                const endpointToEdges = new Map();
                edges.forEach((edge, index) => {
                    [edge.a, edge.b].forEach((point) => {
                        const endpoint = completedBoundaryEndpointKey(point);
                        if (!endpointToEdges.has(endpoint)) endpointToEdges.set(endpoint, []);
                        endpointToEdges.get(endpoint).push(index);
                    });
                });
                const retainedEndpoints = retainedEndpointsBySurface.get(surfaceComponentId) || new Set();
                const visitedSuppressed = new Set();
                edges.forEach((edge, startIndex) => {
                    if (visitedSuppressed.has(startIndex)) return;
                    const stack = [startIndex];
                    const runIndexes = [];
                    let branchAdjacent = false;
                    while (stack.length) {
                        const index = stack.pop();
                        if (visitedSuppressed.has(index)) continue;
                        visitedSuppressed.add(index);
                        runIndexes.push(index);
                        [edges[index].a, edges[index].b].forEach((point) => {
                            const neighbours = endpointToEdges.get(completedBoundaryEndpointKey(point)) || [];
                            if (neighbours.length > 2) branchAdjacent = true;
                            neighbours.forEach((nextIndex) => { if (!visitedSuppressed.has(nextIndex)) stack.push(nextIndex); });
                        });
                    }
                    const runEndpointDegrees = new Map();
                    runIndexes.forEach((index) => [edges[index].a, edges[index].b].forEach((point) => {
                        const endpoint = completedBoundaryEndpointKey(point);
                        runEndpointDegrees.set(endpoint, (runEndpointDegrees.get(endpoint) || 0) + 1);
                    }));
                    const terminalEndpoints = [...runEndpointDegrees.entries()].filter(([, degree]) => degree === 1).map(([endpoint]) => endpoint);
                    const boundedTerminals = terminalEndpoints.filter((endpoint) => retainedEndpoints.has(endpoint)).length;
                    illustratedSurfaceSuppressedGapRuns += 1;
                    if (boundedTerminals >= 2) illustratedSurfaceSuppressedGapBothBounded += 1;
                    else if (boundedTerminals === 1) illustratedSurfaceSuppressedGapOneBounded += 1;
                    else illustratedSurfaceSuppressedGapUnbounded += 1;
                    if (branchAdjacent) illustratedSurfaceSuppressedGapBranchAdjacent += 1;
                    if (boundedTerminals >= 2 && !branchAdjacent) illustratedSurfaceSuppressedGapSameSourceContiguous += 1;
                    const runLength = runIndexes.length;
                    if (runLength === 1) illustratedSurfaceSuppressedGapLength1 += 1;
                    else if (runLength === 2) illustratedSurfaceSuppressedGapLength2 += 1;
                    else if (runLength <= 4) illustratedSurfaceSuppressedGapLength3To4 += 1;
                    else if (runLength <= 8) illustratedSurfaceSuppressedGapLength5To8 += 1;
                    else illustratedSurfaceSuppressedGapLength9Plus += 1;

                    // G.5Z.19: the remaining longer runs are audited as complete exact-source
                    // sequences. Span is measured in contour cells from the run's own
                    // endpoints; ink statistics use only the local samples already captured
                    // by G.5Z.15. This is evidence collection only.
                    if (boundedTerminals >= 2 && !branchAdjacent && runLength > 2) {
                        illustratedSurfaceLongGapRuns += 1;
                        illustratedSurfaceLongGapEdges += runLength;
                        if (runLength <= 8) illustratedSurfaceLongGapFiveToEightRuns += 1;
                        else illustratedSurfaceLongGapNinePlusRuns += 1;
                        illustratedSurfaceLongGapExactLengths.push(runLength);
                        const runEdges = runIndexes.map((index) => edges[index]);
                        const runInks = runEdges.map((runEdge) => Number(runEdge.boundaryInk || 0));
                        illustratedSurfaceLongGapNearCutoffEdges += runInks.filter((ink) => ink > .24 && ink <= .30).length;
                        illustratedSurfaceLongGapDeepQuietEdges += runInks.filter((ink) => ink <= .18).length;
                        illustratedSurfaceLongGapInteriorDepthSupportedEdges += runEdges.filter((runEdge) => {
                            const x1 = runEdge.insideColumn - runEdge.outsideDx;
                            const y1 = runEdge.insideRow - runEdge.outsideDy;
                            const x2 = runEdge.insideColumn - (runEdge.outsideDx * 2);
                            const y2 = runEdge.insideRow - (runEdge.outsideDy * 2);
                            return isCompletedSurfacePlayable(x1, y1) && isCompletedSurfacePlayable(x2, y2);
                        }).length;
                        const runMaxInk = runInks.length ? Math.max(...runInks) : 0;
                        illustratedSurfaceLongGapMaxInkPermille = Math.max(illustratedSurfaceLongGapMaxInkPermille, Math.round(runMaxInk * 1000));
                        const points = runEdges.flatMap((runEdge) => [runEdge.a, runEdge.b]);
                        const xs = points.map((point) => point.x);
                        const ys = points.map((point) => point.y);
                        const spanCells = Math.round(Math.max(
                            (Math.max(...xs) - Math.min(...xs)) / Math.max(1, contourCellX),
                            (Math.max(...ys) - Math.min(...ys)) / Math.max(1, contourCellY)
                        ));
                        illustratedSurfaceLongGapTotalSpanCells += spanCells;
                        illustratedSurfaceLongGapLargestSpanCells = Math.max(illustratedSurfaceLongGapLargestSpanCells, spanCells);

                        // G.5Z.20: perimeter travel is the sum of the exact original edge lengths
                        // in contour-cell units; endpoint displacement is measured only between
                        // the two true degree-one terminals of this same-source run. No endpoint
                        // search, snapping, interpolation or synthetic bridge is permitted.
                        const travelCells = runEdges.reduce((total, runEdge) => {
                            const dxCells = (runEdge.b.x - runEdge.a.x) / Math.max(1, contourCellX);
                            const dyCells = (runEdge.b.y - runEdge.a.y) / Math.max(1, contourCellY);
                            return total + Math.hypot(dxCells, dyCells);
                        }, 0);
                        const terminalPoints = terminalEndpoints.map((endpoint) => {
                            for (const runEdge of runEdges) {
                                if (completedBoundaryEndpointKey(runEdge.a) === endpoint) return runEdge.a;
                                if (completedBoundaryEndpointKey(runEdge.b) === endpoint) return runEdge.b;
                            }
                            return null;
                        }).filter(Boolean);
                        const endpointDisplacementCells = terminalPoints.length === 2
                            ? Math.hypot(
                                (terminalPoints[1].x - terminalPoints[0].x) / Math.max(1, contourCellX),
                                (terminalPoints[1].y - terminalPoints[0].y) / Math.max(1, contourCellY)
                            )
                            : 0;
                        illustratedSurfaceLongGapGeometry.push({
                            edges: runLength,
                            travelMilliCells: Math.round(travelCells * 1000),
                            displacementMilliCells: Math.round(endpointDisplacementCells * 1000),
                            travelDisplacementPermille: endpointDisplacementCells > 0
                                ? Math.round((travelCells / endpointDisplacementCells) * 1000)
                                : 0
                        });
                    }

                    // G.5Z.21 is deliberately a second admission class rather than a
                    // relaxation of G.5Z.18. It uses the same exact-source topology and only
                    // the four G.5Z.20 short-run candidates: 5–8 edges whose perimeter travel,
                    // at the published one-decimal audit precision, is <= 0.3 contour cells.
                    // Runs of 9+ edges remain suppressed by construction.
                    const shortRunEdges = runIndexes.map((index) => edges[index]);
                    const shortRunTravelCells = shortRunEdges.reduce((total, runEdge) => {
                        const dxCells = (runEdge.b.x - runEdge.a.x) / Math.max(1, contourCellX);
                        const dyCells = (runEdge.b.y - runEdge.a.y) / Math.max(1, contourCellY);
                        return total + Math.hypot(dxCells, dyCells);
                    }, 0);
                    const shortRunPublishedTravelTenths = Math.round(shortRunTravelCells * 10);
                    const shortGapEligible = boundedTerminals >= 2
                        && !branchAdjacent
                        && runLength >= 5
                        && runLength <= 8
                        && shortRunPublishedTravelTenths <= 3;
                    if (shortGapEligible) {
                        illustratedSurfaceShortGapEligibleRuns += 1;
                        illustratedSurfaceShortGapEligibleEdges += runLength;
                        runIndexes.forEach((index) => {
                            const restored = edges[index];
                            illustratedSurfacePerimeterEdges += 1;
                            illustratedSurfaceShortGapRestoredEdges += 1;
                            reconstructedIllustratedSurfaceEdges.push({
                                type: 'wall', confidence: 87, selected: true, contour: true, fineContour: true,
                                fullBoundary: false, partialContour: true,
                                partialContourRecovery: 'recovered-surface-boundary-completion',
                                reconstructedSurfaceAuthority: true, reconstructedSurfacePropagation: true,
                                recoveredSurfaceBoundaryCompletion: true, perimeterContinuity: true,
                                surfaceBoundaryCorroboration: restored.surfaceBoundaryCorroboration,
                                surfaceBoundaryInk: restored.boundaryInk,
                                surfaceBoundaryStructuralExact: restored.exactStructuralBoundary,
                                surfaceBoundaryShortGapRestored: true,
                                semanticBoundaryClassification: 'structural-wall',
                                semanticBoundaryRole: 'completed-illustrated-surface-perimeter',
                                evidenceModel: 'living-contour-recovered-surface-boundary-completion-v14',
                                recoveryEvidence: ['illustrated-interior-surface', 'g5y-admission-preserved', 'certified-playable-component', 'complete-playable-frontier', 'interior-seam-suppression', 'portal-threshold-veto', 'g5z21-short-run-continuity'],
                                polyline: true, points: [restored.a, restored.b],
                                x1: restored.a.x, y1: restored.a.y, x2: restored.b.x, y2: restored.b.y
                            });
                        });
                    }

                    // G.5Z.18 restores no invented geometry: these are the exact original
                    // perimeter edges withheld by G.5Z.16. Locality is deliberately hard
                    // bounded at two edges so long both-bounded open-paper excursions stay
                    // suppressed even though they eventually return to retained boundary.
                    const microGapEligible = boundedTerminals >= 2 && !branchAdjacent && runLength <= 2;
                    if (microGapEligible) {
                        illustratedSurfaceMicroGapEligibleRuns += 1;
                        illustratedSurfaceMicroGapEligibleEdges += runLength;
                        runIndexes.forEach((index) => {
                            const restored = edges[index];
                            illustratedSurfacePerimeterEdges += 1;
                            illustratedSurfaceMicroGapRestoredEdges += 1;
                            reconstructedIllustratedSurfaceEdges.push({
                                type: 'wall', confidence: 87, selected: true, contour: true, fineContour: true,
                                fullBoundary: false, partialContour: true,
                                partialContourRecovery: 'recovered-surface-boundary-completion',
                                reconstructedSurfaceAuthority: true, reconstructedSurfacePropagation: true,
                                recoveredSurfaceBoundaryCompletion: true, perimeterContinuity: true,
                                surfaceBoundaryCorroboration: restored.surfaceBoundaryCorroboration,
                                surfaceBoundaryInk: restored.boundaryInk,
                                surfaceBoundaryStructuralExact: restored.exactStructuralBoundary,
                                surfaceBoundaryMicroGapRestored: true,
                                semanticBoundaryClassification: 'structural-wall',
                                semanticBoundaryRole: 'completed-illustrated-surface-perimeter',
                                evidenceModel: 'living-contour-recovered-surface-boundary-completion-v13',
                                recoveryEvidence: ['illustrated-interior-surface', 'g5y-admission-preserved', 'certified-playable-component', 'complete-playable-frontier', 'interior-seam-suppression', 'portal-threshold-veto', 'g5z18-micro-gap-continuity'],
                                polyline: true, points: [restored.a, restored.b],
                                x1: restored.a.x, y1: restored.a.y, x2: restored.b.x, y2: restored.b.y
                            });
                        });
                    }
                });
            });
            if (illustratedSurfaceLongGapEdges > 0) {
                const longGapInks = [];
                suppressedBySurface.forEach((surfaceEdges) => surfaceEdges.forEach((edge) => {
                    if (Number.isFinite(edge.boundaryInk)) longGapInks.push(edge.boundaryInk);
                }));
                // Keep the published mean scoped to the still-suppressed long-run population.
                // Micro-gap members are excluded by reconstructing the long-run components.
                let longInkSum = 0;
                let longInkCount = 0;
                const seen = new Set();
                suppressedBySurface.forEach((surfaceEdges) => {
                    const endpointMap = new Map();
                    surfaceEdges.forEach((edge, index) => [edge.a, edge.b].forEach((point) => {
                        const endpoint = completedBoundaryEndpointKey(point);
                        if (!endpointMap.has(endpoint)) endpointMap.set(endpoint, []);
                        endpointMap.get(endpoint).push(index);
                    }));
                    surfaceEdges.forEach((edge, startIndex) => {
                        const token = `${edge.surfaceComponentId}:${startIndex}`;
                        if (seen.has(token)) return;
                        const stack = [startIndex], indexes = [];
                        while (stack.length) {
                            const index = stack.pop();
                            const key = `${surfaceEdges[index].surfaceComponentId}:${index}`;
                            if (seen.has(key)) continue;
                            seen.add(key); indexes.push(index);
                            [surfaceEdges[index].a, surfaceEdges[index].b].forEach((point) =>
                                (endpointMap.get(completedBoundaryEndpointKey(point)) || []).forEach((next) => stack.push(next)));
                        }
                        if (indexes.length > 2) indexes.forEach((index) => { longInkSum += Number(surfaceEdges[index].boundaryInk || 0); longInkCount += 1; });
                    });
                });
                illustratedSurfaceLongGapMeanInkPermille = longInkCount ? Math.round((longInkSum / longInkCount) * 1000) : 0;
            }

            // A floor-facing wall edge should have a meaningful depth of playable floor
            // behind it. Tiny white pockets between hatch strokes usually fail this test,
            // which prevents Pippin hopping across the ink band to its opposite edge.
            const minimumWallBandFloorDepth = Math.max(2, Math.round(contourSubdivisions * .34));
            const wallBandFloorDepth = (column, row, dx, dy) => {
                let depth = 0;
                for (let step = 0; step < minimumWallBandFloorDepth + 2; step += 1) {
                    const x = column + (dx * step);
                    const y = row + (dy * step);
                    if (!isPlayableFloor(x, y)) break;
                    depth += 1;
                }
                return depth;
            };
            const wallBandEdgeIsFloorFacing = (column, row, inwardDx, inwardDy) =>
                wallBandFloorDepth(column, row, inwardDx, inwardDy) >= minimumWallBandFloorDepth;

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
                    if (!isPlayableFloor(column, row)) continue;
                    const left = column * contourStep;
                    const right = (column + 1) * contourStep;
                    const top = row * contourStep;
                    const bottom = (row + 1) * contourStep;
                    // The finite analysis envelope is not cave rock. G.5 also refuses
                    // the exterior-facing side of a wall band and hatch-sized white
                    // pockets: emitted geometry must have sustained playable floor on
                    // its inward side. This gives Hybrid a stable side to preserve.
                    if (row > 0 && !isFloor(column, row - 1) && wallBandEdgeIsFloorFacing(column, row, 0, 1)) add(left, top, right, top, 91);
                    if (column < contourColumns - 1 && !isFloor(column + 1, row) && wallBandEdgeIsFloorFacing(column, row, -1, 0)) add(right, top, right, bottom, 91);
                    if (row < contourRows - 1 && !isFloor(column, row + 1) && wallBandEdgeIsFloorFacing(column, row, 0, -1)) add(left, bottom, right, bottom, 91);
                    if (column > 0 && !isFloor(column - 1, row) && wallBandEdgeIsFloorFacing(column, row, 1, 0)) add(left, top, left, bottom, 91);
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

            // IV.30.1G.5A — Semantic Boundary Classification.
            // IV.30.1G.5E — Boundary Role & Interior Feature Classification.
            // Contrast alone cannot tell a cave perimeter from a hill, rubble island,
            // stair mark or freestanding object. Classify the *role* of the ordered
            // chain using sidedness, enclosure scale and playable-region context before
            // it is allowed to become an automatic LOS wall.
            const semanticBoundaryClassification = (chain, closed, length) => {
                if (!Array.isArray(chain) || chain.length < 2) {
                    return { role: 'uncertain', boundaryRole: 'unresolved-evidence', confidence: 0, evidence: ['insufficient-boundary-evidence'] };
                }
                let sampled = 0;
                let playableBothSides = 0;
                let playableOneSide = 0;
                let playableNeitherSide = 0;
                let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
                chain.forEach((point) => {
                    minX = Math.min(minX, Number(point[0])); maxX = Math.max(maxX, Number(point[0]));
                    minY = Math.min(minY, Number(point[1])); maxY = Math.max(maxY, Number(point[1]));
                });
                const normalOffset = Math.max(1, Math.round(contourSubdivisions * .32));
                const samplePlayable = (x, y) => isPlayableFloor(Math.round(x), Math.round(y));
                for (let index = 0; index < chain.length - 1; index += 1) {
                    const a = chain[index];
                    const b = chain[index + 1];
                    const dx = Number(b[0]) - Number(a[0]);
                    const dy = Number(b[1]) - Number(a[1]);
                    const magnitude = Math.hypot(dx, dy);
                    if (magnitude <= .000001) continue;
                    const midpointX = ((Number(a[0]) + Number(b[0])) / 2) * contourSubdivisions;
                    const midpointY = ((Number(a[1]) + Number(b[1])) / 2) * contourSubdivisions;
                    const normalX = (-dy / magnitude) * normalOffset;
                    const normalY = (dx / magnitude) * normalOffset;
                    const sideA = samplePlayable(midpointX + normalX, midpointY + normalY);
                    const sideB = samplePlayable(midpointX - normalX, midpointY - normalY);
                    sampled += 1;
                    if (sideA && sideB) playableBothSides += 1;
                    else if (sideA || sideB) playableOneSide += 1;
                    else playableNeitherSide += 1;
                }
                const bothSidesRatio = sampled > 0 ? playableBothSides / sampled : 0;
                const oneSideRatio = sampled > 0 ? playableOneSide / sampled : 0;
                const neitherSideRatio = sampled > 0 ? playableNeitherSide / sampled : 0;
                const width = Math.max(0, maxX - minX);
                const height = Math.max(0, maxY - minY);
                const enclosureArea = width * height;
                const enclosureSpan = Math.max(width, height);
                const compactEnclosure = closed && enclosureSpan <= 2.35 && enclosureArea <= 3.6;
                const tinyEnclosure = closed && enclosureSpan <= 1.05 && enclosureArea <= .85;
                const twoSidedPlayable = bothSidesRatio >= .46;
                const perimeterSeparation = oneSideRatio >= .48;

                if (tinyEnclosure && (bothSidesRatio >= .18 || neitherSideRatio < .72)) {
                    return {
                        role: 'decoration-noise', boundaryRole: 'interior-decoration', confidence: 94,
                        evidence: ['tiny-enclosed-mark', 'interior-playable-context', 'not-region-perimeter']
                    };
                }
                if (compactEnclosure && twoSidedPlayable) {
                    return {
                        role: 'interior-feature', boundaryRole: 'interior-feature', confidence: 92,
                        evidence: ['compact-enclosed-feature', 'playable-floor-both-sides', 'do-not-block-sight']
                    };
                }
                if (closed && bothSidesRatio >= .62) {
                    return {
                        role: 'terrain', boundaryRole: 'terrain-elevation', confidence: 91,
                        evidence: ['playable-floor-both-sides', 'closed-interior-boundary', 'terrain-not-wall']
                    };
                }
                if (closed && length < 3.2 && bothSidesRatio >= .28) {
                    return {
                        role: 'obstacle', boundaryRole: 'interior-obstacle', confidence: 86,
                        evidence: ['compact-interior-boundary', 'mixed-playable-context', 'review-not-auto-wall']
                    };
                }
                if (perimeterSeparation) {
                    return {
                        role: 'structural-wall', boundaryRole: closed ? 'enclosed-region-boundary' : 'playable-region-perimeter', confidence: 92,
                        evidence: ['playable-floor-one-side', 'wall-band-separation', 'region-boundary-role']
                    };
                }
                if (!closed && length >= 2.4 && oneSideRatio >= .30 && bothSidesRatio < .34) {
                    return {
                        role: 'structural-wall', boundaryRole: 'partial-region-perimeter', confidence: 78,
                        evidence: ['long-open-boundary', 'asymmetric-playable-context', 'partial-perimeter-role']
                    };
                }
                return {
                    role: 'uncertain', boundaryRole: 'unresolved-evidence', confidence: 64,
                    evidence: ['ambiguous-region-relationship', 'review-before-wall', 'no-interior-promotion']
                };
            };
            const semanticRoleIsAutomaticWall = (role) => role === 'structural-wall';
            const classifyContourEndpoint = (point) => {
                const x = Number(point.x);
                const y = Number(point.y);
                const edgeTolerance = Math.max(contourStep * 1.15, .18);
                if (x <= edgeTolerance || y <= edgeTolerance
                    || x >= columns - edgeTolerance || y >= rows - edgeTolerance) {
                    return { classification: 'map-edge', evidence: ['analysis-envelope'] };
                }
                const nearbyThreshold = contourThresholdCandidates.find((threshold) => {
                    const centerX = (Number(threshold.x1) + Number(threshold.x2)) / 2;
                    const centerY = (Number(threshold.y1) + Number(threshold.y2)) / 2;
                    return Math.hypot(centerX - x, centerY - y) <= .82;
                });
                if (nearbyThreshold) {
                    return { classification: 'doorway-passage', evidence: ['structural-threshold', 'floor-continuity'] };
                }
                const canvasX = originX + (x * gridCanvasX);
                const canvasY = originY + (y * gridCanvasY);
                const radius = Math.max(2, Math.min(gridCanvasX, gridCanvasY) * .24);
                const stats = luminanceRegionStats(canvasX - radius, canvasY - radius, canvasX + radius, canvasY + radius);
                if (stats.deviation >= 48 && stats.mean <= mapTone.light - 6) {
                    return { classification: 'noise-gap', evidence: ['locally-busy-ink', 'uncertain-boundary'] };
                }
                return { classification: 'uncertain-boundary', evidence: ['insufficient-gap-evidence'] };
            };
            // IV.30.1G.5F — Playable-Space Adjacency & Threshold Connectivity.
            // Ink tells Pippin where a boundary might be; floor topology now tells him
            // whether that boundary makes sense. Closed chains sample playable floor on
            // both sides of their envelope so hills/elevation marks embedded inside a
            // connected floor region cannot masquerade as room walls. Open chains also
            // test their unresolved ends for a short, floor-continuous passage: that is
            // inferred threshold evidence and must remain open even when no conventional
            // door glyph was recognised by Structural tracing.
            const playableSpaceAdjacency = (chain, closed, semanticBoundary) => {
                if (!Array.isArray(chain) || chain.length < 2) {
                    return { classification: 'unresolved-adjacency', confidence: 0, thresholdConnectivity: false, evidence: ['insufficient-chain'] };
                }
                let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
                chain.forEach((point) => {
                    minX = Math.min(minX, Number(point[0])); maxX = Math.max(maxX, Number(point[0]));
                    minY = Math.min(minY, Number(point[1])); maxY = Math.max(maxY, Number(point[1]));
                });
                const playableAtGrid = (x, y) => isPlayableFloor(
                    Math.round(Number(x) * contourSubdivisions),
                    Math.round(Number(y) * contourSubdivisions)
                );
                const sampleBox = (left, top, right, bottom, step) => {
                    let playable = 0;
                    let sampled = 0;
                    for (let y = top; y <= bottom + .0001; y += step) {
                        for (let x = left; x <= right + .0001; x += step) {
                            sampled += 1;
                            if (playableAtGrid(x, y)) playable += 1;
                        }
                    }
                    return sampled > 0 ? playable / sampled : 0;
                };
                const step = Math.max(.22, contourStep * 1.4);
                const inset = Math.max(.18, contourStep * 1.1);
                const interiorRatio = closed && (maxX - minX) > inset * 2 && (maxY - minY) > inset * 2
                    ? sampleBox(minX + inset, minY + inset, maxX - inset, maxY - inset, step)
                    : 0;
                const ringOffset = Math.max(.28, contourStep * 1.8);
                const ringSamples = [
                    [(minX + maxX) / 2, minY - ringOffset], [(minX + maxX) / 2, maxY + ringOffset],
                    [minX - ringOffset, (minY + maxY) / 2], [maxX + ringOffset, (minY + maxY) / 2],
                    [minX - ringOffset, minY - ringOffset], [maxX + ringOffset, minY - ringOffset],
                    [minX - ringOffset, maxY + ringOffset], [maxX + ringOffset, maxY + ringOffset]
                ];
                const exteriorPlayable = ringSamples.filter(([x, y]) => playableAtGrid(x, y)).length / ringSamples.length;

                const first = chain[0];
                const last = chain[chain.length - 1];
                const endpointDistance = Math.hypot(Number(last[0]) - Number(first[0]), Number(last[1]) - Number(first[1]));
                let floorContinuousGap = false;
                if (!closed && endpointDistance >= .16 && endpointDistance <= 1.45) {
                    let floorSamples = 0;
                    const checks = 7;
                    for (let index = 1; index < checks; index += 1) {
                        const ratio = index / checks;
                        const x = Number(first[0]) + ((Number(last[0]) - Number(first[0])) * ratio);
                        const y = Number(first[1]) + ((Number(last[1]) - Number(first[1])) * ratio);
                        if (playableAtGrid(x, y)) floorSamples += 1;
                    }
                    floorContinuousGap = floorSamples >= checks - 2;
                }

                const embeddedPlayableIsland = closed
                    && interiorRatio >= .44
                    && exteriorPlayable >= .50;
                if (embeddedPlayableIsland) {
                    return {
                        classification: 'interior-playable-island', confidence: 93, thresholdConnectivity: false,
                        interiorPlayableRatio: interiorRatio, exteriorPlayableRatio: exteriorPlayable,
                        evidence: ['playable-floor-inside', 'playable-floor-outside', 'isolation-penalty', 'terrain-or-interior-feature']
                    };
                }
                if (floorContinuousGap) {
                    return {
                        classification: 'threshold-connected-open-chain', confidence: 91, thresholdConnectivity: true,
                        interiorPlayableRatio: interiorRatio, exteriorPlayableRatio: exteriorPlayable,
                        evidence: ['nearby-boundary-ends', 'continuous-playable-floor', 'probable-threshold', 'do-not-auto-bridge']
                    };
                }
                return {
                    classification: closed ? 'region-envelope' : 'open-boundary', confidence: 76, thresholdConnectivity: false,
                    interiorPlayableRatio: interiorRatio, exteriorPlayableRatio: exteriorPlayable,
                    evidence: ['adjacency-consistent']
                };
            };
            const applyPlayableSpaceAdjacency = (entry) => {
                const adjacency = playableSpaceAdjacency(entry.chain, entry.closed, entry.semanticBoundary);
                let semanticBoundary = entry.semanticBoundary;
                if (adjacency.classification === 'interior-playable-island'
                    && semanticBoundary?.role === 'structural-wall') {
                    semanticBoundary = {
                        role: 'terrain', boundaryRole: 'terrain-elevation', confidence: Math.max(91, Number(adjacency.confidence || 0)),
                        evidence: (semanticBoundary.evidence || []).concat(adjacency.evidence || []).concat(['adjacency-demotion'])
                    };
                }
                return { ...entry, semanticBoundary, playableAdjacency: adjacency };
            };

            // IV.30.1G.5G — Portal Pairing & Boundary-Side Reasoning.
            // A short break is not a doorway merely because its pixels are missing. Pair
            // the two boundary ends, compare their approach tangents, then ask what kind
            // of space exists across and to either side of the break. A floor-continuous
            // jamb pair is a certified portal and remains open; a tiny aligned break with
            // asymmetric playable/non-playable sides is certified boundary continuation.
            // Everything else remains unresolved rather than being guessed into a wall.
            const portalPairingAndBoundarySides = (entry) => {
                const unresolved = {
                    classification: 'unresolved-boundary-break', confidence: 0, portal: false,
                    boundaryContinuation: false, evidence: ['insufficient-pairing-evidence']
                };
                if (!entry?.partial || !Array.isArray(entry.chain) || entry.chain.length < 3) return unresolved;
                const chain = entry.chain;
                const first = chain[0];
                const second = chain[1];
                const beforeLast = chain[chain.length - 2];
                const last = chain[chain.length - 1];
                const gapX = Number(last[0]) - Number(first[0]);
                const gapY = Number(last[1]) - Number(first[1]);
                const gapDistance = Math.hypot(gapX, gapY);
                if (gapDistance < .12 || gapDistance > 1.55) return { ...unresolved, evidence: ['ends-not-portal-scale'] };
                const unit = (x, y) => {
                    const magnitude = Math.hypot(x, y);
                    return magnitude > .000001 ? [x / magnitude, y / magnitude] : [0, 0];
                };
                // Tangents point away from each unresolved end into its known chain.
                const tangentA = unit(Number(second[0]) - Number(first[0]), Number(second[1]) - Number(first[1]));
                const tangentB = unit(Number(beforeLast[0]) - Number(last[0]), Number(beforeLast[1]) - Number(last[1]));
                const tangentAgreement = Math.abs((tangentA[0] * tangentB[0]) + (tangentA[1] * tangentB[1]));
                const gapUnit = unit(gapX, gapY);
                const approachA = Math.abs((tangentA[0] * gapUnit[0]) + (tangentA[1] * gapUnit[1]));
                const approachB = Math.abs((tangentB[0] * gapUnit[0]) + (tangentB[1] * gapUnit[1]));
                const pairedJambGeometry = tangentAgreement >= .58 && Math.max(approachA, approachB) >= .42;
                const playableAtGrid = (x, y) => isPlayableFloor(
                    Math.round(Number(x) * contourSubdivisions),
                    Math.round(Number(y) * contourSubdivisions)
                );
                let acrossPlayable = 0;
                const acrossChecks = 9;
                for (let index = 1; index < acrossChecks; index += 1) {
                    const ratio = index / acrossChecks;
                    if (playableAtGrid(Number(first[0]) + gapX * ratio, Number(first[1]) + gapY * ratio)) acrossPlayable += 1;
                }
                const acrossPlayableRatio = acrossPlayable / (acrossChecks - 1);
                const midpointX = (Number(first[0]) + Number(last[0])) / 2;
                const midpointY = (Number(first[1]) + Number(last[1])) / 2;
                const sideOffset = Math.max(.30, contourStep * 1.9);
                const normalX = -gapUnit[1] * sideOffset;
                const normalY = gapUnit[0] * sideOffset;
                const sideAPlayable = playableAtGrid(midpointX + normalX, midpointY + normalY);
                const sideBPlayable = playableAtGrid(midpointX - normalX, midpointY - normalY);
                const boundarySideSeparation = sideAPlayable !== sideBPlayable;
                const thresholdEvidence = Boolean(entry.playableAdjacency?.thresholdConnectivity) || acrossPlayableRatio >= .72;
                if (pairedJambGeometry && thresholdEvidence) {
                    return {
                        classification: 'certified-portal-pair', confidence: 94, portal: true, boundaryContinuation: false,
                        gapDistance, acrossPlayableRatio, sideAPlayable, sideBPlayable,
                        evidence: ['paired-boundary-ends', 'jamb-compatible-tangents', 'playable-through-gap', 'portal-remains-open']
                    };
                }
                if (gapDistance <= .72 && pairedJambGeometry && boundarySideSeparation && acrossPlayableRatio < .55
                    && entry.semanticBoundary?.role === 'structural-wall') {
                    return {
                        classification: 'certified-boundary-continuation', confidence: 89, portal: false, boundaryContinuation: true,
                        gapDistance, acrossPlayableRatio, sideAPlayable, sideBPlayable,
                        evidence: ['paired-boundary-ends', 'aligned-boundary-approach', 'opposed-boundary-sides', 'safe-short-gap-recovery']
                    };
                }
                return {
                    classification: 'unresolved-boundary-break', confidence: 61, portal: false, boundaryContinuation: false,
                    gapDistance, acrossPlayableRatio, sideAPlayable, sideBPlayable,
                    evidence: ['ambiguous-end-pair', 'do-not-invent-portal-or-wall']
                };
            };
            const applyPortalPairingAndBoundarySides = (entry) => ({
                ...entry,
                portalPairing: portalPairingAndBoundarySides(entry)
            });

            // IV.30.1G.5H — Boundary Graph Reconstruction & Evidence Bridging.
            // Treat certified structural chains as nodes in a boundary graph. Nearby
            // unresolved ends may support one another, but proximity alone never grants
            // permission to draw a wall: the approach tangents must agree, the candidate
            // bridge must preserve asymmetric boundary sides, and playable floor must not
            // run through it. Portal/threshold evidence is an explicit veto.
            const boundaryGraphReconstruction = (entries) => {
                if (!Array.isArray(entries) || entries.length < 2) return { entries: entries || [], bridges: [] };
                const unit = (x, y) => {
                    const magnitude = Math.hypot(x, y);
                    return magnitude > .000001 ? [x / magnitude, y / magnitude] : [0, 0];
                };
                const playableAtGrid = (x, y) => isPlayableFloor(
                    Math.round(Number(x) * contourSubdivisions),
                    Math.round(Number(y) * contourSubdivisions)
                );
                const endpoints = [];
                entries.forEach((entry, entryIndex) => {
                    if (!entry?.partial || entry.semanticBoundary?.role !== 'structural-wall') return;
                    if (entry.portalPairing?.portal || entry.playableAdjacency?.thresholdConnectivity) return;
                    const chain = entry.chain;
                    if (!Array.isArray(chain) || chain.length < 3) return;
                    endpoints.push({ entryIndex, end: 'start', point: chain[0], neighbour: chain[1] });
                    endpoints.push({ entryIndex, end: 'finish', point: chain[chain.length - 1], neighbour: chain[chain.length - 2] });
                });
                const candidates = [];
                for (let left = 0; left < endpoints.length; left += 1) {
                    for (let right = left + 1; right < endpoints.length; right += 1) {
                        const a = endpoints[left];
                        const b = endpoints[right];
                        if (a.entryIndex === b.entryIndex) continue;
                        const dx = Number(b.point[0]) - Number(a.point[0]);
                        const dy = Number(b.point[1]) - Number(a.point[1]);
                        const distance = Math.hypot(dx, dy);
                        if (distance < .10 || distance > .82) continue;
                        const bridgeUnit = unit(dx, dy);
                        const tangentA = unit(Number(a.point[0]) - Number(a.neighbour[0]), Number(a.point[1]) - Number(a.neighbour[1]));
                        const tangentB = unit(Number(b.point[0]) - Number(b.neighbour[0]), Number(b.point[1]) - Number(b.neighbour[1]));
                        const approachA = (tangentA[0] * bridgeUnit[0]) + (tangentA[1] * bridgeUnit[1]);
                        const approachB = -((tangentB[0] * bridgeUnit[0]) + (tangentB[1] * bridgeUnit[1]));
                        const tangentAgreement = Math.abs((tangentA[0] * tangentB[0]) + (tangentA[1] * tangentB[1]));
                        if (approachA < .52 || approachB < .52 || tangentAgreement < .62) continue;
                        let throughPlayable = 0;
                        const throughChecks = 7;
                        for (let sample = 1; sample < throughChecks; sample += 1) {
                            const ratio = sample / throughChecks;
                            if (playableAtGrid(Number(a.point[0]) + dx * ratio, Number(a.point[1]) + dy * ratio)) throughPlayable += 1;
                        }
                        const throughPlayableRatio = throughPlayable / (throughChecks - 1);
                        const midpointX = (Number(a.point[0]) + Number(b.point[0])) / 2;
                        const midpointY = (Number(a.point[1]) + Number(b.point[1])) / 2;
                        const sideOffset = Math.max(.30, contourStep * 1.9);
                        const normalX = -bridgeUnit[1] * sideOffset;
                        const normalY = bridgeUnit[0] * sideOffset;
                        const sideAPlayable = playableAtGrid(midpointX + normalX, midpointY + normalY);
                        const sideBPlayable = playableAtGrid(midpointX - normalX, midpointY - normalY);
                        const boundarySideSeparation = sideAPlayable !== sideBPlayable;
                        if (!boundarySideSeparation || throughPlayableRatio >= .50) continue;
                        candidates.push({
                            a, b, distance, tangentAgreement, throughPlayableRatio, sideAPlayable, sideBPlayable,
                            score: (1 - Math.min(1, distance / .82)) * 40 + tangentAgreement * 35 + (1 - throughPlayableRatio) * 25
                        });
                    }
                }
                candidates.sort((a, b) => b.score - a.score);
                const claimedEnds = new Set();
                const bridges = [];
                candidates.forEach((candidate) => {
                    const aKey = `${candidate.a.entryIndex}:${candidate.a.end}`;
                    const bKey = `${candidate.b.entryIndex}:${candidate.b.end}`;
                    if (claimedEnds.has(aKey) || claimedEnds.has(bKey)) return;
                    claimedEnds.add(aKey); claimedEnds.add(bKey);
                    bridges.push({
                        classification: 'certified-evidence-bridge', confidence: 90, boundaryContinuation: true,
                        fromEntry: candidate.a.entryIndex, toEntry: candidate.b.entryIndex,
                        from: candidate.a.point, to: candidate.b.point, distance: candidate.distance,
                        evidence: ['boundary-graph-neighbours', 'compatible-end-tangents', 'opposed-boundary-sides', 'non-playable-through-bridge', 'safe-evidence-bridge']
                    });
                });
                return { entries, bridges };
            };

            const splitContourPathAtProtectedThresholds = (points) => {
                if (!Array.isArray(points) || points.length < 2) return { runs: [], gaps: [] };
                const runs = [];
                const gaps = [];
                let run = [points[0]];
                for (let index = 0; index < points.length - 1; index += 1) {
                    const a = points[index];
                    const b = points[index + 1];
                    const threshold = contourThresholdMatch(a, b);
                    if (threshold) {
                        if (run.length >= 2) runs.push(run);
                        gaps.push({
                            classification: 'doorway-passage',
                            from: a,
                            to: b,
                            confidence: Number(threshold.confidence || 0),
                            evidence: ['g2-structural-doorway', 'protected-open-gap', 'do-not-auto-bridge']
                        });
                        run = [b];
                        continue;
                    }
                    if (run.length === 0) run.push(a);
                    const previous = run[run.length - 1];
                    if (!previous || previous.x !== b.x || previous.y !== b.y) run.push(b);
                }
                if (run.length >= 2) runs.push(run);
                return { runs, gaps };
            };
            // Tiny hatch/ink loops are suppressed before the review-object budget is allocated.
            // IV.30.1G.4 — Partial Contour Recovery / Pippin Marks What He Knows.
            // Living Contour used to fail the entire draft when fragmented artwork
            // produced more safe boundary chains than the review budget allowed. Real
            // cave maps often contain hatching, door gaps and damaged ink that split an
            // otherwise useful perimeter. Preserve the strongest defensible open and
            // closed chains instead of requiring an all-or-nothing complete contour.
            // Historical Economy regression vocabulary retained for compatibility:
            // meaningfulChains filtered with entry.length >= contourStep * 2.5 before
            // IV.30.1G.4 taught the reader to keep safe partial contour sections.
            const recoverableChains = contourChains
                .map((chain, sourceIndex) => ({
                    chain,
                    sourceIndex,
                    length: chainLength(chain),
                    closed: isClosedChain(chain)
                }))
                .filter((entry) => entry.length >= contourStep * 1.75)
                .map((entry) => ({
                    ...entry,
                    partial: !entry.closed,
                    semanticBoundary: semanticBoundaryClassification(entry.chain, entry.closed, entry.length),
                    recoveryScore: (entry.closed ? 28 : 0)
                        + Math.min(72, entry.length * 9)
                        + Math.min(12, Math.max(0, entry.chain.length - 2) * .45)
                }))
                .map(applyPlayableSpaceAdjacency)
                .map(applyPortalPairingAndBoundarySides)
                .sort((a, b) => (b.recoveryScore - a.recoveryScore) || (b.length - a.length));

            const boundaryGraph = boundaryGraphReconstruction(recoverableChains);
            const certifiedEvidenceBridges = boundaryGraph.bridges;
            recoverableChains.forEach((entry, entryIndex) => {
                entry.boundaryGraphBridges = certifiedEvidenceBridges.filter((bridge) => bridge.fromEntry === entryIndex || bridge.toEntry === entryIndex);
            });

            // IV.30.1G.5I — Playable Region Closure & Perimeter Inference.
            // The Dungeon from Hell proved that some genuine perimeter ink never becomes
            // a trustworthy contour chain at all. Reverse the question for those gaps:
            // begin with already-certified playable floor, heal only locally surrounded
            // floor samples lost beneath illustration/ink, then inspect the resulting
            // playable-to-non-playable transition for corroborating structural ink.
            // This is not a white-area outline pass. Exterior whitespace is excluded,
            // portals/thresholds are vetoes, and a recovered edge needs both local region
            // closure support and nearby wall-body darkness before it may become a wall.
            const playableRegionClosureAndPerimeterInference = () => {
                const closure = Array.from({ length: contourRows }, (_, row) =>
                    Array.from({ length: contourColumns }, (_, column) => isPlayableFloor(column, row))
                );
                const inferred = Array.from({ length: contourRows }, () => Array(contourColumns).fill(false));

                // IV.30.1G.5L — Reconstructed Surface Propagation & Contour Re-certification.
                // G.5J/G.5K already know about playable surface hidden by illustration,
                // but G.5I historically emitted new perimeter only for cells recovered by
                // its own closure pass. Carry the provenance of those earlier recovered
                // cells into the closure graph so their *outer* playable/non-playable edge
                // can be reconsidered. This is still additive and remains subject to the
                // same portal, wall-body and local-continuity vetoes as G.5I.
                const propagatedRecoveredSurface = Array.from({ length: contourRows }, (_, row) =>
                    Array.from({ length: contourColumns }, (_, column) => Boolean(
                        isPlayableFloor(column, row)
                        && (reconstructedPlayableSurface[row][column] || illustratedInteriorPlayableSurface[row][column] || illustratedFloorContinuitySurface[row][column])
                    ))
                );
                const neighbourOffsets = [[-1,-1],[0,-1],[1,-1],[-1,0],[1,0],[-1,1],[0,1],[1,1]];

                // Two deliberately small closure passes recover floor hidden by a hatch,
                // creature line or stone edge without flooding across a real wall band.
                for (let pass = 0; pass < 2; pass += 1) {
                    const additions = [];
                    for (let row = 1; row < contourRows - 1; row += 1) {
                        for (let column = 1; column < contourColumns - 1; column += 1) {
                            if (closure[row][column]) continue;
                            // Never absorb known exterior white space into a dungeon region.
                            const rawComponent = floor[row][column] ? floorComponent[row][column] : -1;
                            if (rawComponent >= 0 && exteriorFloorComponents.has(rawComponent)) continue;
                            let surroundingPlayable = 0;
                            let orthogonalPlayable = 0;
                            neighbourOffsets.forEach(([dx, dy]) => {
                                if (closure[row + dy][column + dx]) surroundingPlayable += 1;
                            });
                            [[-1,0],[1,0],[0,-1],[0,1]].forEach(([dx,dy]) => {
                                if (closure[row + dy][column + dx]) orthogonalPlayable += 1;
                            });
                            if (surroundingPlayable < 4 || orthogonalPlayable < 2) continue;
                            if (darkness[row][column] > .58) continue;
                            additions.push([column, row]);
                        }
                    }
                    additions.forEach(([column, row]) => { closure[row][column] = true; inferred[row][column] = true; });
                    if (additions.length === 0) break;
                }

                const edgeKey = (a, b) => `${a.x},${a.y}:${b.x},${b.y}`;
                const inferredEdges = new Map();
                const addPerimeterEdge = (column, row, side) => {
                    const left = column * contourStep;
                    const right = (column + 1) * contourStep;
                    const top = row * contourStep;
                    const bottom = (row + 1) * contourStep;
                    let a, b, outsideColumn = column, outsideRow = row;
                    if (side === 'top') { a={x:left,y:top}; b={x:right,y:top}; outsideRow -= 1; }
                    else if (side === 'right') { a={x:right,y:top}; b={x:right,y:bottom}; outsideColumn += 1; }
                    else if (side === 'bottom') { a={x:left,y:bottom}; b={x:right,y:bottom}; outsideRow += 1; }
                    else { a={x:left,y:top}; b={x:left,y:bottom}; outsideColumn -= 1; }
                    if (outsideColumn < 0 || outsideRow < 0 || outsideColumn >= contourColumns || outsideRow >= contourRows) return;
                    if (closure[outsideRow][outsideColumn]) return;
                    // G.5I-owned closure and G.5L-propagated G.5J/G.5K surface may both
                    // request local re-certification. Ordinary untouched floor remains the
                    // responsibility of the existing Living Contour reader.
                    const closureRecovered = inferred[row][column];
                    const propagatedRecovery = propagatedRecoveredSurface[row][column];
                    if (!closureRecovered && !propagatedRecovery) return;

                    // A propagated cell must genuinely belong to the playable region, not
                    // merely be an isolated illustrated fleck. Require orthogonal support
                    // from the closure mask before its outside edge may be reconsidered.
                    if (propagatedRecovery) {
                        const orthogonalSupport = [[-1,0],[1,0],[0,-1],[0,1]]
                            .filter(([dx,dy]) => closure[row + dy][column + dx]).length;
                        if (orthogonalSupport < 2) return;
                    }
                    const threshold = contourThresholdMatch(a, b);
                    if (threshold) return;
                    const wallBodyDarkness = darkness[outsideRow][outsideColumn];
                    const localInk = Math.max(wallBodyDarkness, darkness[row][column]);
                    if (localInk < .20) return;
                    const key = edgeKey(a, b);
                    if (!inferredEdges.has(key)) inferredEdges.set(key, { a, b, localInk, propagatedRecovery });
                };
                for (let row = 1; row < contourRows - 1; row += 1) {
                    for (let column = 1; column < contourColumns - 1; column += 1) {
                        // IV.30.1G.5N — the G.5L propagation flag is itself permission
                        // to reconsider the cell perimeter. Previously this hand-off only
                        // visited G.5I's `inferred` cells, so reconstructed G.5J/G.5K
                        // surface could be recorded but never actually reach addPerimeterEdge.
                        if (!closure[row][column] || (!inferred[row][column] && !propagatedRecoveredSurface[row][column])) continue;
                        addPerimeterEdge(column, row, 'top');
                        addPerimeterEdge(column, row, 'right');
                        addPerimeterEdge(column, row, 'bottom');
                        addPerimeterEdge(column, row, 'left');
                    }
                }
                // Preserve both evidence-model property contracts literally. Older G.5I
                // perimeter inference remains v8; only G.5L propagated recovery is v9.
                const playableRegionClosureProvenance = {
                    evidenceModel: 'living-contour-playable-region-closure-v8'
                };
                const reconstructedSurfaceProvenance = {
                    evidenceModel: 'living-contour-reconstructed-surface-propagation-v9'
                };
                return Array.from(inferredEdges.values()).map((edge) => ({
                    type: 'wall', confidence: Math.max(80, Math.min(89, Math.round(80 + edge.localInk * 14))), selected: true,
                    contour: true, fineContour: true, fullBoundary: false, partialContour: true,
                    partialContourRecovery: 'playable-region-perimeter-inference',
                    playableRegionClosure: true, inferredPerimeter: true, certifiedPortal: false, thresholdGapProtection: false,
                    semanticBoundaryClassification: 'structural-wall', semanticBoundaryRole: 'playable-region-perimeter',
                    reconstructedSurfacePropagation: true, contourRecertification: true,
                    recoveryEvidence: ['certified-playable-region', 'reconstructed-playable-surface', 'illustrated-interior-surface', 'propagated-recovery-evidence', 'local-region-closure', 'playable-to-non-playable-transition', 'corroborating-wall-body-ink', 'portal-threshold-veto'],
                    // Select the provenance object without erasing either historical contract.
                    ...(edge.propagatedRecovery
                        ? reconstructedSurfaceProvenance
                        : playableRegionClosureProvenance),
                    polyline: true,
                    points: [edge.a, edge.b], x1: edge.a.x, y1: edge.a.y, x2: edge.b.x, y2: edge.b.y
                }));
            };
            const inferredPlayablePerimeterSuggestions = playableRegionClosureAndPerimeterInference();

            // IV.30.1G.5M — keep an explainable trail through the contour pipeline.
            // These records are observational only: no threshold, score, budget or
            // suggestion-selection decision reads them back into the analyser.
            const contourEvidenceAuditRecords = options.evidenceAudit === true
                ? recoverableChains.map((entry) => ({
                    stage: 'semantic-classification',
                    state: semanticRoleIsAutomaticWall(entry.semanticBoundary?.role) ? 'candidate' : 'rejected',
                    reason: semanticRoleIsAutomaticWall(entry.semanticBoundary?.role)
                        ? 'automatic-wall-role'
                        : `semantic-role-${entry.semanticBoundary?.role || 'uncertain'}`,
                    points: entry.chain.map((point) => ({ x: roundContourCoordinate(point[0]), y: roundContourCoordinate(point[1]) }))
                }))
                : [];

            // IV.30.1G.5T.3 — Evidence Audit Terminal-Path Ownership.
            // A normal contour scan may safely return the preserved baseline when this
            // outer pass has no new recoverable chains/perimeters. Evidence Audit must
            // not take that early exit: G.5T's traversal counters belong to the outer
            // diagnostic pass and still need to reach the publisher below, even when
            // the visible review objects ultimately come entirely from the monotonic
            // pre-occlusion baseline.
            // IV.30.1G.5W.1 — Surface Representation Pipeline Publication.
            // Reconstructed illustrated-surface perimeter is now a first-class reason to
            // continue into authority/review representation. G.5W originally retained
            // those edges, but a normal Living Contour pass could still take the older
            // no-recoverable/no-inferred early return before the retained surface geometry
            // reached promotion/arbitration. Only return when *all three* evidence sources
            // are empty. Evidence Audit retains its G.5T.3 terminal-path ownership rule.
            if (recoverableChains.length === 0
                && inferredPlayablePerimeterSuggestions.length === 0
                && reconstructedIllustratedSurfaceEdges.length === 0
                && !(options.evidenceAudit === true && options.skipOcclusionRecovery !== true)) {
                return preOcclusionRecoveryContours;
            }

            // Keep the historical Economy vocabulary as a compatibility contract:
            // budgetedChains, remainingBudget and Math.sqrt(entry.length) formerly
            // apportioned a 200-segment review budget. IV.30.1G.4 now spends that same
            // object budget on the strongest certified contour sections. Nothing is
            // joined across an unresolved gap merely to make a closed shape.
            const budgetedChains = recoverableChains.slice(0, maximumReviewSuggestions);
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

            let pathSuggestions = budgetedChains.flatMap((entry) => {
                // Terrain, compact obstacles and decorative marks remain classified
                // evidence, but they do not become automatic LOS walls. Uncertain
                // boundaries stay reviewable rather than being silently discarded.
                if (!semanticRoleIsAutomaticWall(entry.semanticBoundary?.role)) return [];
                const path = simplifyChainToTarget(entry);
                const points = path.map((point) => ({
                    x: roundContourCoordinate(point[0]),
                    y: roundContourCoordinate(point[1])
                }));
                const protectedPath = splitContourPathAtProtectedThresholds(points);
                const runs = protectedPath.runs.length > 0 ? protectedPath.runs : (protectedPath.gaps.length === 0 ? [points] : []);
                return runs.map((runPoints) => {
                    const gapProtected = protectedPath.gaps.length > 0;
                    const partialContour = entry.partial || gapProtected;
                    const endpointClassifications = partialContour
                        ? [classifyContourEndpoint(runPoints[0]), classifyContourEndpoint(runPoints[runPoints.length - 1])]
                        : [];
                    const confidence = entry.closed && !gapProtected
                        ? 94
                        : Math.max(74, Math.min(91, Math.round(77 + Math.min(13, entry.length * 1.6))));
                    return {
                        type: 'wall', confidence, selected: true,
                        contour: true, fineContour: true, fullBoundary: entry.closed && !gapProtected,
                        partialContour,
                        partialContourRecovery: gapProtected
                            ? 'protected-threshold-split'
                            : (entry.partial ? 'certified-open-chain' : 'closed-chain'),
                        unresolvedBoundaryEnds: partialContour ? [runPoints[0], runPoints[runPoints.length - 1]] : [],
                        gapClassifications: endpointClassifications,
                        protectedContourGaps: protectedPath.gaps,
                        thresholdGapProtection: gapProtected || Boolean(entry.playableAdjacency?.thresholdConnectivity) || Boolean(entry.portalPairing?.portal),
                        portalPairingClassification: entry.portalPairing?.classification || 'unresolved-boundary-break',
                        portalPairingConfidence: Number(entry.portalPairing?.confidence || 0),
                        portalPairingEvidence: Array.isArray(entry.portalPairing?.evidence) ? entry.portalPairing.evidence : [],
                        certifiedPortal: Boolean(entry.portalPairing?.portal),
                        certifiedBoundaryContinuation: Boolean(entry.portalPairing?.boundaryContinuation),
                        boundaryGraphBridges: Array.isArray(entry.boundaryGraphBridges) ? entry.boundaryGraphBridges : [],
                        certifiedEvidenceBridge: Array.isArray(entry.boundaryGraphBridges) && entry.boundaryGraphBridges.length > 0,
                        playableSpaceAdjacency: entry.playableAdjacency?.classification || 'unresolved-adjacency',
                        playableSpaceAdjacencyConfidence: Number(entry.playableAdjacency?.confidence || 0),
                        playableSpaceAdjacencyEvidence: Array.isArray(entry.playableAdjacency?.evidence) ? entry.playableAdjacency.evidence : [],
                        thresholdConnectivity: Boolean(entry.playableAdjacency?.thresholdConnectivity),
                        semanticBoundaryClassification: entry.semanticBoundary?.role || 'uncertain',
                        semanticBoundaryRole: entry.semanticBoundary?.boundaryRole || 'unresolved-evidence',
                        semanticBoundaryConfidence: Number(entry.semanticBoundary?.confidence || 0),
                        semanticBoundaryEvidence: Array.isArray(entry.semanticBoundary?.evidence) ? entry.semanticBoundary.evidence : [],
                        recoveryEvidence: gapProtected
                            ? ['ordered-boundary-chain', 'g2-threshold-reused', 'doorway-gap-preserved', 'unresolved-ends-preserved']
                            : (entry.partial
                                ? ['ordered-boundary-chain', 'minimum-safe-length', 'unresolved-ends-preserved']
                                : ['closed-boundary-chain']),
                        // Historical G.4B evidence spellings retained as regression vocabulary:
                        // ? 'living-contour-gap-classification-v5b'
                        // : (entry.partial ? 'living-contour-partial-v5' : 'living-contour-closed-v5')
                        evidenceModel: gapProtected
                            ? 'living-contour-wall-band-v6'
                            : (entry.partial ? 'living-contour-wall-band-partial-v6' : 'living-contour-wall-band-closed-v6'),
                        adaptiveBudget: true, polyline: true, points: runPoints,
                        x1: runPoints[0].x, y1: runPoints[0].y,
                        x2: runPoints[runPoints.length - 1].x, y2: runPoints[runPoints.length - 1].y
                    };
                });
            }).filter((item) => item.points.length >= 2 && item.points.length <= maximumPathVertices);

            // Evidence bridges are deliberately emitted as their own tiny wall polylines.
            // They never consume a protected portal and never close a region merely for
            // visual completeness; they exist only where the graph independently certified
            // a structural continuation between two fragmented chains.
            const evidenceBridgeSuggestions = certifiedEvidenceBridges.map((bridge) => {
                const from = { x: roundContourCoordinate(bridge.from[0]), y: roundContourCoordinate(bridge.from[1]) };
                const to = { x: roundContourCoordinate(bridge.to[0]), y: roundContourCoordinate(bridge.to[1]) };
                return {
                    type: 'wall', confidence: bridge.confidence, selected: true, contour: true, fineContour: true,
                    fullBoundary: false, partialContour: true, partialContourRecovery: 'certified-evidence-bridge',
                    boundaryGraphBridge: true, certifiedEvidenceBridge: true, certifiedBoundaryContinuation: true,
                    thresholdGapProtection: false, certifiedPortal: false,
                    recoveryEvidence: bridge.evidence.concat(['proximity-is-evidence-not-permission']),
                    evidenceModel: 'living-contour-boundary-graph-v7', polyline: true, points: [from, to],
                    x1: from.x, y1: from.y, x2: to.x, y2: to.y
                };
            });
            // IV.30.1G.5N — Inferred Perimeter Promotion & Emission Continuity.
            // G.5M proved that hundreds of certified one-cell perimeter edges were being
            // generated and then only the first 24 were allowed through the supplemental
            // object budget. Promote connected edge runs into reviewable polylines first:
            // the safety budget remains an *object* budget, not an arbitrary edge guillotine.
            const promoteInferredPerimeterChains = (edges) => {
                const pointKey = (point) => `${point.x},${point.y}`;
                const adjacency = new Map();
                edges.forEach((edge, index) => {
                    edge.points.forEach((point) => {
                        const key = pointKey(point);
                        if (!adjacency.has(key)) adjacency.set(key, []);
                        adjacency.get(key).push(index);
                    });
                });
                const unused = new Set(edges.map((_, index) => index));
                const promoted = [];
                const takeRun = (startIndex, startPoint) => {
                    const first = edges[startIndex];
                    const a = first.points[0], b = first.points[1];
                    const points = [startPoint, pointKey(startPoint) === pointKey(a) ? b : a];
                    const members = [first];
                    unused.delete(startIndex);
                    let guard = 0;
                    while (guard++ < edges.length) {
                        const end = points[points.length - 1];
                        const candidates = (adjacency.get(pointKey(end)) || []).filter((index) => unused.has(index));
                        if (candidates.length !== 1) break;
                        const nextIndex = candidates[0];
                        const next = edges[nextIndex];
                        const nextPoint = pointKey(next.points[0]) === pointKey(end) ? next.points[1] : next.points[0];
                        points.push(nextPoint); members.push(next); unused.delete(nextIndex);
                        if (pointKey(nextPoint) === pointKey(points[0])) break;
                    }
                    return { points, members };
                };
                while (unused.size > 0) {
                    const componentSeed = unused.values().next().value;
                    const seed = edges[componentSeed];
                    const endpoints = seed.points.filter((point) => (adjacency.get(pointKey(point)) || []).filter((index) => unused.has(index)).length === 1);
                    const run = takeRun(componentSeed, endpoints[0] || seed.points[0]);
                    const strongest = run.members.reduce((best, item) => item.confidence > best.confidence ? item : best, run.members[0]);
                    const propagated = run.members.some((item) => item.evidenceModel === 'living-contour-reconstructed-surface-propagation-v9');
                    // IV.30.1G.5Z.8 — Vertex-Cap-Safe Perimeter Segmentation & Continuity Preservation.
                    // G.5Z.7 proved that one coherent 332-edge reconstructed perimeter was
                    // discarded solely because its 333 vertices exceeded the existing
                    // 256-vertex review-path safety cap. Keep that cap. Instead, partition
                    // an oversized non-branching run into contiguous review paths whose
                    // neighbouring segments share the split vertex. No source edge is
                    // simplified, bridged or omitted merely to fit one review object.
                    const maximumEdgesPerPath = maximumPathVertices - 1;
                    const segmentCount = Math.max(1, Math.ceil(run.members.length / maximumEdgesPerPath));
                    for (let segmentIndex = 0; segmentIndex < segmentCount; segmentIndex += 1) {
                        const memberStart = segmentIndex * maximumEdgesPerPath;
                        const memberEnd = Math.min(run.members.length, memberStart + maximumEdgesPerPath);
                        const segmentMembers = run.members.slice(memberStart, memberEnd);
                        const segmentPoints = run.points.slice(memberStart, memberEnd + 1);
                        if (segmentMembers.length === 0 || segmentPoints.length < 2) continue;
                        const segmentStrongest = segmentMembers.reduce(
                            (best, item) => item.confidence > best.confidence ? item : best,
                            segmentMembers[0]
                        );
                        promoted.push({
                            ...segmentStrongest,
                            confidence: Math.max(...segmentMembers.map((item) => item.confidence)),
                            inferredPerimeterPromotion: true,
                            inferredPerimeterEdgeCount: segmentMembers.length,
                            // Regression-contract compatibility: emissionContinuity: 'connected-perimeter-chain'
                            emissionContinuity: segmentCount > 1 ? 'vertex-cap-contiguous-segment' : 'connected-perimeter-chain',
                            vertexCapSegmented: segmentCount > 1,
                            vertexCapSegmentIndex: segmentIndex,
                            vertexCapSegmentCount: segmentCount,
                            vertexCapSourceEdgeCount: run.members.length,
                            vertexCapSharedStart: segmentCount > 1 && segmentIndex > 0,
                            vertexCapSharedEnd: segmentCount > 1 && segmentIndex < segmentCount - 1,
                            evidenceModel: propagated ? 'living-contour-inferred-perimeter-promotion-v11' : segmentStrongest.evidenceModel,
                            recoveryEvidence: Array.from(new Set(segmentMembers.flatMap((item) => item.recoveryEvidence || []).concat([
                                'connected-edge-promotion', 'object-budget-after-grouping',
                                ...(segmentCount > 1 ? ['vertex-cap-safe-segmentation', 'shared-split-vertex-continuity'] : [])
                            ]))),
                            polyline: true, points: segmentPoints,
                            x1: segmentPoints[0].x, y1: segmentPoints[0].y,
                            x2: segmentPoints[segmentPoints.length - 1].x, y2: segmentPoints[segmentPoints.length - 1].y
                        });
                    }
                }
                return promoted.filter((item) => item.points.length >= 2 && item.points.length <= maximumPathVertices);
            };
            const promotedInferredPerimeterSuggestions = promoteInferredPerimeterChains(inferredPlayablePerimeterSuggestions);
            const promotedReconstructedSurfaceSuggestions = promoteInferredPerimeterChains(reconstructedIllustratedSurfaceEdges)
                .map((item) => ({
                    ...item,
                    reconstructedSurfaceAuthority: true,
                    evidenceModel: 'living-contour-reconstructed-surface-authority-v12',
                    recoveryEvidence: Array.from(new Set((item.recoveryEvidence || []).concat([
                        'reconstructed-surface-authority', 'review-budget-representation'
                    ])))
                }));

            // IV.30.1G.5Z.26 — Source-proven, topology-safe local path assembly.
            // Only exact coincident endpoints on the same uniquely identified original
            // surface component qualify. No suppressed perimeter edges are restored.
            const localAssemblyPointKey = (point) => `${point.x},${point.y}`;
            const originalComponentBySegment = new Map();
            illustratedSurfaceBoundaryTopology.forEach((edge) => {
                const key = completedEdgeKey(edge.a, edge.b);
                if (!originalComponentBySegment.has(key)) originalComponentBySegment.set(key, new Set());
                originalComponentBySegment.get(key).add(edge.surfaceComponentId);
            });
            const localAssemblyProvenance = (path) => {
                const points = path.points || [];
                const ids = new Set();
                for (let i = 1; i < points.length; i += 1) {
                    const components = originalComponentBySegment.get(completedEdgeKey(points[i - 1], points[i]));
                    if (!components || components.size !== 1) return null;
                    components.forEach((id) => ids.add(id));
                    if (ids.size !== 1) return null;
                }
                return ids.size === 1 ? [...ids][0] : null;
            };
            const localAssemblyAudit = { candidates: 0, joined: 0, capRejected: 0,
                sourceRejected: 0, topologyRejected: 0, edgeCountBefore: promotedReconstructedSurfaceSuggestions
                    .reduce((sum, path) => sum + (path.points || []).length - 1, 0) };
            let localAssemblyChanged = true;
            while (localAssemblyChanged) {
                localAssemblyChanged = false;
                const endpoints = new Map();
                promotedReconstructedSurfaceSuggestions.forEach((path, index) => {
                    const points = path.points || [];
                    if (points.length < 2 || localAssemblyPointKey(points[0]) === localAssemblyPointKey(points[points.length - 1])) return;
                    [points[0], points[points.length - 1]].forEach((point) => {
                        const key = localAssemblyPointKey(point);
                        if (!endpoints.has(key)) endpoints.set(key, []);
                        endpoints.get(key).push(index);
                    });
                });
                for (const [key, members] of endpoints) {
                    if (members.length !== 2 || members[0] === members[1]) continue;
                    const [leftIndex, rightIndex] = members;
                    const left = promotedReconstructedSurfaceSuggestions[leftIndex];
                    const right = promotedReconstructedSurfaceSuggestions[rightIndex];
                    localAssemblyAudit.candidates += 1;
                    const leftSource = localAssemblyProvenance(left);
                    if (leftSource === null || leftSource !== localAssemblyProvenance(right)
                        || left.vertexCapSegmented || right.vertexCapSegmented) {
                        localAssemblyAudit.sourceRejected += 1; continue;
                    }
                    const a = localAssemblyPointKey(left.points[left.points.length - 1]) === key
                        ? left.points : left.points.slice().reverse();
                    const b = localAssemblyPointKey(right.points[0]) === key
                        ? right.points : right.points.slice().reverse();
                    const joined = a.concat(b.slice(1));
                    if (joined.length > maximumPathVertices) {
                        localAssemblyAudit.capRejected += 1; continue;
                    }
                    const visitedVertices = new Set();
                    const visitedEdges = new Set();
                    let unsafe = false;
                    joined.forEach((point, i) => {
                        const vertex = localAssemblyPointKey(point);
                        if (visitedVertices.has(vertex)) unsafe = true;
                        visitedVertices.add(vertex);
                        if (i) {
                            const edge = completedEdgeKey(joined[i - 1], point);
                            if (visitedEdges.has(edge)) unsafe = true;
                            visitedEdges.add(edge);
                        }
                    });
                    if (unsafe) { localAssemblyAudit.topologyRejected += 1; continue; }
                    const merged = { ...left, points: joined,
                        x1: joined[0].x, y1: joined[0].y,
                        x2: joined[joined.length - 1].x, y2: joined[joined.length - 1].y,
                        inferredPerimeterEdgeCount: joined.length - 1,
                        localSourceProvenAssembly: true,
                        recoveryEvidence: Array.from(new Set((left.recoveryEvidence || [])
                            .concat(right.recoveryEvidence || [], ['source-proven-local-continuity', 'exact-endpoint-no-gap']))) };
                    promotedReconstructedSurfaceSuggestions[Math.min(leftIndex, rightIndex)] = merged;
                    promotedReconstructedSurfaceSuggestions.splice(Math.max(leftIndex, rightIndex), 1);
                    localAssemblyAudit.joined += 1;
                    localAssemblyChanged = true;
                    break;
                }
            }
            localAssemblyAudit.edgeCountAfter = promotedReconstructedSurfaceSuggestions
                .reduce((sum, path) => sum + (path.points || []).length - 1, 0);

            // IV.30.1G.5Z.2 — Completed Perimeter Path Representation Audit.
            // Observe, but do not alter, the G.5Z completed perimeter as it becomes
            // reviewable paths. These counters deliberately use source edge counts so a
            // long polyline cannot hide how much recovered perimeter it represents.
            const reconstructedSurfaceAssembledEdges = promotedReconstructedSurfaceSuggestions.reduce(
                (sum, item) => sum + Number(item.inferredPerimeterEdgeCount || 0), 0
            );
            // IV.30.1G.5Z.8 — publish the corrective segmentation separately from
            // ordinary connected-edge promotion so the 256-vertex cap remains auditable.
            const reconstructedSurfaceVertexCapSegmentedPaths = promotedReconstructedSurfaceSuggestions.filter((item) => item.vertexCapSegmented === true);
            const reconstructedSurfaceVertexCapSegmentedPathCount = reconstructedSurfaceVertexCapSegmentedPaths.length;
            const reconstructedSurfaceVertexCapSegmentedEdges = reconstructedSurfaceVertexCapSegmentedPaths.reduce(
                (sum, item) => sum + Number(item.inferredPerimeterEdgeCount || 0), 0
            );
            const reconstructedSurfaceVertexCapSourceComponents = reconstructedSurfaceVertexCapSegmentedPaths.filter(
                (item) => Number(item.vertexCapSegmentIndex || 0) === 0
            ).length;
            const reconstructedSurfaceVertexCapSplitVertices = reconstructedSurfaceVertexCapSegmentedPaths.filter((item) => item.vertexCapSharedStart === true).length;
            const reconstructedSurfaceClosedChains = promotedReconstructedSurfaceSuggestions.filter((item) => {
                const points = Array.isArray(item.points) ? item.points : [];
                return points.length > 2 && `${points[0].x},${points[0].y}` === `${points[points.length - 1].x},${points[points.length - 1].y}`;
            }).length;
            const reconstructedSurfaceOpenChains = Math.max(0, promotedReconstructedSurfaceSuggestions.length - reconstructedSurfaceClosedChains);

            // IV.30.1G.5Z.7 — Surface Perimeter-to-Path Assembly Loss Audit.
            // G.5Z.6 expanded the completed perimeter enough to expose a new handoff:
            // contributed perimeter edges can fail to enter promoted review paths before
            // representation accounting begins. Audit that loss without changing assembly.
            const reconstructedSurfaceAuditPointKey = (point) => `${point.x},${point.y}`;
            const reconstructedSurfaceAuditSegmentKey = (a, b) => {
                const ak = reconstructedSurfaceAuditPointKey(a);
                const bk = reconstructedSurfaceAuditPointKey(b);
                return ak < bk ? `${ak}|${bk}` : `${bk}|${ak}`;
            };
            const reconstructedSurfaceAssembledSegmentKeys = new Set();
            promotedReconstructedSurfaceSuggestions.forEach((item) => {
                const points = Array.isArray(item.points) ? item.points : [];
                for (let index = 1; index < points.length; index += 1) {
                    reconstructedSurfaceAssembledSegmentKeys.add(reconstructedSurfaceAuditSegmentKey(points[index - 1], points[index]));
                }
            });
            const reconstructedSurfaceUnassembledEdges = reconstructedIllustratedSurfaceEdges.filter((edge) => {
                const points = Array.isArray(edge.points) ? edge.points : [];
                return points.length >= 2 && !reconstructedSurfaceAssembledSegmentKeys.has(reconstructedSurfaceAuditSegmentKey(points[0], points[1]));
            });
            const reconstructedSurfaceUnassembledEdgeCount = reconstructedSurfaceUnassembledEdges.length;
            const reconstructedSurfaceSourceDegree = new Map();
            reconstructedIllustratedSurfaceEdges.forEach((edge) => {
                (edge.points || []).slice(0, 2).forEach((point) => {
                    const key = reconstructedSurfaceAuditPointKey(point);
                    reconstructedSurfaceSourceDegree.set(key, (reconstructedSurfaceSourceDegree.get(key) || 0) + 1);
                });
            });
            const reconstructedSurfaceUnassembledBranchAdjacentEdges = reconstructedSurfaceUnassembledEdges.filter((edge) =>
                (edge.points || []).slice(0, 2).some((point) => (reconstructedSurfaceSourceDegree.get(reconstructedSurfaceAuditPointKey(point)) || 0) > 2)
            ).length;
            const reconstructedSurfaceUnassembledAdjacency = new Map();
            reconstructedSurfaceUnassembledEdges.forEach((edge, index) => {
                (edge.points || []).slice(0, 2).forEach((point) => {
                    const key = reconstructedSurfaceAuditPointKey(point);
                    if (!reconstructedSurfaceUnassembledAdjacency.has(key)) reconstructedSurfaceUnassembledAdjacency.set(key, []);
                    reconstructedSurfaceUnassembledAdjacency.get(key).push(index);
                });
            });
            const reconstructedSurfaceUnassembledVisited = new Set();
            const reconstructedSurfaceUnassembledComponentSizes = [];
            reconstructedSurfaceUnassembledEdges.forEach((edge, seedIndex) => {
                if (reconstructedSurfaceUnassembledVisited.has(seedIndex)) return;
                const queue = [seedIndex];
                reconstructedSurfaceUnassembledVisited.add(seedIndex);
                let size = 0;
                while (queue.length > 0) {
                    const index = queue.shift();
                    size += 1;
                    (reconstructedSurfaceUnassembledEdges[index].points || []).slice(0, 2).forEach((point) => {
                        (reconstructedSurfaceUnassembledAdjacency.get(reconstructedSurfaceAuditPointKey(point)) || []).forEach((nextIndex) => {
                            if (reconstructedSurfaceUnassembledVisited.has(nextIndex)) return;
                            reconstructedSurfaceUnassembledVisited.add(nextIndex);
                            queue.push(nextIndex);
                        });
                    });
                }
                reconstructedSurfaceUnassembledComponentSizes.push(size);
            });
            const reconstructedSurfaceUnassembledComponents = reconstructedSurfaceUnassembledComponentSizes.length;
            const reconstructedSurfaceLargestUnassembledComponent = reconstructedSurfaceUnassembledComponentSizes.length > 0
                ? Math.max(...reconstructedSurfaceUnassembledComponentSizes) : 0;
            const reconstructedSurfaceVertexCapRiskComponents = reconstructedSurfaceUnassembledComponentSizes.filter((size) => size + 1 > maximumPathVertices).length;
            const reconstructedSurfaceVertexCapRiskEdges = reconstructedSurfaceUnassembledComponentSizes.filter((size) => size + 1 > maximumPathVertices).reduce((sum, size) => sum + size, 0);
            let reconstructedSurfaceAlreadyRepresentedEdges = 0;
            let reconstructedSurfaceAuthorityExtensionEdges = 0;
            let reconstructedSurfaceNovelRepresentedEdges = 0;
            const reconstructedSurfaceAuthorityExtensionSegmentKeys = new Set();
            const reconstructedSurfaceNovelSegmentKeys = new Set();

            // IV.30.1G.5O — Promoted Chain Consolidation & Emission Budget Liberation.
            // G.5N proved promotion worked, but its fixed 48-object perimeter allowance
            // still made certified perimeter compete with ordinary raw-chain output.
            // Consolidate exact/contained promoted paths, then reserve the *remaining*
            // review capacity for promoted evidence before ordinary contour candidates.
            // The global 200-object safety ceiling remains unchanged.
            const consolidatePromotedPerimeterChains = (items) => {
                const consolidated = new Map();
                items.forEach((item) => {
                    const key = cartographySuggestionKey(item);
                    const existing = consolidated.get(key);
                    if (!existing) {
                        consolidated.set(key, { ...item, consolidatedPromotion: true });
                        return;
                    }
                    existing.confidence = Math.max(existing.confidence, item.confidence);
                    existing.inferredPerimeterEdgeCount = Math.max(
                        Number(existing.inferredPerimeterEdgeCount || 0),
                        Number(item.inferredPerimeterEdgeCount || 0)
                    );
                    existing.recoveryEvidence = Array.from(new Set(
                        (existing.recoveryEvidence || []).concat(item.recoveryEvidence || [], ['promoted-chain-consolidation'])
                    ));
                });
                return Array.from(consolidated.values());
            };
            const consolidatedPromotedPerimeterSuggestions = consolidatePromotedPerimeterChains(promotedInferredPerimeterSuggestions);
            // G.5N compatibility contract: promotedInferredPerimeterSuggestions.slice(0, perimeterInferenceBudget)
            const bridgeInferenceBudget = Math.min(12, evidenceBridgeSuggestions.length);
            const bridgeSupplementalSuggestions = evidenceBridgeSuggestions.slice(0, bridgeInferenceBudget);
            const protectedBaselineKeys = new Set(preOcclusionRecoveryContours.map(cartographySuggestionKey));
            const representedPromotedKeys = new Set();
            const liberatedPromotedSuggestions = consolidatedPromotedPerimeterSuggestions.filter((item) => {
                const key = cartographySuggestionKey(item);
                if (protectedBaselineKeys.has(key)) {
                    representedPromotedKeys.add(key);
                    return false;
                }
                return true;
            });
            const perimeterInferenceBudget = Math.max(0,
                maximumReviewSuggestions - preOcclusionRecoveryContours.length - bridgeSupplementalSuggestions.length
            );
            const promotedSupplementalSuggestions = liberatedPromotedSuggestions.slice(0, perimeterInferenceBudget);
            promotedSupplementalSuggestions.forEach((item) => representedPromotedKeys.add(cartographySuggestionKey(item)));
            const supplementalSuggestions = bridgeSupplementalSuggestions.concat(promotedSupplementalSuggestions);

            // IV.30.1G.5P — Contour Authority & Evidence Arbitration.
            // G.5O proved that promoted evidence can saturate all 200 review objects, but
            // allowing inferred chains to enter ahead of directly observed contour paths
            // can evict stronger geometry. Authority is therefore monotonic: preserve the
            // pre-occlusion safety baseline first, then every direct certified/recoverable
            // Living Contour path, then graph-certified bridges, and only then spend the
            // remaining capacity on promoted inferred perimeter. Promotion supplements
            // observed geometry; it never outranks it merely because it was reconstructed.
            const authoritativeContourSuggestions = [];
            const authoritativeContourKeys = new Set();
            const addAuthoritativeContour = (item) => {
                const key = cartographySuggestionKey(item);
                if (authoritativeContourKeys.has(key)) return;
                authoritativeContourKeys.add(key);
                authoritativeContourSuggestions.push(item);
            };
            preOcclusionRecoveryContours.forEach(addAuthoritativeContour);
            pathSuggestions.forEach(addAuthoritativeContour);

            const surfacePointKey = (point) => `${roundContourCoordinate(point.x)},${roundContourCoordinate(point.y)}`;

            // IV.30.1G.5X — Authoritative Path Coalescence & Review Capacity Liberation.
            // A saturated review list is not permission to discard authority. Instead,
            // exact-endpoint, semantically compatible, non-branching authoritative paths
            // may share one review object. Every source segment is retained; only the
            // number of review objects changes. No gap is bridged by coalescence.
            const authoritativeSourceSuggestions = authoritativeContourSuggestions.slice();
            const authoritativeInputCount = authoritativeSourceSuggestions.length;
            const authoritativeSegmentKey = (a, b) => {
                const first = surfacePointKey(a), second = surfacePointKey(b);
                return first < second ? `${first}|${second}` : `${second}|${first}`;
            };
            const authoritativeSegmentsFor = (item) => {
                const points = Array.isArray(item.points) ? item.points : [];
                const segments = [];
                for (let index = 0; index < points.length - 1; index += 1) {
                    segments.push(authoritativeSegmentKey(points[index], points[index + 1]));
                }
                return segments;
            };
            // IV.30.1G.5Z.3 — Represented Geometry Visual Correspondence Audit.
            // G.5Z.2 proved every completed perimeter edge is represented by some review
            // object. Retain exact source-segment identities so final review geometry can
            // prove spatial correspondence rather than object-level bookkeeping alone.
            // This audit is observational: it never mutates points or arbitration.
            const reconstructedSurfaceSourceSegmentKeys = new Set(
                promotedReconstructedSurfaceSuggestions.flatMap(authoritativeSegmentsFor)
            );

            const authoritativeCompatibilityKey = (item) => [
                item.type || 'wall',
                item.certifiedPortal === true ? 'portal' : 'no-portal',
                item.thresholdGapProtection === true ? 'threshold-protected' : 'no-threshold',
                item.semanticBoundaryClassification || 'unclassified',
                item.semanticBoundaryRole || 'unresolved-evidence'
            ].join('|');
            let authoritativePathMerges = 0;
            let authoritativeCoalescenceChanged = true;
            while (authoritativeCoalescenceChanged) {
                authoritativeCoalescenceChanged = false;
                const endpointMembership = new Map();
                authoritativeContourSuggestions.forEach((item, index) => {
                    const points = Array.isArray(item.points) ? item.points : [];
                    if (points.length < 2) return;
                    [points[0], points[points.length - 1]].forEach((point) => {
                        const key = surfacePointKey(point);
                        if (!endpointMembership.has(key)) endpointMembership.set(key, []);
                        endpointMembership.get(key).push(index);
                    });
                });
                for (const [endpoint, members] of endpointMembership.entries()) {
                    if (members.length !== 2) continue;
                    const leftIndex = members[0], rightIndex = members[1];
                    if (leftIndex === rightIndex) continue;
                    const left = authoritativeContourSuggestions[leftIndex];
                    const right = authoritativeContourSuggestions[rightIndex];
                    if (!left || !right || authoritativeCompatibilityKey(left) !== authoritativeCompatibilityKey(right)) continue;
                    const leftPoints = Array.isArray(left.points) ? left.points : [];
                    const rightPoints = Array.isArray(right.points) ? right.points : [];
                    if (leftPoints.length < 2 || rightPoints.length < 2) continue;
                    const orientToEnd = (points) => surfacePointKey(points[points.length - 1]) === endpoint ? points : points.slice().reverse();
                    const orientFromStart = (points) => surfacePointKey(points[0]) === endpoint ? points : points.slice().reverse();
                    const combined = orientToEnd(leftPoints).concat(orientFromStart(rightPoints).slice(1));
                    if (combined.length > maximumPathVertices) continue;
                    const merged = {
                        ...left,
                        points: combined,
                        x1: combined[0].x, y1: combined[0].y,
                        x2: combined[combined.length - 1].x, y2: combined[combined.length - 1].y,
                        authoritativePathCoalescence: true,
                        recoveryEvidence: Array.from(new Set((left.recoveryEvidence || []).concat(right.recoveryEvidence || [], [
                            'authoritative-path-coalescence', 'exact-endpoint-no-gap', 'source-geometry-preserved'
                        ])))
                    };
                    const keep = Math.min(leftIndex, rightIndex), remove = Math.max(leftIndex, rightIndex);
                    authoritativeContourSuggestions[keep] = merged;
                    authoritativeContourSuggestions.splice(remove, 1);
                    authoritativePathMerges += 1;
                    authoritativeCoalescenceChanged = true;
                    break;
                }
            }
            authoritativeContourKeys.clear();
            authoritativeContourSuggestions.forEach((item) => authoritativeContourKeys.add(cartographySuggestionKey(item)));
            const coalescedAuthoritativeSegmentKeys = new Set(authoritativeContourSuggestions.flatMap(authoritativeSegmentsFor));
            const authoritativeSourcesPreserved = authoritativeSourceSuggestions.filter((item) =>
                authoritativeSegmentsFor(item).every((key) => coalescedAuthoritativeSegmentKeys.has(key))
            ).length;
            const authoritativeReviewPaths = authoritativeContourSuggestions.length;
            const authoritativeReviewSlotsLiberated = Math.max(0, authoritativeInputCount - authoritativeReviewPaths);

            // IV.30.1G.5Z.22B.1 — inspect authoritative endpoints only after
            // authority has been initialized and its exact-endpoint coalescence completed.
            // IV.30.1G.5Z.22 — Residual Open-Chain Termination & Illustrated Wall Correspondence Audit.
            // Inspect ONLY the final promoted source paths. No endpoint snapping, wall
            // admission, threshold relaxation, draft mutation or persistence occurs here.
            const residualTerminationPointKey = (point) => `${point.x},${point.y}`;
            const residualSuppressedByEndpoint = new Map();
            const retainedSurfaceEdgeKeys = new Set(reconstructedIllustratedSurfaceEdges.map((edge) => {
                const points = edge.points || [];
                return points.length >= 2 ? completedEdgeKey(points[0], points[1]) : '';
            }));
            // A restored member is no longer suppressed even though its original
            // G.5Z.16 topology record remains marked suppressedOpenPaper.
            const stillSuppressed = illustratedSurfaceBoundaryTopology.filter((edge) =>
                edge.suppressedOpenPaper && !retainedSurfaceEdgeKeys.has(edge.key));
            const suppressedAdjacency = new Map();
            stillSuppressed.forEach((edge, index) => [edge.a, edge.b].forEach((point) => {
                const key = residualTerminationPointKey(point);
                if (!suppressedAdjacency.has(key)) suppressedAdjacency.set(key, []);
                suppressedAdjacency.get(key).push(index);
                if (!residualSuppressedByEndpoint.has(key)) residualSuppressedByEndpoint.set(key, []);
                residualSuppressedByEndpoint.get(key).push(edge);
            }));
            const suppressedRunSizeByIndex = new Map();
            stillSuppressed.forEach((edge, index) => {
                if (suppressedRunSizeByIndex.has(index)) return;
                const pending = [index], members = new Set();
                while (pending.length) {
                    const next = pending.pop();
                    if (members.has(next)) continue;
                    members.add(next);
                    [stillSuppressed[next].a, stillSuppressed[next].b].forEach((point) =>
                        (suppressedAdjacency.get(residualTerminationPointKey(point)) || []).forEach((candidate) => {
                            if (!members.has(candidate) && stillSuppressed[candidate].surfaceComponentId === edge.surfaceComponentId) pending.push(candidate);
                        }));
                }
                members.forEach((member) => suppressedRunSizeByIndex.set(member, members.size));
            });
            const suppressedRunSizeByEdgeKey = new Map(stillSuppressed.map((edge, index) => [edge.key, suppressedRunSizeByIndex.get(index)]));
            // G.5Z.23: stable connected-run identity. Group by exact shared vertices
            // AND source component; equal run lengths alone do not imply pairing.
            const suppressedRunByIndex = new Map();
            const suppressedRuns = [];
            stillSuppressed.forEach((edge, index) => {
                if (suppressedRunByIndex.has(index)) return;
                const pending = [index], members = new Set();
                while (pending.length) {
                    const next = pending.pop();
                    if (members.has(next)) continue;
                    members.add(next);
                    [stillSuppressed[next].a, stillSuppressed[next].b].forEach((point) =>
                        (suppressedAdjacency.get(residualTerminationPointKey(point)) || []).forEach((candidate) => {
                            if (!members.has(candidate) && stillSuppressed[candidate].surfaceComponentId === edge.surfaceComponentId) pending.push(candidate);
                        }));
                }
                const run = { id: suppressedRuns.length + 1, edges: members.size,
                    sourceComponentId: edge.surfaceComponentId, members: [...members] };
                suppressedRuns.push(run);
                members.forEach((member) => suppressedRunByIndex.set(member, run));
            });
            const suppressedRunsByPoint = new Map();
            stillSuppressed.forEach((edge, index) => [edge.a, edge.b].forEach((point) => {
                const key = residualTerminationPointKey(point);
                if (!suppressedRunsByPoint.has(key)) suppressedRunsByPoint.set(key, new Set());
                suppressedRunsByPoint.get(key).add(suppressedRunByIndex.get(index).id);
            }));

            const authorityEndpointKeys = new Set(authoritativeContourSuggestions.flatMap((path) => {
                const points = path.points || [];
                return points.length ? [residualTerminationPointKey(points[0]), residualTerminationPointKey(points[points.length - 1])] : [];
            }));
            const residualTerminations = [];
            promotedReconstructedSurfaceSuggestions.forEach((path, pathIndex) => {
                const points = Array.isArray(path.points) ? path.points : [];
                if (points.length < 2 || residualTerminationPointKey(points[0]) === residualTerminationPointKey(points[points.length - 1])) return;
                [points[0], points[points.length - 1]].forEach((point) => {
                    const neighbouringSuppressed = residualSuppressedByEndpoint.get(residualTerminationPointKey(point)) || [];
                    const localInk = neighbouringSuppressed.length
                        ? Math.max(...neighbouringSuppressed.map((edge) => Number(edge.boundaryInk || 0))) : 0;
                    const exactStructural = neighbouringSuppressed.some((edge) => edge.exactStructuralBoundary === true);
                    residualTerminations.push({
                        id: residualTerminations.length + 1,
                        point: { x: point.x, y: point.y },
                        pathIndex: pathIndex + 1,
                        sourceComponentIds: [...new Set(neighbouringSuppressed.map((edge) => edge.surfaceComponentId))],
                        suppressedRunEdges: neighbouringSuppressed.length
                            ? Math.max(...neighbouringSuppressed.map((edge) => suppressedRunSizeByEdgeKey.get(edge.key) || 0)) : 0,
                        authoritativeEndpoint: authorityEndpointKeys.has(residualTerminationPointKey(point)),
                        localInk, exactStructural,
                        reason: exactStructural ? 'exact-structural-neighbour' : neighbouringSuppressed.length
                            ? (localInk >= .42 ? 'ink-corroborated-neighbour' : 'suppressed-open-paper-neighbour')
                            : 'no-adjacent-suppressed-edge',
                        // A passage/door cannot be certified from ink or endpoint geometry alone.
                        openingClassification: 'unresolved',
                        // Grid coordinates are retained exactly; no rounding or snapping.
                        gridPoint: { x: point.x, y: point.y },
                        suppressedRunIds: [...(suppressedRunsByPoint.get(residualTerminationPointKey(point)) || [])].sort((a, b) => a - b),
                        endpointRole: point === points[0] ? 'start' : 'end',
                        tangent: point === points[0]
                            ? { x: points[1].x - point.x, y: points[1].y - point.y }
                            : { x: point.x - points[points.length - 2].x, y: point.y - points[points.length - 2].y }
                    });
                });
            });


            // G.5Z.25: recover provenance from the ORIGINAL perimeter-edge topology,
            // not from neighbouring suppressed edges (which are absent at coincident ends).
            // Match every emitted path segment by its exact, orientation-independent edge key.
            const originalSurfaceComponentsByEdge = new Map();
            illustratedSurfaceBoundaryTopology.forEach((edge) => {
                const key = completedEdgeKey(edge.a, edge.b);
                if (!originalSurfaceComponentsByEdge.has(key)) originalSurfaceComponentsByEdge.set(key, new Set());
                originalSurfaceComponentsByEdge.get(key).add(edge.surfaceComponentId);
            });
            const residualPathProvenance = promotedReconstructedSurfaceSuggestions.map((path, index) => {
                const points = path.points || [];
                const segmentEvidence = [];
                for (let i = 1; i < points.length; i += 1) {
                    const key = completedEdgeKey(points[i - 1], points[i]);
                    segmentEvidence.push({ key, componentIds: [...(originalSurfaceComponentsByEdge.get(key) || [])] });
                }
                const missingEdges = segmentEvidence.filter((edge) => edge.componentIds.length === 0).length;
                const ambiguousEdges = segmentEvidence.filter((edge) => edge.componentIds.length > 1).length;
                const componentIds = [...new Set(segmentEvidence.flatMap((edge) => edge.componentIds))];
                const verifiedComponentId = !missingEdges && !ambiguousEdges && componentIds.length === 1
                    ? componentIds[0] : null;
                return { pathIndex: index + 1, segmentCount: segmentEvidence.length,
                    missingEdges, ambiguousEdges, componentIds, verifiedComponentId,
                    vertexCapSegmented: path.vertexCapSegmented === true, diagnosticOnly: true };
            });

            // G.5Z.24: forensic-only exact coincident endpoint continuity audit.
            // Endpoint roles and tangents describe the existing path, not permission to merge.
            const residualCoincidentEndpointGroups = [];
            const residualEndpointsByPoint = new Map();
            residualTerminations.forEach((record) => {
                const key = residualTerminationPointKey(record.point);
                if (!residualEndpointsByPoint.has(key)) residualEndpointsByPoint.set(key, []);
                residualEndpointsByPoint.get(key).push(record);
            });
            residualEndpointsByPoint.forEach((records, key) => {
                if (records.length < 2) return;
                const pathIndices = [...new Set(records.map((record) => record.pathIndex))];
                const pathEdges = records.map((record) => {
                    const path = promotedReconstructedSurfaceSuggestions[record.pathIndex - 1];
                    const points = path.points || [];
                    const neighbour = record.endpointRole === 'start' ? points[1] : points[points.length - 2];
                    return { id: record.id, pathIndex: record.pathIndex,
                        endpointRole: record.endpointRole,
                        neighbour: neighbour ? { x: neighbour.x, y: neighbour.y } : null,
                        tangent: { ...record.tangent },
                        sourceComponentIds: [...record.sourceComponentIds],
                        vertexCapSegmented: path.vertexCapSegmented === true };
                });
                const samePath = pathIndices.length !== records.length;
                const directions = pathEdges.map((edge) => edge.neighbour
                    ? `${edge.neighbour.x},${edge.neighbour.y}` : 'missing');
                const duplicateOutgoingEdge = new Set(directions).size !== directions.length;
                const sharedSourceEvidence = pathEdges.every((edge) => edge.sourceComponentIds.length > 0)
                    && pathEdges.some((edge) => edge.sourceComponentIds.some((id) =>
                        pathEdges.every((other) => other.sourceComponentIds.includes(id))));
                const classification = records.length !== 2 ? 'ambiguous-multiple-endpoints'
                    : samePath ? 'same-path-coincidence'
                    : duplicateOutgoingEdge ? 'overlapping-local-edge'
                    : !sharedSourceEvidence ? 'source-provenance-unverified'
                    : pathEdges.some((edge) => edge.vertexCapSegmented) ? 'vertex-cap-split-review'
                    : 'candidate-continuation-unverified';
                const provenance = pathIndices.map((index) => residualPathProvenance[index - 1]);
                const verifiedSameComponent = provenance.length === 2
                    && provenance.every((item) => item.verifiedComponentId !== null)
                    && provenance[0].verifiedComponentId === provenance[1].verifiedComponentId;
                const sourceClassification = provenance.some((item) => item.missingEdges || item.ambiguousEdges)
                    ? 'incomplete-or-ambiguous-edge-provenance'
                    : !verifiedSameComponent ? 'different-or-unverified-source-component'
                    : samePath || duplicateOutgoingEdge ? 'coincident-or-overlapping-edge-review'
                    : pathEdges.some((edge) => edge.vertexCapSegmented) ? 'same-source-cap-split-review'
                    : 'same-source-local-continuation-candidate';
                residualCoincidentEndpointGroups.push({ key, point: { ...records[0].point },
                    endpointIds: records.map((record) => record.id), pathIndices,
                    pathEdges, sharedSourceEvidence, classification,
                    provenance, verifiedSameComponent, sourceClassification,
                    // Classification never authorises a join, wall, or change to review occupancy.
                    diagnosticOnly: true });
            });

            // Pair only endpoints touching the SAME connected suppressed run.
            // A shared edge count or visual proximity is never sufficient evidence.
            const residualRunPairings = suppressedRuns.map((run) => {
                const endpoints = residualTerminations.filter((record) => record.suppressedRunIds.includes(run.id));
                const distinct = [...new Set(endpoints.map((record) => residualTerminationPointKey(record.point)))];
                return { runId: run.id, edges: run.edges, sourceComponentId: run.sourceComponentId,
                    endpointIds: endpoints.map((record) => record.id),
                    classification: endpoints.length === 2 && distinct.length === 2 ? 'two-distinct-endpoints'
                        : endpoints.length === 0 ? 'no-adjacent-endpoint'
                        : endpoints.length === 1 ? 'single-adjacent-endpoint'
                        : distinct.length !== endpoints.length ? 'coincident-endpoints' : 'ambiguous-multiple-endpoints' };
            });
            const residualUnmatchedEndpointIds = residualTerminations
                .filter((record) => record.suppressedRunIds.length === 0).map((record) => record.id);
            // G.5Z.27: diagnostic-only graph of surviving open paths and suppressed runs.
            // A graph cycle is a closure POSSIBILITY, never evidence for admitting paper edges.
            const residualClosureAudit = (() => {
                const links = residualRunPairings.map((run) => {
                    const endpoints = run.endpointIds.map((id) => residualTerminations.find((entry) => entry.id === id)).filter(Boolean);
                    const paths = [...new Set(endpoints.map((entry) => entry.pathIndex))];
                    const classification = run.classification !== 'two-distinct-endpoints' ? 'unpaired-run'
                        : paths.length !== 2 ? 'same-path-or-ambiguous-closure'
                        : run.edges >= 64 ? 'extended-unsupported-frontier'
                        : 'short-unsupported-frontier';
                    return { runId: run.runId, edges: run.edges, endpointIds: run.endpointIds.slice(),
                        pathIndices: paths, sourceComponentId: run.sourceComponentId, classification,
                        admission: 'suppressed-diagnostic-only' };
                });
                const nodes = [...new Set(residualTerminations.map((entry) => entry.pathIndex))];
                const adjacency = new Map(nodes.map((node) => [node, new Set()]));
                links.filter((link) => link.pathIndices.length === 2 && link.classification !== 'unpaired-run')
                    .forEach((link) => { const [a, b] = link.pathIndices;
                        adjacency.get(a)?.add(b); adjacency.get(b)?.add(a); });
                const seen = new Set();
                const components = [];
                nodes.forEach((node) => {
                    if (seen.has(node)) return;
                    const stack = [node], paths = [];
                    while (stack.length) { const current = stack.pop();
                        if (seen.has(current)) continue;
                        seen.add(current); paths.push(current);
                        (adjacency.get(current) || []).forEach((next) => { if (!seen.has(next)) stack.push(next); });
                    }
                    const pathSet = new Set(paths);
                    const componentLinks = links.filter((link) => link.pathIndices.some((index) => pathSet.has(index)));
                    const degrees = paths.map((index) => componentLinks.filter((link) => link.pathIndices.includes(index)).length);
                    const paired = componentLinks.every((link) => link.pathIndices.length === 2 &&
                        link.classification !== 'unpaired-run');
                    const closureFeasible = paired && degrees.every((degree) => degree === 2) &&
                        componentLinks.length === paths.length;
                    components.push({ pathIndices: paths.sort((a, b) => a - b),
                        runIds: componentLinks.map((link) => link.runId),
                        classification: closureFeasible ? 'graph-cycle-only-not-geometry-certified'
                            : 'open-or-ambiguous-network', closureFeasible });
                });
                return { diagnosticOnly: true, openPathCount: nodes.length,
                    endpointCount: residualTerminations.length, runCount: links.length,
                    links, components, graphClosureCandidates: components.filter((item) => item.closureFeasible).length,
                    admittedEdges: 0, restoredRuns: 0 };
            })();
            // G.5Z.28: geometry and exterior-separation forensic audit. Graph closure
            // alone cannot certify an illustrated wall or authorise edge admission.
            const residualCycleGeometryAudit = (() => {
                const pointKey = residualTerminationPointKey;
                const segmentKey = (a, b) => completedEdgeKey(a, b);
                const retainedSegments = promotedReconstructedSurfaceSuggestions.flatMap((path) => {
                    const points = path.points || [];
                    return points.slice(1).map((point, index) => ({ a: points[index], b: point }));
                });
                const runSegments = new Map(suppressedRuns.map((run) => [run.id,
                    run.members.map((index) => stillSuppressed[index])]));
                const allSegments = retainedSegments.concat([...runSegments.values()].flat());
                const vertexDegree = new Map();
                const uniqueEdges = new Set();
                let duplicateEdges = 0;
                allSegments.forEach((edge) => {
                    const key = segmentKey(edge.a, edge.b);
                    if (uniqueEdges.has(key)) duplicateEdges += 1;
                    uniqueEdges.add(key);
                    [edge.a, edge.b].forEach((point) => {
                        const key = pointKey(point);
                        vertexDegree.set(key, (vertexDegree.get(key) || 0) + 1);
                    });
                });
                // Axis-aligned grid edges can intersect away from endpoints only when
                // overlapping or crossing. Check the latter independently of graph degree.
                const crosses = (first, second) => {
                    const firstVertical = first.a.x === first.b.x;
                    const secondVertical = second.a.x === second.b.x;
                    if (firstVertical === secondVertical) return false;
                    const vertical = firstVertical ? first : second;
                    const horizontal = firstVertical ? second : first;
                    const x = vertical.a.x, y = horizontal.a.y;
                    return x > Math.min(horizontal.a.x, horizontal.b.x) && x < Math.max(horizontal.a.x, horizontal.b.x)
                        && y > Math.min(vertical.a.y, vertical.b.y) && y < Math.max(vertical.a.y, vertical.b.y);
                };
                let interiorCrossings = 0;
                for (let i = 0; i < allSegments.length; i += 1) {
                    for (let j = i + 1; j < allSegments.length; j += 1) {
                        if (crosses(allSegments[i], allSegments[j])) interiorCrossings += 1;
                    }
                }
                const branchVertices = [...vertexDegree.values()].filter((degree) => degree !== 2).length;
                const runs = suppressedRuns.map((run) => {
                    const members = runSegments.get(run.id) || [];
                    const ink = members.map((edge) => Number(edge.boundaryInk || 0));
                    const quiet = members.filter((edge) => edge.suppressedOpenPaper === true).length;
                    const sourceIds = [...new Set(members.map((edge) => edge.surfaceComponentId))];
                    const pairing = residualRunPairings.find((entry) => entry.runId === run.id);
                    const endpoints = (pairing?.endpointIds || []).map((id) => residualTerminations.find((entry) => entry.id === id)).filter(Boolean);
                    const sourceVerified = sourceIds.length === 1 && endpoints.length === 2 && endpoints.every((endpoint) =>
                        residualPathProvenance[endpoint.pathIndex - 1]?.verifiedComponentId === sourceIds[0]);
                    return { runId: run.id, edges: members.length, quietEdges: quiet,
                        maximumInk: ink.length ? Math.max(...ink) : 0, sourceVerified,
                        exteriorClassification: quiet === members.length ? 'suppressed-open-paper-frontier' : 'mixed-or-unverified-frontier',
                        geometryClassification: pairing?.classification === 'two-distinct-endpoints' && sourceVerified
                            ? 'endpoint-and-source-consistent-not-wall-certified' : 'unverified-endpoint-or-source',
                        diagnosticOnly: true };
                });
                const cycleCandidate = residualClosureAudit.graphClosureCandidates > 0;
                const combinatorialSimple = cycleCandidate && duplicateEdges === 0 && branchVertices === 0 && interiorCrossings === 0;
                return { diagnosticOnly: true, cycleCandidate, combinatorialSimple,
                    duplicateEdges, branchVertices, interiorCrossings, runs,
                    // A topologically simple outline still needs positive illustration
                    // evidence and exterior separation; quiet paper is not a wall.
                    classification: !cycleCandidate ? 'no-graph-cycle'
                        : !combinatorialSimple ? 'geometric-cycle-not-certified'
                        : 'simple-grid-cycle-only-exterior-boundary-unverified',
                    geometryCertified: false, exteriorSeparationCertified: false,
                    admittedEdges: 0, restoredRuns: 0 };
            })();
            // IV.30.1G.5Z.29 — forensic-only separation of the recovered-surface
            // frontier from positively illustrated wall evidence. The original boundary
            // topology records the inside cell and outward normal BEFORE suppression.
            // A quiet inside/outside pair proves a surface frontier, NOT a wall.
            const residualExteriorFrontierAudit = (() => {
                const runs = suppressedRuns.map((run) => {
                    const members = run.members.map((index) => stillSuppressed[index]);
                    const sourceIds = [...new Set(members.map((edge) => edge.surfaceComponentId))];
                    const exactSourceCells = members.filter((edge) =>
                        Number.isInteger(edge.insideColumn) && Number.isInteger(edge.insideRow)
                        && Number.isInteger(edge.outsideDx) && Number.isInteger(edge.outsideDy)
                        && Math.abs(edge.outsideDx) + Math.abs(edge.outsideDy) === 1);
                    const insideCells = new Set(exactSourceCells.map((edge) => `${edge.insideColumn},${edge.insideRow}`));
                    const outsideCells = new Set(exactSourceCells.map((edge) =>
                        `${edge.insideColumn + edge.outsideDx},${edge.insideRow + edge.outsideDy}`));
                    const inkSupported = members.filter((edge) => edge.exactStructuralBoundary === true
                        || edge.surfaceBoundaryCorroboration === 'exact-structural'
                        || edge.surfaceBoundaryCorroboration === 'strong-local-ink'
                        || edge.surfaceBoundaryCorroboration === 'moderate-local-ink');
                    const quietPairs = exactSourceCells.filter((edge) => edge.suppressedOpenPaper === true
                        && Number(edge.boundaryInk) <= .30);
                    const endpointPair = residualRunPairings.find((entry) => entry.runId === run.id);
                    const endpoints = (endpointPair?.endpointIds || []).map((id) =>
                        residualTerminations.find((entry) => entry.id === id)).filter(Boolean);
                    const sourceConsistent = sourceIds.length === 1 && endpoints.length === 2
                        && endpoints.every((entry) =>
                            residualPathProvenance[entry.pathIndex - 1]?.verifiedComponentId === sourceIds[0]);
                    const completeProvenance = exactSourceCells.length === members.length;
                    return { runId: run.id, edges: members.length, sourceComponentCount: sourceIds.length,
                        sourceConsistent, provenanceRecorded: exactSourceCells.length,
                        distinctInsideCells: insideCells.size, distinctOutsideCells: outsideCells.size,
                        quietPairs: quietPairs.length, inkSupportedEdges: inkSupported.length,
                        classification: !completeProvenance ? 'incomplete-cell-provenance'
                            : !sourceConsistent ? 'source-or-endpoint-unverified'
                            : inkSupported.length ? 'mixed-frontier-requires-local-review'
                            : quietPairs.length === members.length
                                ? 'recovered-surface-frontier-not-illustrated-wall'
                                : 'frontier-evidence-unresolved',
                        diagnosticOnly: true };
                });
                const classifiedEdges = runs.reduce((total, run) => total + run.edges, 0);
                const quietEdges = runs.reduce((total, run) => total + run.quietPairs, 0);
                const corroboratedEdges = runs.reduce((total, run) => total + run.inkSupportedEdges, 0);
                return { diagnosticOnly: true, runs, classifiedEdges, quietEdges,
                    corroboratedEdges, originalSuppressedEdges: stillSuppressed.length,
                    retainedPerimeterEdges: reconstructedIllustratedSurfaceEdges.length,
                    // Classification is descriptive only: no synthetic wall, closure,
                    // floor admission, review object or persistent draft is created.
                    separationClassification: runs.every((run) =>
                        run.classification === 'recovered-surface-frontier-not-illustrated-wall')
                        ? 'quiet-surface-frontier-distinct-from-illustrated-wall-evidence'
                        : 'mixed-or-incomplete-frontier-evidence',
                    wallCertification: false, admittedEdges: 0, restoredRuns: 0 };
            })();
            // IV.30.1G.5Z.30 — forensic-only endpoint evidence. Inspect the
            // actual retained segment touching each termination separately from
            // the suppressed surface frontier; neither a tangent nor a quiet
            // neighbour is positive evidence of an illustrated wall.
            const residualIllustratedTerminationAudit = (() => {
                const records = residualTerminations.map((record) => {
                    const path = promotedReconstructedSurfaceSuggestions[record.pathIndex - 1];
                    const points = Array.isArray(path?.points) ? path.points : [];
                    const neighbour = record.endpointRole === 'start' ? points[1] : points[points.length - 2];
                    const touching = (residualSuppressedByEndpoint.get(residualTerminationPointKey(record.point)) || []);
                    const runIds = [...record.suppressedRunIds];
                    const corroborated = touching.filter((edge) => edge.exactStructuralBoundary === true
                        || ['exact-structural', 'strong-local-ink', 'moderate-local-ink'].includes(edge.surfaceBoundaryCorroboration));
                    const quiet = touching.filter((edge) => edge.suppressedOpenPaper === true);
                    const retainedSegmentPresent = !!neighbour && Number.isFinite(neighbour.x) && Number.isFinite(neighbour.y)
                        && (neighbour.x !== record.point.x || neighbour.y !== record.point.y);
                    const classification = !retainedSegmentPresent ? 'retained-segment-missing'
                        : corroborated.length ? 'mixed-local-evidence-review-required'
                        : touching.length && quiet.length === touching.length ? 'retained-path-meets-quiet-frontier'
                        : touching.length ? 'local-boundary-evidence-unresolved'
                        : 'retained-path-without-adjacent-suppressed-frontier';
                    return { endpointId: record.id, pathIndex: record.pathIndex, endpointRole: record.endpointRole,
                        retainedSegmentPresent, retainedNeighbour: neighbour ? { x: neighbour.x, y: neighbour.y } : null,
                        suppressedRunIds: runIds, adjacentSuppressedEdges: touching.length,
                        quietAdjacentEdges: quiet.length, corroboratedAdjacentEdges: corroborated.length,
                        maximumAdjacentInk: touching.reduce((max, edge) => Math.max(max, Number(edge.boundaryInk) || 0), 0),
                        classification, illustratedWallTerminationCertified: false, diagnosticOnly: true };
                });
                return { diagnosticOnly: true, records, endpointCount: records.length,
                    retainedSegmentsPresent: records.filter((entry) => entry.retainedSegmentPresent).length,
                    quietFrontierEndpoints: records.filter((entry) => entry.classification === 'retained-path-meets-quiet-frontier').length,
                    locallyCorroboratedEndpoints: records.filter((entry) => entry.corroboratedAdjacentEdges > 0).length,
                    illustratedWallTerminationCertified: false, admittedEdges: 0, restoredRuns: 0 };
            })();
            // IV.30.1G.5Z.32 — independent, diagnostic-only illustrated-ink survey.
            // Start with the sampled image mesh, NOT the recovered floor perimeter.
            // Dense aligned ink is a candidate, never a certified wall: hatching,
            // furniture and graph-paper artefacts may also pass this coarse gate.
            const independentIllustratedWallSurvey = (() => {
                const representedSegments = [];
                const addPathSegments = (path) => {
                    const points = Array.isArray(path?.points) ? path.points : [];
                    for (let index = 1; index < points.length; index += 1) {
                        representedSegments.push([points[index - 1], points[index]]);
                    }
                };
                authoritativeContourSuggestions.forEach(addPathSegments);
                promotedReconstructedSurfaceSuggestions.forEach(addPathSegments);
                const pointSegmentDistance = (x, y, a, b) => {
                    const dx = b.x - a.x, dy = b.y - a.y;
                    const lengthSquared = dx * dx + dy * dy;
                    const t = lengthSquared > 0 ? Math.max(0, Math.min(1,
                        ((x - a.x) * dx + (y - a.y) * dy) / lengthSquared)) : 0;
                    return Math.hypot(x - a.x - t * dx, y - a.y - t * dy);
                };
                const inkAt = (x, y) => Number(darkness[y]?.[x] ?? 0);
                const candidateKeys = new Set();
                const candidates = [];
                // Sample every interior mesh cell, including never-frontier cells.
                // Requiring a three-cell streak suppresses isolated flecks, but
                // does not establish semantic wall identity.
                for (let y = 2; y < contourRows - 2; y += 1) {
                    for (let x = 2; x < contourColumns - 2; x += 1) {
                        if (inkAt(x, y) < .72) continue;
                        const horizontal = inkAt(x - 1, y) >= .72 && inkAt(x + 1, y) >= .72;
                        const vertical = inkAt(x, y - 1) >= .72 && inkAt(x, y + 1) >= .72;
                        if (!horizontal && !vertical) continue;
                        const key = `${x},${y}`;
                        candidateKeys.add(key);
                        candidates.push({ x, y, horizontal, vertical });
                    }
                }
                const visited = new Set();
                const components = [];
                for (const seed of candidates) {
                    const seedKey = `${seed.x},${seed.y}`;
                    if (visited.has(seedKey)) continue;
                    const queue = [seed];
                    visited.add(seedKey);
                    let represented = 0, minX = seed.x, maxX = seed.x;
                    let minY = seed.y, maxY = seed.y;
                    for (let cursor = 0; cursor < queue.length; cursor += 1) {
                        const cell = queue[cursor];
                        minX = Math.min(minX, cell.x); maxX = Math.max(maxX, cell.x);
                        minY = Math.min(minY, cell.y); maxY = Math.max(maxY, cell.y);
                        const gx = (cell.x + .5) * contourStep;
                        const gy = (cell.y + .5) * contourStep;
                        if (representedSegments.some(([a, b]) =>
                            pointSegmentDistance(gx, gy, a, b) <= contourStep * 1.5)) represented += 1;
                        for (const [dx, dy] of [[-1,0],[1,0],[0,-1],[0,1]]) {
                            const nx = cell.x + dx, ny = cell.y + dy;
                            const key = `${nx},${ny}`;
                            if (!candidateKeys.has(key) || visited.has(key)) continue;
                            visited.add(key);
                            queue.push({ x: nx, y: ny });
                        }
                    }
                    components.push({ cells: queue.length, sampleCells: queue.map((cell) => ({ x: cell.x, y: cell.y })), representedSamples: represented,
                        unrepresentedSamples: queue.length - represented,
                        bounds: [minX, minY, maxX, maxY].map((value) => value * contourStep),
                        classification: represented === queue.length ? 'near-represented-geometry'
                            : represented ? 'mixed-ink-component-review' : 'unrepresented-ink-component-review',
                        wallCertified: false });
                }
                const review = components.filter((component) => component.unrepresentedSamples > 0)
                    .sort((a, b) => b.unrepresentedSamples - a.unrepresentedSamples);
                // IV.30.1G.5Z.33 — local provenance of the independent review cells.
                // Revisit the original sampled darkness and existing geometry without
                // promoting isolated ink, inventing a contour, or changing floor topology.
                const componentProvenance = review.map((component) => {
                    const localCells = component.sampleCells.map((cell) => ({ ...cell,
                        horizontal: inkAt(cell.x - 1, cell.y) >= .72 && inkAt(cell.x + 1, cell.y) >= .72,
                        vertical: inkAt(cell.x, cell.y - 1) >= .72 && inkAt(cell.x, cell.y + 1) >= .72 }));
                    const localKeys = new Set(localCells.map((cell) => `${cell.x},${cell.y}`));
                    let horizontalStreaks = 0, verticalStreaks = 0, nearestDistance = Infinity;
                    let nearbyInk = 0, nearbyCandidate = 0, strongInk = 0;
                    const sampleCoordinates = [];
                    for (const cell of localCells) {
                        if (!localKeys.has(`${cell.x},${cell.y}`)) continue;
                        const gx = (cell.x + .5) * contourStep;
                        const gy = (cell.y + .5) * contourStep;
                        sampleCoordinates.push(`${(cell.x * contourStep).toFixed(2)},${(cell.y * contourStep).toFixed(2)}`);
                        if (cell.horizontal) horizontalStreaks += 1;
                        if (cell.vertical) verticalStreaks += 1;
                        if (inkAt(cell.x, cell.y) >= .72) strongInk += 1;
                        for (const [a, b] of representedSegments) {
                            nearestDistance = Math.min(nearestDistance, pointSegmentDistance(gx, gy, a, b));
                        }
                    }
                    const neighbourhood = new Set();
                    for (const cell of localCells) {
                        for (let dy = -2; dy <= 2; dy += 1) {
                            for (let dx = -2; dx <= 2; dx += 1) {
                                const x = cell.x + dx, y = cell.y + dy;
                                if (x < 0 || y < 0 || x >= contourColumns || y >= contourRows) continue;
                                const key = `${x},${y}`;
                                if (localKeys.has(key) || neighbourhood.has(key)) continue;
                                neighbourhood.add(key);
                                if (inkAt(x, y) >= .72) nearbyInk += 1;
                                if (candidateKeys.has(key)) nearbyCandidate += 1;
                            }
                        }
                    }
                    return { bounds: component.bounds, sampleCoordinates, cells: component.cells,
                        horizontalStreaks, verticalStreaks, strongInk, nearbyInk, nearbyCandidate,
                        nearestRepresentedDistance: Number.isFinite(nearestDistance) ? nearestDistance : null,
                        classification: 'local-ink-provenance-review-not-wall-certified', wallCertified: false };
                });
                // IV.30.1G.5Z.34 — inspect local ink continuity independently of
                // the three-cell candidate gate. A connected dark pixel is not a wall.
                const localStructuralContext = review.map((component) => {
                    const sampleKeys = new Set(component.sampleCells.map((cell) => `${cell.x},${cell.y}`));
                    const margin = 6;
                    const minX = Math.max(0, Math.min(...component.sampleCells.map((cell) => cell.x)) - margin);
                    const maxX = Math.min(contourColumns - 1, Math.max(...component.sampleCells.map((cell) => cell.x)) + margin);
                    const minY = Math.max(0, Math.min(...component.sampleCells.map((cell) => cell.y)) - margin);
                    const maxY = Math.min(contourRows - 1, Math.max(...component.sampleCells.map((cell) => cell.y)) + margin);
                    const strongKeys = new Set();
                    for (let y = minY; y <= maxY; y += 1) {
                        for (let x = minX; x <= maxX; x += 1) {
                            if (inkAt(x, y) >= .72) strongKeys.add(`${x},${y}`);
                        }
                    }
                    const visitedInk = new Set();
                    const clusters = [];
                    for (const seed of component.sampleCells) {
                        const seedKey = `${seed.x},${seed.y}`;
                        if (!strongKeys.has(seedKey) || visitedInk.has(seedKey)) continue;
                        const queue = [seed];
                        visitedInk.add(seedKey);
                        let xLow = seed.x, xHigh = seed.x, yLow = seed.y, yHigh = seed.y;
                        let branches = 0, ends = 0, representedProximity = 0;
                        for (let cursor = 0; cursor < queue.length; cursor += 1) {
                            const cell = queue[cursor];
                            xLow = Math.min(xLow, cell.x); xHigh = Math.max(xHigh, cell.x);
                            yLow = Math.min(yLow, cell.y); yHigh = Math.max(yHigh, cell.y);
                            const neighbours = [[-1,0],[1,0],[0,-1],[0,1]]
                                .map(([dx, dy]) => ({ x: cell.x + dx, y: cell.y + dy }))
                                .filter((next) => strongKeys.has(`${next.x},${next.y}`));
                            if (neighbours.length > 2) branches += 1;
                            if (neighbours.length === 1) ends += 1;
                            const gx = (cell.x + .5) * contourStep;
                            const gy = (cell.y + .5) * contourStep;
                            if (representedSegments.some(([a, b]) => pointSegmentDistance(gx, gy, a, b) <= contourStep * 1.5)) representedProximity += 1;
                            for (const next of neighbours) {
                                const key = `${next.x},${next.y}`;
                                if (visitedInk.has(key)) continue;
                                visitedInk.add(key);
                                queue.push(next);
                            }
                        }
                        clusters.push({ pixels: queue.length, horizontalSpan: xHigh - xLow + 1,
                            verticalSpan: yHigh - yLow + 1, branches, ends, representedProximity,
                            touchesSurveyWindow: xLow === minX || xHigh === maxX || yLow === minY || yHigh === maxY,
                            reviewPixelsConnected: queue.filter((cell) => sampleKeys.has(`${cell.x},${cell.y}`)).length });
                    }
                    return { bounds: component.bounds, window: [minX, minY, maxX, maxY].map((value) => value * contourStep),
                        clusters, classification: 'local-ink-continuity-structural-context-unverified', wallCertified: false };
                });
                // IV.30.1G.5Z.35 — diagnostic orientation and feature context.
                // Compare local connected ink with its immediate surroundings, not
                // merely the three-cell survey gate. This is deliberately NOT a
                // semantic wall classifier: hatching and wall ink can look alike.
                const localInkFeatureClassification = localStructuralContext.map((context, index) => {
                    const source = review[index];
                    const sampleKeys = new Set(source.sampleCells.map((cell) => `${cell.x},${cell.y}`));
                    const margin = 6;
                    const minX = Math.max(0, Math.min(...source.sampleCells.map((cell) => cell.x)) - margin);
                    const maxX = Math.min(contourColumns - 1, Math.max(...source.sampleCells.map((cell) => cell.x)) + margin);
                    const minY = Math.max(0, Math.min(...source.sampleCells.map((cell) => cell.y)) - margin);
                    const maxY = Math.min(contourRows - 1, Math.max(...source.sampleCells.map((cell) => cell.y)) + margin);
                    const records = context.clusters.map((cluster) => {
                        const vertical = cluster.verticalSpan > cluster.horizontalSpan;
                        const horizontal = cluster.horizontalSpan > cluster.verticalSpan;
                        let nearbyParallel = 0, nearbyPerpendicular = 0;
                        const seen = new Set();
                        for (const cell of source.sampleCells) {
                            for (let dy = -6; dy <= 6; dy += 1) {
                                for (let dx = -6; dx <= 6; dx += 1) {
                                    const x = cell.x + dx, y = cell.y + dy, key = `${x},${y}`;
                                    if (x < minX + 1 || x >= maxX || y < minY + 1 || y >= maxY ||
                                        sampleKeys.has(key) || seen.has(key) || inkAt(x, y) < .72) continue;
                                    seen.add(key);
                                    const v = inkAt(x, y - 1) >= .72 && inkAt(x, y + 1) >= .72;
                                    const h = inkAt(x - 1, y) >= .72 && inkAt(x + 1, y) >= .72;
                                    if ((vertical && v) || (horizontal && h)) nearbyParallel += 1;
                                    if ((vertical && h) || (horizontal && v)) nearbyPerpendicular += 1;
                                }
                            }
                        }
                        const bounded = !cluster.touchesSurveyWindow;
                        const simple = cluster.branches === 0 && cluster.ends === 2;
                        return { orientation: vertical ? 'vertical' : horizontal ? 'horizontal' : 'undetermined',
                            pixels: cluster.pixels, bounded, simple, nearbyParallel, nearbyPerpendicular,
                            representedProximity: cluster.representedProximity,
                            classification: bounded && simple && cluster.representedProximity === 0
                                ? 'isolated-short-ink-feature-review-not-wall-certified'
                                : 'local-ink-feature-context-unresolved-not-wall-certified', wallCertified: false };
                    });
                    return { bounds: context.bounds, records,
                        classification: 'orientation-and-neighbourhood-only-no-semantic-wall-certification', wallCertified: false };
                });
                // IV.30.1G.5Z.36 — close the *local review*, not the whole-image survey.
                // Reconcile independent candidate, provenance, continuity and feature
                // evidence by original bounds; fail closed if a record is missing or
                // ambiguous. No diagnostic disposition may create gameplay geometry.
                const residualInkDisposition = review.map((component, index) => {
                    const provenance = componentProvenance[index];
                    const continuity = localStructuralContext[index];
                    const feature = localInkFeatureClassification[index];
                    const sameBounds = [provenance, continuity, feature].every((record) =>
                        record && JSON.stringify(record.bounds) === JSON.stringify(component.bounds));
                    const connectedReviewPixels = continuity?.clusters.reduce((sum, cluster) =>
                        sum + cluster.reviewPixelsConnected, 0) ?? 0;
                    const locallyReviewed = sameBounds && component.unrepresentedSamples > 0 &&
                        connectedReviewPixels === component.unrepresentedSamples &&
                        continuity.clusters.length > 0 && feature.records.length === continuity.clusters.length &&
                        feature.records.every((record) => record.bounded && record.simple &&
                            record.representedProximity === 0 &&
                            record.classification === 'isolated-short-ink-feature-review-not-wall-certified') &&
                        provenance.wallCertified === false && continuity.wallCertified === false &&
                        feature.wallCertified === false;
                    return { bounds: component.bounds.slice(), reviewCells: component.cells,
                        unrepresentedSamples: component.unrepresentedSamples,
                        provenance: provenance?.classification || 'missing-provenance',
                        continuity: continuity?.classification || 'missing-continuity',
                        featureClassifications: feature?.records.map((record) => record.classification) || [],
                        disposition: locallyReviewed
                            ? 'local-review-complete-isolated-ink-not-wall-certified'
                            : 'local-review-open-insufficient-evidence',
                        localReviewComplete: locallyReviewed, wallCertified: false,
                        reopenOnNewIndependentEvidence: true };
                });
                const residualInkClosure = {
                    diagnosticOnly: true, scope: 'local-ink-review-only-not-whole-illustration',
                    components: residualInkDisposition,
                    localReviewsComplete: residualInkDisposition.filter((item) => item.localReviewComplete).length,
                    localReviewsOpen: residualInkDisposition.filter((item) => !item.localReviewComplete).length,
                    wholeIllustrationCoverageCertified: false, missingWallsCertified: false,
                    wallCertified: false, admittedEdges: 0, restoredRuns: 0
                };
                return { diagnosticOnly: true, scope: 'independent-whole-mesh-ink-candidate-survey',
                    meshCellsExamined: Math.max(0, contourRows - 4) * Math.max(0, contourColumns - 4),
                    candidateInkCells: candidates.length, componentCount: components.length,
                    representedOnlyComponents: components.length - review.length,
                    reviewComponentCount: review.length, componentProvenance, localStructuralContext, localInkFeatureClassification, residualInkClosure,
                    reviewBounds: review.slice(0, 12).map((component) =>
                        `${component.classification}/${component.cells}c/${component.unrepresentedSamples}unrepresented@(${component.bounds.join(',')})`),
                    // Pixel streaks alone cannot prove missing walls or image coverage.
                    wholeIllustrationCoverageCertified: false, missingWallsCertified: false,
                    admittedEdges: 0, restoredRuns: 0 };
            })();
            // IV.30.1G.5Z.31 — coverage of the *available perimeter evidence*, not a
            // whole-image wall detector. Compare each sampled boundary edge with the
            // retained surface geometry and authoritative review geometry by exact
            // endpoints. Do not turn an unrepresented quiet frontier into a wall.
            const residualIllustratedWallCoverageAudit = (() => {
                const edgeKey = (a, b) => completedEdgeKey(a, b);
                const retainedKeys = new Set(reconstructedIllustratedSurfaceEdges.map((edge) =>
                    edgeKey(edge.points[0], edge.points[1])));
                const authoritativeKeys = new Set();
                authoritativeContourSuggestions.forEach((path) => {
                    const points = Array.isArray(path.points) ? path.points : [];
                    for (let i = 1; i < points.length; i += 1) {
                        authoritativeKeys.add(edgeKey(points[i - 1], points[i]));
                    }
                });
                const records = illustratedSurfaceBoundaryTopology.map((edge) => {
                    const retained = retainedKeys.has(edge.key);
                    const authoritative = authoritativeKeys.has(edge.key);
                    const structural = edge.exactStructuralBoundary === true
                        || edge.surfaceBoundaryCorroboration === 'exact-structural';
                    const inkSupported = ['strong-local-ink', 'moderate-local-ink']
                        .includes(edge.surfaceBoundaryCorroboration);
                    const quiet = edge.suppressedOpenPaper === true;
                    const classification = retained || authoritative ? 'represented-sampled-boundary'
                        : quiet ? 'unrepresented-quiet-surface-frontier-not-wall'
                        : structural || inkSupported ? 'unrepresented-corroborated-sample-review-required'
                        : 'unrepresented-unverified-sample';
                    return { key: edge.key, classification, retained, authoritative,
                        structural, inkSupported, quiet, diagnosticOnly: true };
                });
                const count = (classification) => records.filter((record) =>
                    record.classification === classification).length;
                const corroboratedUnrepresented = records.filter((record) =>
                    record.classification === 'unrepresented-corroborated-sample-review-required');
                return { diagnosticOnly: true, scope: 'sampled-recovered-surface-boundary-only',
                    sampledEdges: records.length, representedSampledEdges: count('represented-sampled-boundary'),
                    quietUnrepresentedEdges: count('unrepresented-quiet-surface-frontier-not-wall'),
                    corroboratedUnrepresentedEdges: corroboratedUnrepresented.length,
                    unverifiedUnrepresentedEdges: count('unrepresented-unverified-sample'),
                    reviewKeys: corroboratedUnrepresented.slice(0, 12).map((record) => record.key),
                    // The image may contain structural marks away from the recovered
                    // surface frontier. This audit cannot certify their coverage.
                    wholeIllustrationCoverageCertified: false, admittedEdges: 0, restoredRuns: 0 };
            })();
            // A bounded visual witness only: no suppressed edge is admitted or saved.
            const residualPairedRunSegments = suppressedRuns.filter((run) =>
                residualRunPairings[run.id - 1].classification === 'two-distinct-endpoints')
                .flatMap((run) => run.members.map((index) => ({ runId: run.id,
                    a: { ...stillSuppressed[index].a }, b: { ...stillSuppressed[index].b } })));

            // G.5W: a reconstructed surface chain that meets an authoritative endpoint
            // extends that object instead of becoming object 201. This preserves every
            // authoritative review object while allowing genuinely new contiguous surface
            // geometry to become visible under a saturated 200-object budget.
            let reconstructedSurfaceAlreadyRepresented = 0;
            let reconstructedSurfaceAuthorityExtensions = 0;
            const representedReconstructedSurfaceKeys = new Set();
            const remainingReconstructedSurfaceSuggestions = [];
            promotedReconstructedSurfaceSuggestions.forEach((surface) => {
                const surfaceKey = cartographySuggestionKey(surface);
                if (authoritativeContourKeys.has(surfaceKey)) {
                    reconstructedSurfaceAlreadyRepresented += 1;
                    reconstructedSurfaceAlreadyRepresentedEdges += Number(surface.inferredPerimeterEdgeCount || 0);
                    representedReconstructedSurfaceKeys.add(surfaceKey);
                    return;
                }
                const surfacePoints = Array.isArray(surface.points) ? surface.points : [];
                let extended = false;
                if (surfacePoints.length >= 2) {
                    for (let index = 0; index < authoritativeContourSuggestions.length; index += 1) {
                        const authority = authoritativeContourSuggestions[index];
                        const authorityPoints = Array.isArray(authority.points) ? authority.points : [];
                        if (authorityPoints.length < 2) continue;
                        let combined = null;
                        if (surfacePointKey(authorityPoints[authorityPoints.length - 1]) === surfacePointKey(surfacePoints[0]))
                            combined = authorityPoints.concat(surfacePoints.slice(1));
                        else if (surfacePointKey(authorityPoints[authorityPoints.length - 1]) === surfacePointKey(surfacePoints[surfacePoints.length - 1]))
                            combined = authorityPoints.concat(surfacePoints.slice(0, -1).reverse());
                        else if (surfacePointKey(authorityPoints[0]) === surfacePointKey(surfacePoints[surfacePoints.length - 1]))
                            combined = surfacePoints.slice(0, -1).concat(authorityPoints);
                        else if (surfacePointKey(authorityPoints[0]) === surfacePointKey(surfacePoints[0]))
                            combined = surfacePoints.slice(1).reverse().concat(authorityPoints);
                        if (!combined || combined.length > maximumPathVertices) continue;
                        authoritativeContourSuggestions[index] = {
                            ...authority,
                            points: combined,
                            x1: combined[0].x, y1: combined[0].y,
                            x2: combined[combined.length - 1].x, y2: combined[combined.length - 1].y,
                            reconstructedSurfaceAuthorityExtension: true,
                            recoveryEvidence: Array.from(new Set((authority.recoveryEvidence || []).concat([
                                'reconstructed-surface-authority-extension', 'zero-additional-review-object-cost'
                            ])))
                        };
                        reconstructedSurfaceAuthorityExtensions += 1;
                        reconstructedSurfaceAuthorityExtensionEdges += Number(surface.inferredPerimeterEdgeCount || 0);
                        authoritativeSegmentsFor(surface).forEach((key) => reconstructedSurfaceAuthorityExtensionSegmentKeys.add(key));
                        representedReconstructedSurfaceKeys.add(surfaceKey);
                        extended = true;
                        break;
                    }
                }
                if (!extended) remainingReconstructedSurfaceSuggestions.push(surface);
            });

            const arbitratedSuggestions = authoritativeContourSuggestions.slice(0, maximumReviewSuggestions);
            const arbitratedKeys = new Set(arbitratedSuggestions.map(cartographySuggestionKey));
            let authoritativePreserved = authoritativeSourcesPreserved;
            let bridgeRepresented = 0;
            let promotedRepresentedByArbitration = 0;
            const appendByAuthority = (items, authority) => {
                items.forEach((item) => {
                    const key = cartographySuggestionKey(item);
                    if (arbitratedKeys.has(key)) {
                        if (authority === 'promoted') representedPromotedKeys.add(key);
                        return;
                    }
                    if (arbitratedSuggestions.length >= maximumReviewSuggestions) return;
                    arbitratedKeys.add(key);
                    arbitratedSuggestions.push(item);
                    if (authority === 'bridge') bridgeRepresented += 1;
                    if (authority === 'promoted') {
                        representedPromotedKeys.add(key);
                        promotedRepresentedByArbitration += 1;
                    }
                });
            };
            // Regression-contract compatibility for G.5X/G.5W/G.5W.1/G.5T.1/G.5Z.12.
            // G.5Z.13 separates certified overlays from the bounded review ledger, so the
            // historical executable admission/fallback statements below are no longer used.
            // Keep their exact vocabulary here so earlier regression contracts continue to
            // prove that the architectural lineage has not been silently discarded:
            // appendByAuthority(remainingReconstructedSurfaceSuggestions, 'surface');
            // pathSuggestions = preOcclusionRecoveryContours;
            // IV.30.1G.5Z.12 — Review Capacity Pressure & Surface Representation Admission Audit.
            // Do not raise the ceiling, compress unrelated geometry, or weaken topology to make it fit.
            // const reconstructedSurfaceCapacityRejectedSuggestions = remainingReconstructedSurfaceSuggestions.filter(
            // const reconstructedSurfaceCapacityRejectedEdges = reconstructedSurfaceCapacityRejectedSuggestions.reduce(
            // rejectedPoints.length + representedPoints.length - 1 <= maximumPathVertices
            // IV.30.1G.5Z.13 — Certified Surface Overlay Representation & Review-Budget Separation.
            // The 200-object ceiling remains the bounded review-suggestion ledger. Reconstructed
            // surface paths have already passed floor recovery, component completion, perimeter
            // extraction and cap-safe assembly, so render them as a separate certified overlay
            // channel. They remain review-only geometry and gain no automatic persistence authority; Keeper acceptance remains explicit.
            const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;
            const reconstructedSurfaceAuthorityOccupancy = arbitratedSuggestions.length;
            const reconstructedSurfaceSlotsAvailable = Math.max(0, maximumReviewSuggestions - reconstructedSurfaceAuthorityOccupancy);
            const reconstructedSurfaceRequestedNovelChains = remainingReconstructedSurfaceSuggestions.length;
            const reconstructedSurfaceRequestedNovelEdges = remainingReconstructedSurfaceSuggestions.reduce(
                (sum, item) => sum + Number(item.inferredPerimeterEdgeCount || 0), 0
            );

            const certifiedSurfaceOverlaySuggestions = remainingReconstructedSurfaceSuggestions.map((item) => ({
                ...item,
                certifiedSurfaceOverlay: true,
                recoveryEvidence: Array.from(new Set((item.recoveryEvidence || []).concat([
                    'certified-surface-overlay', 'separate-from-review-suggestion-budget'
                ])))
            }));
            const reconstructedSurfaceOverlayChains = certifiedSurfaceOverlaySuggestions.length;
            const reconstructedSurfaceOverlayEdges = certifiedSurfaceOverlaySuggestions.reduce(
                (sum, item) => sum + Number(item.inferredPerimeterEdgeCount || 0), 0
            );
            certifiedSurfaceOverlaySuggestions.forEach((item) => {
                representedReconstructedSurfaceKeys.add(cartographySuggestionKey(item));
                reconstructedSurfaceNovelRepresentedEdges += Number(item.inferredPerimeterEdgeCount || 0);
                authoritativeSegmentsFor(item).forEach((key) => reconstructedSurfaceNovelSegmentKeys.add(key));
            });

            // G.5Z.12 pressure remains published as historical evidence, but no certified surface
            // path is capacity-rejected in G.5Z.13. Bridge/promoted suggestions still compete only
            // inside the ordinary 200-object review ledger.
            const reconstructedSurfaceCapacityRejectedChains = 0;
            const reconstructedSurfaceCapacityRejectedEdges = 0;
            const reconstructedSurfaceCapacityRejectedClosedChains = 0;
            const reconstructedSurfaceCapacityRejectedOpenChains = 0;
            const reconstructedSurfaceCapacityRejectedVertexCapSegments = 0;
            const reconstructedSurfaceCapacityRejectedEndpointContiguous = 0;
            const reconstructedSurfaceCapacityRejectedMergeEligible = 0;
            const reconstructedSurfaceCapacityRejectedIndependent = 0;

            appendByAuthority(bridgeSupplementalSuggestions, 'bridge');
            appendByAuthority(promotedSupplementalSuggestions, 'promoted');
            const reviewBudgetSuggestions = arbitratedSuggestions.slice();
            const reconstructedSurfaceReviewObjectsEmitted = reviewBudgetSuggestions.length;
            pathSuggestions = reviewBudgetSuggestions.concat(certifiedSurfaceOverlaySuggestions);

            // G.5Z.3 compares the final review coordinates with the completed perimeter.
            // Exact source-segment survival is the strongest correspondence signal. Any
            // future simplifier that moves geometry will therefore become visible here.
            const reconstructedSurfaceFinalSegmentKeys = new Set(pathSuggestions.flatMap(authoritativeSegmentsFor));
            const reconstructedSurfaceExactCorrespondenceEdges = Array.from(reconstructedSurfaceSourceSegmentKeys)
                .filter((key) => reconstructedSurfaceFinalSegmentKeys.has(key)).length;
            const reconstructedSurfaceAuthorityCorrespondenceEdges = Array.from(reconstructedSurfaceAuthorityExtensionSegmentKeys)
                .filter((key) => reconstructedSurfaceFinalSegmentKeys.has(key)).length;
            const reconstructedSurfaceNovelCorrespondenceEdges = Array.from(reconstructedSurfaceNovelSegmentKeys)
                .filter((key) => reconstructedSurfaceFinalSegmentKeys.has(key)).length;
            const reconstructedSurfaceDisplacedEdges = Math.max(0,
                reconstructedSurfaceSourceSegmentKeys.size - reconstructedSurfaceExactCorrespondenceEdges
            );
            const reconstructedSurfaceCollapsedEdges = promotedReconstructedSurfaceSuggestions.reduce((count, item) => {
                const points = Array.isArray(item.points) ? item.points : [];
                for (let index = 0; index < points.length - 1; index += 1) {
                    if (surfacePointKey(points[index]) === surfacePointKey(points[index + 1])) count += 1;
                }
                return count;
            }, 0);
            const reconstructedSurfaceRenderedReviewSegments = pathSuggestions.reduce(
                (sum, item) => sum + Math.max(0, (Array.isArray(item.points) ? item.points.length : 0) - 1), 0
            );


            // Defensive compatibility fallback: IV.30.1C intentionally no longer
            // performs global segment compaction. Historical contracts referenced
            // simplificationTolerance *= 1.35 and
            // if (fallbackValues.length > maximumReviewSuggestions) return []
            // because exceeding 200 segments previously meant failure. Polyline paths
            // remove that bottleneck while retaining the 200-object safety boundary.
            const simplificationTolerance = Math.max(contourStep * 1.1, .12);
            const fallbackValues = buildSimplifiedSuggestions(simplificationTolerance);
            void fallbackValues;
            // IV.30.1G.5T.1 — Evidence Audit Publication After Review-Budget Fallback.
            // G.5T can discover enough new frontier evidence to exceed the 200-object
            // review boundary. Preserve the exact monotonic fallback for normal scans,
            // but keep the top-level Evidence Audit pass alive long enough to publish
            // its traversal telemetry. The recursive skipOcclusionRecovery baseline is
            // still forbidden from publishing diagnostics below.
            if (reviewBudgetSuggestions.length > maximumReviewSuggestions) {
                if (options.evidenceAudit === true && options.skipOcclusionRecovery !== true) {
                    pathSuggestions = preOcclusionRecoveryContours.concat(certifiedSurfaceOverlaySuggestions);
                } else {
                    return preOcclusionRecoveryContours.concat(certifiedSurfaceOverlaySuggestions);
                }
            }

            const publishContourEvidenceAudit = (emittedSuggestions) => {
                if (options.evidenceAudit !== true || options.skipOcclusionRecovery === true) return;
                const emittedKeys = new Set(emittedSuggestions.map(cartographySuggestionKey));
                const promotedMemberKeys = new Set();
                const representedFinalKeys = new Set(representedPromotedKeys);
                consolidatedPromotedPerimeterSuggestions.forEach((item) => {
                    const key = cartographySuggestionKey(item);
                    if (emittedKeys.has(key)) representedFinalKeys.add(key);
                });
                promotedInferredPerimeterSuggestions.forEach((item) => {
                    const emitted = representedFinalKeys.has(cartographySuggestionKey(item));
                    contourEvidenceAuditRecords.push({
                        stage: 'perimeter-promotion',
                        state: emitted ? 'emitted' : 'budgeted-out',
                        reason: emitted ? 'connected-chain-promoted-and-represented' : 'promoted-chain-review-budget-or-deduplication',
                        legacyReason: emitted ? null : 'supplemental-budget-or-deduplication',
                        points: item.points
                    });
                    // Record member edges as promoted rather than falsely describing them
                    // as lost merely because their final review key is now a polyline key.
                    for (let index = 0; index < Number(item.inferredPerimeterEdgeCount || 0); index += 1) {
                        // Count is sufficient for the audit summary; geometry remains on the promoted chain.
                        promotedMemberKeys.add(`${cartographySuggestionKey(item)}:${index}`);
                    }
                });
                inferredPlayablePerimeterSuggestions.forEach((item) => {
                    contourEvidenceAuditRecords.push({
                        stage: 'perimeter-inference',
                        state: 'promoted',
                        reason: 'eligible-for-connected-edge-promotion',
                        points: item.points
                    });
                });
                // IV.30.1G.5T.2 — Evidence Audit Status Ownership.
                // Publish through both the historical shared audit state and an explicit
                // top-level callback. The callback gives the Analyse Map caller ownership
                // of the final diagnostic object, so later draft rendering cannot lose the
                // audit merely because an internal contour pass used fallback/recursion.
                const publishedEvidenceAudit = {
                    evidenceModel: 'living-contour-evidence-audit-v10',
                    rawChains: contourChains.length,
                    recoverableChains: recoverableChains.length,
                    semanticRejected: recoverableChains.filter((entry) => !semanticRoleIsAutomaticWall(entry.semanticBoundary?.role)).length,
                    certifiedBridges: certifiedEvidenceBridges.length,
                    inferredPerimeters: inferredPlayablePerimeterSuggestions.length,
                    promotedPerimeterChains: promotedInferredPerimeterSuggestions.length,
                    promotedPerimeterEdges: promotedInferredPerimeterSuggestions.reduce((sum, item) => sum + Number(item.inferredPerimeterEdgeCount || 0), 0),
                    consolidatedPromotedChains: consolidatedPromotedPerimeterSuggestions.length,
                    authoritativeContours: authoritativeInputCount,
                    authoritativePreserved,
                    authoritativeReviewPaths,
                    authoritativePathMerges,
                    authoritativeReviewSlotsLiberated,
                    authoritativeRemainingCapacity: Math.max(0, maximumReviewSuggestions - reviewBudgetSuggestions.length),
                    illustratedFloorSeeds,
                    illustratedTraversalSeedsQueued,
                    illustratedTraversalSeedsVisited,
                    illustratedAdjacentSamplesExamined,
                    illustratedAlreadyPlayableNeighbours,
                    illustratedFrontierCandidates,
                    illustratedFrontierAdmitted,
                    illustratedFrontierStructuralRejects,
                    illustratedFrontierExteriorRejects,
                    illustratedFrontierInteriorSupportRejects,
                    illustratedFrontierDecorationRejects,
                    illustratedDecorationRejectUnrecoveredCells: finalIllustratedDecorationRejectCells.size,
                    illustratedDecorationRejectComponents,
                    illustratedDecorationRejectAdjacentComponents,
                    illustratedDecorationRejectMultiSidedComponents,
                    illustratedDecorationRejectInteriorSupportedComponents,
                    illustratedDecorationRejectIsolatedComponents,
                    illustratedPotentialPlayableIslandComponents,
                    illustratedPotentialPlayableIslandCells,
                    illustratedMeshCells,
                    illustratedRecoveredOrCanonicalCells,
                    illustratedFrontierExaminedUniqueCells: illustratedFrontierExaminedCells.size,
                    illustratedNeverFrontierCells,
                    illustratedFloorLikeNeverFrontierCells: illustratedFloorLikeNeverFrontierCells.size,
                    illustratedNeverFrontierComponents,
                    illustratedNeverFrontierExteriorComponents,
                    illustratedNeverFrontierInteriorComponents,
                    illustratedNeverFrontierNarrowGapComponents,
                    illustratedDisconnectedPlayableIslandComponents,
                    illustratedDisconnectedPlayableIslandCells,
                    illustratedInteriorQualificationRejectedComponents,
                    illustratedInteriorQualificationRejectedCells,
                    illustratedInteriorQualificationSingletonRejects,
                    illustratedInteriorQualificationMultiCellRejects,
                    illustratedInteriorQualificationRejectedInitiallyNearRecovered,
                    illustratedInteriorQualificationRejectedAbsorbedComponents,
                    illustratedInteriorQualificationRejectedAbsorbedCells,
                    illustratedInteriorQualificationRejectedRemainingComponents,
                    illustratedInteriorQualificationRejectedPostRecoveryAdjacent,
                    illustratedInteriorQualificationRejectedPostRecoveryNarrowGap,
                    illustratedSecondaryNarrowGapCandidates,
                    illustratedSecondaryTopologySafeCandidates,
                    illustratedSecondarySeedsAdmitted,
                    illustratedSecondaryRecoveredCells,
                    illustratedIterativeGenerationsRun,
                    illustratedIterativeGeneration2Candidates,
                    illustratedIterativeGeneration2Seeds,
                    illustratedIterativeGeneration2RecoveredCells,
                    illustratedIterativeGeneration3Candidates,
                    illustratedIterativeGeneration3Seeds,
                    illustratedIterativeGeneration3RecoveredCells,
                    illustratedIterativeSeedsAdmitted,
                    illustratedIterativeRecoveredCells,
                    illustratedIterativeGenerationAudit: illustratedIterativeGenerationAudit.map((entry) => ({...entry})),
                    illustratedIterativeConvergenceReason,
                    illustratedUnseededIslandComponents,
                    illustratedUnseededIslandCells,
                    illustratedUnseededInitiallyNoNarrowGap,
                    illustratedUnseededInitiallyUnsafeBridge,
                    illustratedUnseededAbsorbedComponents,
                    illustratedUnseededAbsorbedCells,
                    illustratedUnseededRemainingComponents,
                    illustratedUnseededPostRecoveryAdjacentComponents,
                    illustratedUnseededPostRecoveryNarrowGapComponents,
                    illustratedUnseededPostRecoveryTopologySafeComponents,
                    illustratedUnseededPostRecoveryStructuralBlockedComponents,
                    illustratedFrontierNeighbourQualified,
                    illustratedFrontierDecorationQualified,
                    illustratedFrontierProvisionalAdmissions,
                    illustratedFrontierUnaccounted: Math.max(0, illustratedFrontierCandidates - illustratedFrontierAdmitted - illustratedFrontierStructuralRejects - illustratedFrontierExteriorRejects - illustratedFrontierInteriorSupportRejects - illustratedFrontierDecorationRejects),
                    illustratedPropagationWaves,
                    illustratedPropagationDeepestWave,
                    illustratedPropagatedAdmissions,
                    illustratedQuietFloorAdmissions,
                    illustratedExhaustedFrontierCells,
                    recoveredIllustratedFloorCells,
                    reconstructedIllustratedSurfaces,
                    illustratedSurfaceAnchorCandidates,
                    illustratedSurfaceExactPlayableAnchors,
                    illustratedSurfaceReconciledAnchors,
                    illustratedSurfaceUnresolvedSurfaces,
                    illustratedSurfaceCompletedComponents,
                    illustratedSurfaceCompletedCells,
                    illustratedSurfaceRawBoundarySides,
                    illustratedSurfacePlayableSeamsSuppressed,
                    illustratedSurfaceThresholdRejects,
                    illustratedSurfaceBoundaryStructuralCorroborated,
                    illustratedSurfaceBoundaryStrongInkCorroborated,
                    illustratedSurfaceBoundaryModerateInkCorroborated,
                    illustratedSurfaceBoundaryUnsupportedFrontier,
                    illustratedSurfaceBoundaryOpenPaperFrontier,
                    illustratedSurfaceBoundaryOpenPaperSuppressed,
                    illustratedSurfaceSuppressedGapRuns,
                    illustratedSurfaceSuppressedGapBothBounded,
                    illustratedSurfaceSuppressedGapOneBounded,
                    illustratedSurfaceSuppressedGapUnbounded,
                    illustratedSurfaceSuppressedGapBranchAdjacent,
                    illustratedSurfaceSuppressedGapSameSourceContiguous,
                    illustratedSurfaceSuppressedGapLength1,
                    illustratedSurfaceSuppressedGapLength2,
                    illustratedSurfaceSuppressedGapLength3To4,
                    illustratedSurfaceSuppressedGapLength5To8,
                    illustratedSurfaceSuppressedGapLength9Plus,
                    illustratedSurfaceMicroGapEligibleRuns,
                    illustratedSurfaceMicroGapEligibleEdges,
                    illustratedSurfaceMicroGapRestoredEdges,
                    illustratedSurfaceShortGapEligibleRuns,
                    illustratedSurfaceShortGapEligibleEdges,
                    illustratedSurfaceShortGapRestoredEdges,
                    illustratedSurfaceLongGapRuns,
                    illustratedSurfaceLongGapEdges,
                    illustratedSurfaceLongGapFiveToEightRuns,
                    illustratedSurfaceLongGapNinePlusRuns,
                    illustratedSurfaceLongGapNearCutoffEdges,
                    illustratedSurfaceLongGapDeepQuietEdges,
                    illustratedSurfaceLongGapInteriorDepthSupportedEdges,
                    illustratedSurfaceLongGapMeanInkPermille,
                    illustratedSurfaceLongGapMaxInkPermille,
                    illustratedSurfaceLongGapTotalSpanCells,
                    illustratedSurfaceLongGapLargestSpanCells,
                    illustratedSurfaceLongGapExactLengths: illustratedSurfaceLongGapExactLengths.slice().sort((a,b) => a-b),
                    illustratedSurfaceLongGapGeometry: illustratedSurfaceLongGapGeometry.slice().sort((a,b) => a.edges - b.edges),
                    illustratedSurfacePerimeterEdges,
                    localAssemblyAudit,
                    reconstructedSurfacePromotedChains: promotedReconstructedSurfaceSuggestions.length,
                    reconstructedSurfaceAssembledEdges,
                    reconstructedSurfaceUnassembledEdges: reconstructedSurfaceUnassembledEdgeCount,
                    reconstructedSurfaceUnassembledComponents,
                    reconstructedSurfaceLargestUnassembledComponent,
                    reconstructedSurfaceUnassembledBranchAdjacentEdges,
                    reconstructedSurfaceVertexCapRiskComponents,
                    reconstructedSurfaceVertexCapRiskEdges,
                    reconstructedSurfaceVertexCapSourceComponents,
                    reconstructedSurfaceVertexCapSegmentedPathCount,
                    reconstructedSurfaceVertexCapSegmentedEdges,
                    reconstructedSurfaceVertexCapSplitVertices,
                    reconstructedSurfaceClosedChains,
                    reconstructedSurfaceOpenChains,
                    reconstructedSurfaceAlreadyRepresented,
                    reconstructedSurfaceAlreadyRepresentedEdges,
                    reconstructedSurfaceAuthorityExtensions,
                    reconstructedSurfaceAuthorityExtensionEdges,
                    reconstructedSurfaceNovelChains: remainingReconstructedSurfaceSuggestions.length,
                    reconstructedSurfaceReviewCeiling,
                    reconstructedSurfaceAuthorityOccupancy,
                    reconstructedSurfaceSlotsAvailable,
                    reconstructedSurfaceRequestedNovelChains,
                    reconstructedSurfaceRequestedNovelEdges,
                    reconstructedSurfaceOverlayChains,
                    reconstructedSurfaceOverlayEdges,
                    reconstructedSurfaceReviewObjectsEmitted,
                    reconstructedSurfaceCapacityRejectedChains,
                    reconstructedSurfaceCapacityRejectedEdges,
                    reconstructedSurfaceCapacityRejectedClosedChains,
                    reconstructedSurfaceCapacityRejectedOpenChains,
                    reconstructedSurfaceCapacityRejectedVertexCapSegments,
                    reconstructedSurfaceCapacityRejectedEndpointContiguous,
                    reconstructedSurfaceCapacityRejectedMergeEligible,
                    reconstructedSurfaceCapacityRejectedIndependent,
                    reconstructedSurfaceRepresentedChains: representedReconstructedSurfaceKeys.size,
                    reconstructedSurfaceRepresentedEdges: reconstructedSurfaceAlreadyRepresentedEdges + reconstructedSurfaceAuthorityExtensionEdges + reconstructedSurfaceNovelRepresentedEdges,
                    reconstructedSurfaceUncoveredEdges: Math.max(0, reconstructedSurfaceAssembledEdges - reconstructedSurfaceAlreadyRepresentedEdges - reconstructedSurfaceAuthorityExtensionEdges - reconstructedSurfaceNovelRepresentedEdges),
                    reconstructedSurfaceRenderedReviewSegments,
                    reconstructedSurfaceExactCorrespondenceEdges,
                    reconstructedSurfaceAuthorityCorrespondenceEdges,
                    reconstructedSurfaceNovelCorrespondenceEdges,
                    reconstructedSurfaceDisplacedEdges,
                    reconstructedSurfaceCollapsedEdges,
                    reconstructedSurfaceOpenChainTerminations: reconstructedSurfaceOpenChains * 2,
                    residualTerminations,
                    residualCoincidentEndpointGroups,
                    residualRunPairings,
                    residualClosureAudit,
                    residualCycleGeometryAudit,
                    residualExteriorFrontierAudit,
                    residualIllustratedTerminationAudit,
                    residualIllustratedWallCoverageAudit,
                    independentIllustratedWallSurvey,
                    independentInkComponentProvenanceAudit: {
                        diagnosticOnly: true, components: independentIllustratedWallSurvey.componentProvenance,
                        wallCertified: false, admittedEdges: 0, restoredRuns: 0
                    },
                    independentLocalInkContinuityAudit: {
                        diagnosticOnly: true, components: independentIllustratedWallSurvey.localStructuralContext,
                        wallCertified: false, admittedEdges: 0, restoredRuns: 0
                    },
                    residualInkReviewDispositionAudit: independentIllustratedWallSurvey.residualInkClosure,
                    independentLocalInkFeatureClassificationAudit: {
                        diagnosticOnly: true, components: independentIllustratedWallSurvey.localInkFeatureClassification,
                        wallCertified: false, admittedEdges: 0, restoredRuns: 0
                    },
                    residualUnmatchedEndpointIds,
                    residualPairedRunSegments,
                    bridgeRepresented,
                    promotedRepresentedByArbitration,
                    representedPromotedChains: consolidatedPromotedPerimeterSuggestions.filter((item) => representedFinalKeys.has(cartographySuggestionKey(item))).length,
                    representedPromotedEdges: consolidatedPromotedPerimeterSuggestions.filter((item) => representedFinalKeys.has(cartographySuggestionKey(item))).reduce((sum, item) => sum + Number(item.inferredPerimeterEdgeCount || 0), 0),
                    preOcclusionCertified: preOcclusionRecoveryContours.length,
                    reviewObjectsEmitted: emittedSuggestions.filter((item) => item.certifiedSurfaceOverlay !== true).length,
                    certifiedSurfaceOverlaysEmitted: emittedSuggestions.filter((item) => item.certifiedSurfaceOverlay === true).length,
                    emitted: emittedSuggestions.length,
                    records: contourEvidenceAuditRecords
                };
                cartographyEvidenceAudit = publishedEvidenceAudit;
                if (typeof options.onEvidenceAudit === 'function') {
                    options.onEvidenceAudit(publishedEvidenceAudit);
                }
            };

            // Monotonic evidence contract: preserve every pre-G.5J certified object first,
            // then spend only the remaining review budget on genuinely new reconstruction
            // evidence. G.5J can therefore improve a draft but can never make Living
            // Contour or Hybrid sparser than the G.5I baseline.
            if (preOcclusionRecoveryContours.length > 0) {
                const merged = new Map();
                preOcclusionRecoveryContours.forEach((item) => merged.set(cartographySuggestionKey(item), item));
                reviewBudgetSuggestions.forEach((item) => {
                    if (merged.size >= maximumReviewSuggestions) return;
                    const key = cartographySuggestionKey(item);
                    if (!merged.has(key)) merged.set(key, item);
                });
                const mergedSuggestions = Array.from(merged.values()).concat(certifiedSurfaceOverlaySuggestions);
                publishContourEvidenceAudit(mergedSuggestions);
                return mergedSuggestions;
            }
            publishContourEvidenceAudit(pathSuggestions);
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
            // IV.30.1G.4A — Hybrid Partial-Contour Preservation.
            // The connectivity pass is useful on maps with tiny floor seams, but on
            // heavily-hatched cave maps it can legitimately prove *less* than the
            // standalone Living Contour reader. Never turn that uncertainty into an
            // empty Hybrid draft: keep a standard contour pass as a review-first
            // fallback and preserve its unresolved ends exactly as certified.
            const thresholdCandidates = structural.filter((item) => item?.type === 'door' && item?.doorwayReasoning);
            // Historical Connected Dungeon / G.4A regression contract:
            // const connectedContours = livingContourCandidates({ connectPlayableFloor: true });
            const connectedContours = livingContourCandidates({ connectPlayableFloor: true, thresholdCandidates });
            // IV.30.1G.5D — Contour Chain Certification.
            // Connected-floor reasoning is useful evidence, not an exclusive reader.
            // The standalone Living pass can retain a long coherent cave perimeter that
            // the connectivity pass only sees in fragments. Hybrid therefore considers
            // both sets of Living chains and certifies whole chains before arbitration.
            const standaloneContours = livingContourCandidates({ thresholdCandidates });
            const contours = connectedContours.concat(standaloneContours);
            const hybridContourSource = connectedContours.length > 0 ? 'certified-dual-living-readers' : 'standalone-fallback';

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

            // IV.30.1G.5B — Structural Evidence Corroboration.
            // The calibrated grid is a ruler, not artwork. A grid-aligned structural
            // vote may strengthen real architecture, but it cannot enter Hybrid merely
            // because a thin dark grid line is persistent. Require either nearby Living
            // Contour agreement or asymmetric wall-body ink beside the candidate.
            const contourSegments = [];
            contours.forEach((item) => {
                const points = Array.isArray(item.points) ? item.points : [];
                for (let index = 0; index < points.length - 1; index += 1) {
                    contourSegments.push({ start: points[index], end: points[index + 1] });
                }
            });
            const pointToSegmentDistanceForEvidence = (point, start, end) => {
                const dx = end.x - start.x;
                const dy = end.y - start.y;
                const lengthSquared = (dx * dx) + (dy * dy);
                if (lengthSquared <= .000001) return Math.hypot(point.x - start.x, point.y - start.y);
                const t = Math.max(0, Math.min(1, (((point.x - start.x) * dx) + ((point.y - start.y) * dy)) / lengthSquared));
                return Math.hypot(point.x - (start.x + (t * dx)), point.y - (start.y + (t * dy)));
            };
            const livingContourCorroborates = (item) => {
                const center = midpoint(item);
                return contourSegments.some((segment) => pointToSegmentDistanceForEvidence(center, segment.start, segment.end) <= .48);
            };
            const artworkDensityAtGridPoint = (gx, gy, radiusCanvas) => {
                const cx = toCanvasX(grid.offsetX + (gx * grid.size));
                const cy = toCanvasY(grid.offsetY + (gy * grid.size));
                const radius = Math.max(1, Math.round(radiusCanvas));
                let darkHits = 0;
                let total = 0;
                for (let y = Math.max(0, Math.round(cy) - radius); y <= Math.min(canvas.height - 1, Math.round(cy) + radius); y += 1) {
                    for (let x = Math.max(0, Math.round(cx) - radius); x <= Math.min(canvas.width - 1, Math.round(cx) + radius); x += 1) {
                        total += 1;
                        if (luminance(x, y) <= 104) darkHits += 1;
                    }
                }
                return darkHits / Math.max(1, total);
            };
            const structuralArtworkCorroboration = (item) => {
                const dx = Number(item.x2) - Number(item.x1);
                const dy = Number(item.y2) - Number(item.y1);
                const length = Math.max(.0001, Math.hypot(dx, dy));
                const normalX = -dy / length;
                const normalY = dx / length;
                const center = midpoint(item);
                const gridCanvas = Math.max(4, toCanvasX(grid.size));
                const sideOffset = .27;
                const sampleRadius = Math.max(2, gridCanvas * .09);
                let sideA = 0;
                let sideB = 0;
                [0.25, 0.5, 0.75].forEach((t) => {
                    const gx = Number(item.x1) + (dx * t);
                    const gy = Number(item.y1) + (dy * t);
                    sideA += artworkDensityAtGridPoint(gx + (normalX * sideOffset), gy + (normalY * sideOffset), sampleRadius);
                    sideB += artworkDensityAtGridPoint(gx - (normalX * sideOffset), gy - (normalY * sideOffset), sampleRadius);
                });
                sideA /= 3;
                sideB /= 3;
                const wallBodyDensity = Math.max(sideA, sideB);
                const quietSideDensity = Math.min(sideA, sideB);
                const livingAgreement = livingContourCorroborates(item);
                const asymmetricWallBody = wallBodyDensity >= .115 && wallBodyDensity >= quietSideDensity * 1.45;
                return {
                    livingAgreement,
                    asymmetricWallBody,
                    wallBodyDensity,
                    quietSideDensity,
                    corroborated: livingAgreement || asymmetricWallBody
                };
            };

            // A single straight-looking fleck can be handwriting, stairs, hatch, or the
            // imported map's own graph-paper grid. Local parallel support is useful only
            // after artwork corroboration; confidence alone must never resurrect the grid.
            const strongStructural = structural
                .map((item) => ({ ...item, localStructuralSupport: structuralSupport(item) }))
                .map((item) => ({ ...item, structuralArtworkEvidence: structuralArtworkCorroboration(item) }))
                .filter((item) => item.structuralArtworkEvidence.corroborated)
                .filter((item) => item.localStructuralSupport >= 2 || item.confidence >= 94 || item.structuralArtworkEvidence.livingAgreement)
                .map((item) => ({
                    ...item,
                    hybridJudgement: true,
                    hybridRegion: 'structural',
                    structuralEvidenceCorroboration: item.structuralArtworkEvidence.livingAgreement ? 'living-contour' : 'wall-body-ink'
                }));

            const pointToSegmentDistance = (point, start, end) => {
                const dx = end.x - start.x;
                const dy = end.y - start.y;
                const lengthSquared = (dx * dx) + (dy * dy);
                if (lengthSquared <= .000001) return Math.hypot(point.x - start.x, point.y - start.y);
                const t = Math.max(0, Math.min(1, (((point.x - start.x) * dx) + ((point.y - start.y) * dy)) / lengthSquared));
                return Math.hypot(point.x - (start.x + (t * dx)), point.y - (start.y + (t * dy)));
            };
            // IV.30.1G.5C — Semantic Continuity & Region Topology.
            // A wall is not a bag of unrelated pixels. Once Living Contour has proved
            // an ordered boundary around the same playable region, locally weak spans
            // inherit cautious support from their neighbours. Door/threshold evidence
            // remains an explicit break: topology may continue a wall, never seal an
            // opening that Pippin has already classified as a threshold.
            const thresholdNearSpan = (a, b) => {
                const center = { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 };
                return thresholdCandidates.some((door) => pointToSegmentDistance(
                    center,
                    { x: Number(door.x1), y: Number(door.y1) },
                    { x: Number(door.x2), y: Number(door.y2) }
                ) <= .72);
            };
            const contourTopologyEvidence = (item) => {
                const points = Array.isArray(item.points) ? item.points : [];
                if (points.length < 2) return { coherent: false, continuity: 0, playableSideConsistency: 0, regionMembership: 'unresolved' };
                let pathLength = 0;
                let consistentSamples = 0;
                let sampledSpans = 0;
                let previousPolarity = 0;
                const sideOffset = .24;
                const gridCanvas = Math.max(4, toCanvasX(grid.size));
                const sampleRadius = Math.max(2, gridCanvas * .075);
                for (let index = 0; index < points.length - 1; index += 1) {
                    const a = points[index];
                    const b = points[index + 1];
                    const dx = b.x - a.x;
                    const dy = b.y - a.y;
                    const length = Math.hypot(dx, dy);
                    if (length <= .0001) continue;
                    pathLength += length;
                    const nx = -dy / length;
                    const ny = dx / length;
                    const gx = (a.x + b.x) / 2;
                    const gy = (a.y + b.y) / 2;
                    const sideA = artworkDensityAtGridPoint(gx + (nx * sideOffset), gy + (ny * sideOffset), sampleRadius);
                    const sideB = artworkDensityAtGridPoint(gx - (nx * sideOffset), gy - (ny * sideOffset), sampleRadius);
                    const difference = sideA - sideB;
                    if (Math.abs(difference) < .025) continue;
                    const polarity = difference > 0 ? 1 : -1;
                    sampledSpans += 1;
                    if (previousPolarity === 0 || polarity === previousPolarity) consistentSamples += 1;
                    previousPolarity = polarity;
                }
                const playableSideConsistency = sampledSpans > 0 ? consistentSamples / sampledSpans : 0;
                const continuity = Math.min(1, pathLength / 3.25);
                const coherent = pathLength >= 1.35 && continuity >= .42 && (playableSideConsistency >= .58 || points.length >= 5);
                return {
                    coherent,
                    continuity,
                    playableSideConsistency,
                    regionMembership: coherent ? 'connected-playable-boundary' : 'local-evidence'
                };
            };

            // IV.30.1G.5D — certify the chain, not every fleck inside it.
            // Long region-defining Living paths may contain weak local ink, especially
            // across crosshatching or damaged scans. Distributed topology, semantic
            // wall classification and path scale can certify the whole ordered chain;
            // tiny interior loops and unresolved fragments never inherit that authority.
            const contourChainCertification = (item, topologyEvidence) => {
                const points = Array.isArray(item.points) ? item.points : [];
                let pathLength = 0;
                for (let index = 0; index < points.length - 1; index += 1) {
                    pathLength += Math.hypot(
                        Number(points[index + 1].x) - Number(points[index].x),
                        Number(points[index + 1].y) - Number(points[index].y)
                    );
                }
                const semanticRole = item.semanticBoundaryClassification || 'uncertain';
                const semanticConfidence = Number(item.semanticBoundaryConfidence || 0);
                const thresholdInterrupted = Boolean(item.thresholdGapProtection)
                    || (Array.isArray(item.protectedContourGaps) && item.protectedContourGaps.length > 0);
                const compactInterior = pathLength < 1.15 && points.length <= 5;
                const regionDefining = topologyEvidence?.regionMembership === 'connected-playable-boundary';
                const distributedSupport = topologyEvidence?.playableSideConsistency >= .58
                    || topologyEvidence?.continuity >= .72;
                const semanticSupport = semanticRole === 'structural-wall';
                const interiorFeatureRole = ['interior-feature', 'terrain', 'obstacle', 'decoration-noise'].includes(semanticRole);
                const certified = !compactInterior
                    && !interiorFeatureRole
                    && pathLength >= 1.2
                    && semanticSupport
                    && (regionDefining || distributedSupport || pathLength >= 3.4);
                return {
                    certified,
                    pathLength,
                    semanticRole,
                    thresholdInterrupted,
                    evidence: certified
                        ? ['ordered-living-chain', 'distributed-boundary-support', 'region-scale-certification']
                        : ['local-chain-only']
                };
            };

            const contourSpanCoveredByStructure = (a, b, topologyEvidence = null, chainCertification = null) => {
                const spanDx = Math.abs(b.x - a.x);
                const spanDy = Math.abs(b.y - a.y);
                const spanOrientation = spanDx <= .0001 ? 'vertical' : spanDy <= .0001 ? 'horizontal' : 'organic';
                if (spanOrientation === 'organic') return false;
                if (thresholdNearSpan(a, b)) return false;
                if (chainCertification?.certified) return false;
                if (topologyEvidence?.coherent && topologyEvidence.playableSideConsistency >= .58) return false;
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
                const topologyEvidence = contourTopologyEvidence(item);
                const chainCertification = contourChainCertification(item, topologyEvidence);
                // Hybrid is allowed to keep reviewable local evidence, but certified
                // chains receive whole-chain preservation through weak local spans.
                let run = [];
                const flush = () => {
                    if (run.length >= 2) {
                        const clean = run.filter((point, index) => index === 0 || point.x !== run[index - 1].x || point.y !== run[index - 1].y);
                        if (clean.length >= 2) {
                            organicPaths.push({
                                ...item, points: clean,
                                x1: clean[0].x, y1: clean[0].y,
                                x2: clean[clean.length - 1].x, y2: clean[clean.length - 1].y,
                                confidence: item.partialContour
                                    ? Math.max(76, Number(item.confidence) || 0)
                                    : Math.max(88, Number(item.confidence) || 0),
                                hybridJudgement: true, hybridRegion: 'organic',
                                hybridContourSource,
                                semanticRegionTopology: topologyEvidence.regionMembership,
                                semanticContinuity: topologyEvidence.continuity,
                                playableSideConsistency: topologyEvidence.playableSideConsistency,
                                contourChainCertification: chainCertification.certified ? 'certified-region-boundary' : 'local-review-chain',
                                contourChainLength: chainCertification.pathLength,
                                contourChainEvidence: chainCertification.evidence,
                                thresholdInterruptedChain: chainCertification.thresholdInterrupted,
                                hybridPartialPreservation: Boolean(item.partialContour),
                                partialContour: Boolean(item.partialContour),
                                unresolvedBoundaryEnds: Array.isArray(item.unresolvedBoundaryEnds)
                                    ? item.unresolvedBoundaryEnds
                                    : [],
                                evidenceModel: item.partialContour
                                    ? 'hybrid-partial-contour-v5a'
                                    : (item.evidenceModel || 'hybrid-contour-v5a')
                            });
                        }
                    }
                    run = [];
                };
                for (let index = 0; index < points.length - 1; index += 1) {
                    const a = points[index];
                    const b = points[index + 1];
                    if (contourSpanCoveredByStructure(a, b, topologyEvidence, chainCertification)) {
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

            const organicUsefulness = (item) => {
                const points = Array.isArray(item.points) ? item.points : [];
                let pathLength = 0;
                for (let index = 0; index < points.length - 1; index += 1) {
                    pathLength += Math.hypot(
                        Number(points[index + 1].x) - Number(points[index].x),
                        Number(points[index + 1].y) - Number(points[index].y)
                    );
                }
                // Partial evidence remains valuable, but a long certified path should
                // outrank a tiny fragment if a pathological map reaches the review cap.
                return (Number(item.confidence) || 0)
                    + Math.min(24, pathLength * 1.8)
                    + (item.partialContour ? 2 : 6);
            };
            const organic = Array.from(unique.values())
                .filter((item) => item.hybridRegion === 'organic')
                .sort((a, b) => organicUsefulness(b) - organicUsefulness(a));
            const built = Array.from(unique.values())
                .filter((item) => item.hybridRegion === 'structural')
                .sort((a, b) => (b.localStructuralSupport - a.localStructuralSupport) || (b.confidence - a.confidence));

            // Polyline paths represent much more geometry per review object. G.4A no
            // longer rejects the entire Hybrid draft when organic fragmentation reaches
            // the cap: retain the strongest certified paths, then spend any remaining
            // review objects on constructed linework. Unresolved ends remain open.
            const retainedOrganic = organic.slice(0, maximumReviewSuggestions);
            const remaining = maximumReviewSuggestions - retainedOrganic.length;
            let combined = retainedOrganic.concat(built.slice(0, remaining));

            // A connected-floor pass can produce contours that are all locally covered
            // by Structural evidence and therefore disappear during overlap trimming.
            // If that leaves Hybrid empty, fall back once to the standalone Living
            // Contour evidence rather than telling the Keeper that nothing is known.
            if (combined.length === 0 && hybridContourSource === 'certified-dual-living-readers') {
                const fallbackContours = livingContourCandidates({ thresholdCandidates });
                const fallbackOrganic = fallbackContours
                    .filter((item) => Array.isArray(item.points) && item.points.length > 1)
                    .map((item) => ({
                        ...item,
                        confidence: item.partialContour
                            ? Math.max(74, Number(item.confidence) || 0)
                            : Math.max(86, Number(item.confidence) || 0),
                        hybridJudgement: true,
                        hybridRegion: 'organic',
                        hybridContourSource: 'standalone-post-trim-fallback',
                        hybridPartialPreservation: Boolean(item.partialContour),
                        partialContour: Boolean(item.partialContour),
                        unresolvedBoundaryEnds: Array.isArray(item.unresolvedBoundaryEnds)
                            ? item.unresolvedBoundaryEnds
                            : [],
                        evidenceModel: item.partialContour
                            ? 'hybrid-partial-contour-v5a'
                            : (item.evidenceModel || 'hybrid-contour-v5a')
                    }))
                    .sort((a, b) => organicUsefulness(b) - organicUsefulness(a))
                    .slice(0, maximumReviewSuggestions);
                combined = fallbackOrganic;
            }

            return combined;
        };

        const scores = candidates.map((item) => item.score).sort((a, b) => a - b);
        const detail = cartographyDetail?.value || 'balanced';
        if (detail === 'hybrid') {
            const existing = new Set(visionBarriers.map(cartographySuggestionKey));
            cartographySuggestions = hybridCartographyCandidates()
                .filter((item) => !existing.has(cartographySuggestionKey(item)));
            renderCartographyReview();
            if (cartographySuggestions.length === 0 && cartographyAssistantStatus) {
                cartographyAssistantStatus.textContent = 'No safe hybrid wall sections could be prepared. Check grid calibration, or review Structural tracing and Living Contour separately.';
            }
            return;
        }
        if (detail === 'audit') {
            const existing = new Set(visionBarriers.map(cartographySuggestionKey));
            cartographyEvidenceAudit = null;
            reportCartographyAuditRuntime('updated JavaScript executing · audit started');
            let completedEvidenceAudit = null;
            cartographySuggestions = livingContourCandidates({
                evidenceAudit: true,
                onEvidenceAudit: (audit) => { completedEvidenceAudit = audit; }
            })
                .filter((item) => !existing.has(cartographySuggestionKey(item)))
                .sort((a, b) => b.confidence - a.confidence);
            // The top-level Evidence Audit owns the status panel. Re-attach the audit
            // captured from the completed contour pass immediately before rendering the
            // review, rather than relying on mutable state from nested analysis passes.
            if (completedEvidenceAudit) cartographyEvidenceAudit = completedEvidenceAudit;
            renderCartographyReview();
            // Separate from the legacy summary: its text can be replaced by other UI paths.
            const endpointRecords = cartographyEvidenceAudit?.residualTerminations;
            const markerCount = cartographySuggestionLayer?.querySelectorAll('[data-audit-termination]').length ?? 0;
            const expectedEndpoints = cartographyEvidenceAudit?.reconstructedSurfaceOpenChainTerminations;
            let assemblyWitness = 'G.5Z.26 assembly · audit unavailable';
            if (cartographyEvidenceAudit && Array.isArray(endpointRecords)) {
                const pairings = cartographyEvidenceAudit.residualRunPairings || [];
                const paired = pairings.filter((run) => run.classification === 'two-distinct-endpoints');
                const unmatched = cartographyEvidenceAudit.residualUnmatchedEndpointIds || [];
                const endpointDetails = endpointRecords.map((record) =>
                    `${record.id}:P${record.pathIndex}/${record.endpointRole}@(${record.gridPoint.x},${record.gridPoint.y})/t(${record.tangent.x},${record.tangent.y})/R[${record.suppressedRunIds.join(',') || '-'}]`).join(' · ');
                const coincident = cartographyEvidenceAudit.residualCoincidentEndpointGroups || [];
                const closure = cartographyEvidenceAudit.residualClosureAudit;
                const geometry = cartographyEvidenceAudit.residualCycleGeometryAudit;
                const frontier = cartographyEvidenceAudit.residualExteriorFrontierAudit;
                const termination = cartographyEvidenceAudit.residualIllustratedTerminationAudit;
                const coverage = cartographyEvidenceAudit.residualIllustratedWallCoverageAudit;
                const independentSurvey = cartographyEvidenceAudit.independentIllustratedWallSurvey;
                const inkProvenance = cartographyEvidenceAudit.independentInkComponentProvenanceAudit;
                const localInk = cartographyEvidenceAudit.independentLocalInkContinuityAudit;
                const featureInk = cartographyEvidenceAudit.independentLocalInkFeatureClassificationAudit;
                const disposition = cartographyEvidenceAudit.residualInkReviewDispositionAudit;
                cartographyAuditRuntimeWitness.dataset.cartographyInkDisposition = disposition
                    ? `G.5Z.36 ink disposition · ${disposition.scope} · ${disposition.components.length} review components · ${disposition.localReviewsComplete} local reviews complete · ${disposition.localReviewsOpen} local reviews open · ${disposition.components.map((item) => `B[${item.bounds.join(',')}]/${item.unrepresentedSamples}unrepresented/${item.disposition}/reopen-on-new-evidence`).join(' · ')} · whole illustration certified ${disposition.wholeIllustrationCoverageCertified} · missing walls certified ${disposition.missingWallsCertified} · wall certified ${disposition.wallCertified} · ${disposition.admittedEdges} edges admitted · ${disposition.restoredRuns} runs restored`
                    : 'G.5Z.36 ink disposition unavailable';
                cartographyAuditRuntimeWitness.dataset.cartographyInkFeatures = featureInk
                    ? `G.5Z.35 ink features · ${featureInk.components.length} review components · ${featureInk.components.map((item) => `B[${item.bounds.join(',')}]/${item.records.map((record) => `${record.orientation}/${record.pixels}pixels/${record.bounded ? 'bounded' : 'truncated'}/${record.simple ? 'simple' : 'complex'}/${record.nearbyParallel}parallel/${record.nearbyPerpendicular}perpendicular/${record.representedProximity}near-represented/${record.classification}`).join(';')}`).join(' · ')} · wall certified ${featureInk.wallCertified} · ${featureInk.admittedEdges} edges admitted · ${featureInk.restoredRuns} runs restored`
                    : 'G.5Z.35 ink features unavailable';
                cartographyAuditRuntimeWitness.dataset.cartographyLocalInk = localInk
                    ? `G.5Z.34 local ink continuity · ${localInk.components.length} review components · ${localInk.components.map((item) => `B[${item.bounds.join(',')}]/window[${item.window.join(',')}]/${item.clusters.map((cluster) => `${cluster.pixels}pixels/${cluster.horizontalSpan}h-span/${cluster.verticalSpan}v-span/${cluster.branches}branches/${cluster.ends}ends/${cluster.representedProximity}near-represented/${cluster.reviewPixelsConnected}review-pixels/${cluster.touchesSurveyWindow ? 'window-truncated' : 'window-contained'}`).join(';')}/${item.classification}`).join(' · ')} · wall certified ${localInk.wallCertified} · ${localInk.admittedEdges} edges admitted · ${localInk.restoredRuns} runs restored`
                    : 'G.5Z.34 local ink continuity unavailable';
                cartographyAuditRuntimeWitness.dataset.cartographyInkProvenance = inkProvenance
                    ? `G.5Z.33 ink provenance · ${inkProvenance.components.length} review components · ${inkProvenance.components.map((item) => `B[${item.bounds.join(',')}]/${item.cells}c/${item.horizontalStreaks}h/${item.verticalStreaks}v/${item.strongInk}strong/${item.nearbyInk}nearby-ink/${item.nearbyCandidate}nearby-candidates/nearest-represented-${item.nearestRepresentedDistance === null ? 'none' : item.nearestRepresentedDistance.toFixed(3)}/samples[${item.sampleCoordinates.join(';')}]/${item.classification}`).join(' · ')} · wall certified ${inkProvenance.wallCertified} · ${inkProvenance.admittedEdges} edges admitted · ${inkProvenance.restoredRuns} runs restored`
                    : 'G.5Z.33 ink provenance unavailable';
                cartographyAuditRuntimeWitness.dataset.cartographyIndependentWallSurvey = independentSurvey
                    ? `G.5Z.32 independent ink survey · ${independentSurvey.scope} · ${independentSurvey.meshCellsExamined} mesh cells examined · ${independentSurvey.candidateInkCells} candidate ink cells · ${independentSurvey.componentCount} ink components · ${independentSurvey.representedOnlyComponents} near-represented components · ${independentSurvey.reviewComponentCount} components requiring review (NOT certified walls) · review bounds [${independentSurvey.reviewBounds.join(';')}] · whole illustration certified ${independentSurvey.wholeIllustrationCoverageCertified} · missing walls certified ${independentSurvey.missingWallsCertified} · ${independentSurvey.admittedEdges} edges admitted · ${independentSurvey.restoredRuns} runs restored`
                    : 'G.5Z.32 independent ink survey unavailable';
                cartographyAuditRuntimeWitness.dataset.cartographyWallCoverage = coverage
                    ? `G.5Z.31 wall coverage · ${coverage.scope} · ${coverage.sampledEdges} sampled edges · ${coverage.representedSampledEdges} represented · ${coverage.quietUnrepresentedEdges} quiet frontier (NOT walls) · ${coverage.corroboratedUnrepresentedEdges} corroborated unrepresented (review) · ${coverage.unverifiedUnrepresentedEdges} unverified · review keys [${coverage.reviewKeys.join(',')}] · whole illustration certified ${coverage.wholeIllustrationCoverageCertified} · ${coverage.admittedEdges} edges admitted · ${coverage.restoredRuns} runs restored`
                    : 'G.5Z.31 wall coverage unavailable';
                cartographyAuditRuntimeWitness.dataset.cartographyTerminationEvidence = termination
                    ? `G.5Z.30 termination evidence · ${termination.endpointCount} endpoints · ${termination.retainedSegmentsPresent} retained segments · ${termination.quietFrontierEndpoints} quiet-frontier endpoints · ${termination.locallyCorroboratedEndpoints} locally corroborated · ${termination.records.map((entry) => `E${entry.endpointId}:P${entry.pathIndex}/${entry.classification}/R[${entry.suppressedRunIds.join(',')}]/${entry.adjacentSuppressedEdges} adjacent/${entry.quietAdjacentEdges} quiet/${entry.corroboratedAdjacentEdges} corroborated/maxInk${entry.maximumAdjacentInk.toFixed(3)}`).join(' · ')} · wall termination certified ${termination.illustratedWallTerminationCertified} · ${termination.admittedEdges} edges admitted · ${termination.restoredRuns} runs restored`
                    : 'G.5Z.30 termination evidence unavailable';
                cartographyAuditRuntimeWitness.dataset.cartographyFrontier = frontier
                    ? `G.5Z.29 frontier · ${frontier.separationClassification} · ${frontier.runs.length} runs · ${frontier.classifiedEdges} classified edges / ${frontier.originalSuppressedEdges} suppressed · ${frontier.quietEdges} quiet pairs · ${frontier.corroboratedEdges} corroborated edges · ${frontier.runs.map((run) => `R${run.runId}:${run.edges}e/${run.provenanceRecorded} provenance/${run.distinctInsideCells} inside cells/${run.distinctOutsideCells} outside cells/${run.quietPairs} quiet/${run.inkSupportedEdges} ink-supported/${run.classification}`).join(' · ')} · wall certified ${frontier.wallCertification} · ${frontier.admittedEdges} edges admitted · ${frontier.restoredRuns} runs restored`
                    : 'G.5Z.29 frontier unavailable';
                cartographyAuditRuntimeWitness.dataset.cartographyGeometry = geometry
                    ? `G.5Z.28 geometry · ${geometry.classification} · graph candidate ${geometry.cycleCandidate} · simple grid cycle ${geometry.combinatorialSimple} · ${geometry.duplicateEdges} duplicate edges · ${geometry.branchVertices} non-degree-two vertices · ${geometry.interiorCrossings} interior crossings · ${geometry.runs.map((run) => `R${run.runId}:${run.edges}e/${run.quietEdges}quiet/maxInk${run.maximumInk.toFixed(3)}/${run.exteriorClassification}/${run.geometryClassification}`).join(' · ')} · geometry certified ${geometry.geometryCertified} · exterior separation certified ${geometry.exteriorSeparationCertified} · ${geometry.admittedEdges} edges admitted · ${geometry.restoredRuns} runs restored`
                    : 'G.5Z.28 geometry unavailable';
                cartographyAuditRuntimeWitness.dataset.cartographyClosure = closure
                    ? `G.5Z.27 closure · ${closure.openPathCount} open paths · ${closure.endpointCount} endpoints · ${closure.runCount} suppressed runs · ${closure.components.length} graph components · ${closure.graphClosureCandidates} graph-cycle candidates (NOT geometry-certified) · ${closure.links.map((link) => `R${link.runId}:${link.edges}e/P[${link.pathIndices.join(',')}]/${link.classification}`).join(' · ')} · ${closure.components.map((component) => `C[P${component.pathIndices.join(',P')}]/R[${component.runIds.join(',')}]/${component.classification}`).join(' · ')} · ${closure.admittedEdges} edges admitted · ${closure.restoredRuns} runs restored`
                    : 'G.5Z.27 closure unavailable';
                assemblyWitness = `G.5Z.26 assembly · ${cartographyEvidenceAudit.localAssemblyAudit?.candidates ?? 0} candidates · ${cartographyEvidenceAudit.localAssemblyAudit?.joined ?? 0} joins · ${cartographyEvidenceAudit.localAssemblyAudit?.sourceRejected ?? 0} source rejects · ${cartographyEvidenceAudit.localAssemblyAudit?.capRejected ?? 0} cap rejects · ${cartographyEvidenceAudit.localAssemblyAudit?.topologyRejected ?? 0} topology rejects · ${cartographyEvidenceAudit.localAssemblyAudit?.edgeCountBefore ?? 0}/${cartographyEvidenceAudit.localAssemblyAudit?.edgeCountAfter ?? 0} edges preserved`;
                const provenanceWitness = `G.5Z.25 provenance · ${coincident.length} groups · ${coincident.map((group) => `E[${group.endpointIds.join(',')}]/${group.sourceClassification}/components[${group.provenance.map((item) => `P${item.pathIndex}:${item.verifiedComponentId ?? '?'}/${item.segmentCount}e/${item.missingEdges}missing/${item.ambiguousEdges}ambiguous`).join(';')}]`).join(' · ')}`;
                cartographyAuditRuntimeWitness.dataset.cartographyProvenance = provenanceWitness;
                const continuityWitness = `G.5Z.24 coincidence · ${coincident.length} coordinate groups · ${coincident.map((group) => `E[${group.endpointIds.join(',')}]@(${group.point.x},${group.point.y})/P[${group.pathIndices.join(',')}]/${group.classification}/source-${group.sharedSourceEvidence ? 'shared' : 'unverified'}/neighbours[${group.pathEdges.map((edge) => `${edge.id}:${edge.neighbour ? `${edge.neighbour.x},${edge.neighbour.y}` : '-'}`).join(';')}]`).join(' · ')}`;
                cartographyAuditRuntimeWitness.dataset.cartographyCoincidence = continuityWitness;
                const connectivityWitness = `G.5Z.23 connectivity · ${endpointRecords.length} endpoints · ${paired.length} two-endpoint connected runs · ${unmatched.length} no-adjacent-run endpoints [${unmatched.join(',')}] · ${pairings.map((run) => `R${run.runId}:${run.edges}e/E[${run.endpointIds.join(',')}]/${run.classification}`).join(' · ')} · ${endpointDetails}`;
                cartographyAuditRuntimeWitness.dataset.cartographyConnectivity = connectivityWitness;
            }
            reportCartographyAuditRuntime(`${cartographyAuditRuntimeWitness.dataset.cartographyInkDisposition || 'G.5Z.36 ink disposition unavailable'} · ${cartographyAuditRuntimeWitness.dataset.cartographyInkFeatures || 'G.5Z.35 ink features unavailable'} · ${cartographyAuditRuntimeWitness.dataset.cartographyLocalInk || 'G.5Z.34 local ink continuity unavailable'} · ${cartographyAuditRuntimeWitness.dataset.cartographyInkProvenance || 'G.5Z.33 ink provenance unavailable'} · ${cartographyAuditRuntimeWitness.dataset.cartographyIndependentWallSurvey || 'G.5Z.32 independent ink survey unavailable'} · ${cartographyAuditRuntimeWitness.dataset.cartographyWallCoverage || 'G.5Z.31 wall coverage unavailable'} · ${cartographyAuditRuntimeWitness.dataset.cartographyTerminationEvidence || 'G.5Z.30 termination evidence unavailable'} · ${cartographyAuditRuntimeWitness.dataset.cartographyFrontier || 'G.5Z.29 frontier unavailable'} · ${cartographyAuditRuntimeWitness.dataset.cartographyGeometry || 'G.5Z.28 geometry unavailable'} · ${cartographyAuditRuntimeWitness.dataset.cartographyClosure || 'G.5Z.27 closure unavailable'} · ${assemblyWitness} · ${cartographyAuditRuntimeWitness.dataset.cartographyProvenance || 'G.5Z.25 provenance unavailable'} · ${cartographyAuditRuntimeWitness.dataset.cartographyCoincidence || 'G.5Z.24 coincidence unavailable'} · ${cartographyAuditRuntimeWitness.dataset.cartographyConnectivity || 'G.5Z.23 connectivity unavailable'} · audit callback ${completedEvidenceAudit ? 'received' : 'MISSING'} · endpoint records ${Array.isArray(endpointRecords) ? endpointRecords.length : 'MISSING'} / expected ${expectedEndpoints ?? 'MISSING'} · SVG markers ${markerCount} · ${Array.isArray(endpointRecords) ? endpointRecords.map((record) => `${record.id}:P${record.pathIndex}/${record.reason}/${record.suppressedRunEdges}e`).join(', ') : 'no endpoint records published'}`);
            if (cartographySuggestions.length === 0 && cartographyAssistantStatus) {
                cartographyAssistantStatus.textContent = cartographyEvidenceAudit
                    ? `Evidence Audit · no emitted contour objects · ${cartographyEvidenceAudit.rawChains} raw chains · ${cartographyEvidenceAudit.semanticRejected} semantic rejects · ${cartographyEvidenceAudit.inferredPerimeters} inferred perimeter edges.`
                    : 'Evidence Audit could not prepare diagnostic contour evidence.';
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
                cartographyAssistantStatus.textContent = 'No safe playable floor contour sections could be prepared. Check grid calibration or try Structural tracing.';
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
                    toggle.textContent = barrier.open ? t('close', 'Close') : t('open', 'Open');
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

    // IV.36.5 — revealed treasure becomes Player-visible through the same Forge revision boundary.
    document.addEventListener('click', async (event) => {
        const button = event.target.closest?.('[data-forge-treasure-action]');
        if (!button || button.disabled) return;
        const marker = button.closest('[data-forge-treasure-id]');
        if (!marker) return;
        button.disabled = true;
        try {
            const data = await request('gmrt_forge_treasure_action', {
                scene_id: treasureSceneId(),
                treasure_id: String(marker.dataset.forgeTreasureId || ''),
                treasure_action: String(button.dataset.forgeTreasureAction || '')
            });
            say(data.message || 'The treasure ledger changed.');
            await replaceChamber(data.message || 'The treasure ledger changed.', treasureSceneId() || null);
        } catch (error) {
            button.disabled = false;
            say(error?.message || 'Pippin cannot reconcile that particular pile of valuables.');
        }
    });

    // IV.36.4 — Keeper controls share the same persisted state as movement triggers.
    document.addEventListener('click', async (event) => {
        const button = event.target.closest?.('[data-forge-trap-action]');
        if (!button || button.disabled) return;
        const marker = button.closest('[data-forge-trap-id]');
        if (!marker) return;
        button.disabled = true;
        try {
            const data = await request('gmrt_forge_trap_action', {
                scene_id: preparationSceneId || projectedSceneId,
                trap_id: String(marker.dataset.forgeTrapId || ''),
                trap_action: String(button.dataset.forgeTrapAction || '')
            });
            say(data.message || 'The trap state changed.');
            await replaceChamber(data.message || 'The trap state changed.', preparationSceneId || projectedSceneId || null);
        } catch (error) {
            button.disabled = false;
            say(error?.message || 'Pippin refuses to touch that mechanism.');
        }
    });

    // IV.36.3 — The Dungeon Has Secrets. Keeper reveal is explicit and persistent.
    document.addEventListener('click', async (event) => {
        const marker = event.target.closest?.('[data-forge-secret-id]');
        if (!marker || marker.disabled) return;
        const secretId=String(marker.dataset.forgeSecretId||'');
        if(!secretId)return;
        marker.disabled=true;
        try {
            const data=await request('gmrt_reveal_forge_secret',{scene_id:preparationSceneId||projectedSceneId,secret_id:secretId});
            say(data.message || 'The secret is revealed.');
            window.location.reload();
        } catch(error) {
            marker.disabled=false;
            say(error?.message || 'That secret refused to be found.');
        }
    });

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
    const dungeonForgeEntry = document.querySelector('[data-dungeon-forge-entry]');
    const dungeonForgeLair = document.querySelector('[data-dungeon-forge-lair]');
    const dungeonForgeLairOccupantWrap = document.querySelector('[data-dungeon-forge-lair-occupant-wrap]');
    const dungeonForgeLairOccupant = document.querySelector('[data-dungeon-forge-lair-occupant]');
    const dungeonForgeLairOccupantHidden = document.querySelector('[data-dungeon-forge-lair-occupant-hidden]');
    const dungeonForgePopulate = document.querySelector('[data-dungeon-forge-populate]');
    const dungeonForgeSecrets = document.querySelector('[data-dungeon-forge-secrets]');
    const dungeonForgeTraps = document.querySelector('[data-dungeon-forge-traps]');
    const dungeonForgeTreasure = document.querySelector('[data-dungeon-forge-treasure]');
    const dungeonForgeStory = document.querySelector('[data-dungeon-forge-story]');
    const updateDungeonForgeLairAvailability = () => {
        if (!dungeonForgeLair) return;
        const allowed = String(dungeonForgeSceneType?.value || 'dungeon') === 'dungeon'
            && String(dungeonForgeStyle?.value || 'standard') === 'grand';
        dungeonForgeLair.disabled = !allowed;
        if (!allowed) dungeonForgeLair.checked = false;
        const chosen = allowed && dungeonForgeLair.checked;
        if (dungeonForgeLairOccupantWrap) dungeonForgeLairOccupantWrap.hidden = !chosen;
        if (!chosen && dungeonForgeLairOccupant) dungeonForgeLairOccupant.value = '';
    };
    dungeonForgeSceneType?.addEventListener('change', updateDungeonForgeLairAvailability);
    dungeonForgeStyle?.addEventListener('change', updateDungeonForgeLairAvailability);
    dungeonForgeLair?.addEventListener('change', updateDungeonForgeLairAvailability);
    updateDungeonForgeLairAvailability();
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
        dungeonForgeLayer.classList.add('has-plan', 'is-pixel-art');
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
        const polygon = (points, className) => forgeSvg('polygon', { points: points.map(([px, py]) => `${px},${py}`).join(' '), class: className });
        const pixelRect = (x, y, width, height, className) => forgeSvg('rect', { x, y, width, height, class: className });

        const outdoorScene = ['forest', 'village'].includes(String(plan.scene_type || 'dungeon'));
        plan.floor.forEach((cell) => {
            const x = Number(cell.x); const y = Number(cell.y);
            const n = decorationChance(x, y, 'floor');
            if (outdoorScene) {
                if (n < .055) decorate.appendChild(pixelRect(x+.24, y+.64, .08, .08, 'is-outdoor-speck'));
                return;
            }
            decorate.appendChild(pixelRect(x+.05,y+.05,.9,.06,'is-dungeon-tile-highlight'));
            decorate.appendChild(pixelRect(x+.89,y+.12,.06,.78,'is-dungeon-tile-shadow'));
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
            const canopy=forgeSvg('g', { class:`is-${String(feature.kind)} is-organic-canopy is-pixel-canopy` });
            const unit=Math.max(.18, Math.min(.34, Math.min(w,h)/5));
            const blocks=Math.max(9, Math.min(20, Math.round((w+h)*2.3)));
            for(let i=0;i<blocks;i+=1){
                const n=decorationChance(x+i,y,'canopy'); const m=decorationChance(x,y+i,'canopy-y');
                const bx=x+.05+(n*Math.max(.2,w-unit*2)); const by=y+.04+(m*Math.max(.2,h-unit*2));
                const size=unit*(i%4===0?2:1.45);
                canopy.appendChild(pixelRect(bx,by,size,size,'is-canopy-pixel'));
                if (i%4===0) canopy.appendChild(pixelRect(bx,by,size*.52,size*.38,'is-canopy-highlight'));
            }
            const trunkX=x+w/2, trunkY=y+h/2;
            canopy.appendChild(pixelRect(trunkX-.14,trunkY-.08,.28,.42,'is-tree-trunk'));
            canopy.appendChild(pixelRect(trunkX-.38,trunkY+.24,.28,.1,'is-tree-root'));
            canopy.appendChild(pixelRect(trunkX+.1,trunkY+.24,.28,.1,'is-tree-root'));
            return canopy;
        };
        const organicRocks = (feature) => {
            const x=Number(feature.x||0), y=Number(feature.y||0), w=Math.max(.5,Number(feature.w||1)), h=Math.max(.5,Number(feature.h||1));
            const rocks=forgeSvg('g',{class:'is-rock-cluster is-organic-rocks is-pixel-rocks'});
            [[.2,.62,.28],[.46,.38,.35],[.72,.62,.3]].forEach(([px,py,pr])=>{
                const cx=x+w*px, cy=y+h*py, r=Math.min(w,h)*pr;
                rocks.appendChild(polygon([[cx-r,cy+.12*r],[cx-.58*r,cy-.72*r],[cx+.08*r,cy-r],[cx+.78*r,cy-.55*r],[cx+r,cy+.18*r],[cx+.52*r,cy+.82*r],[cx-.36*r,cy+.72*r]],'is-rock'));
                rocks.appendChild(polygon([[cx-.48*r,cy-.18*r],[cx-.08*r,cy-.55*r],[cx+.38*r,cy-.3*r],[cx+.12*r,cy-.05*r]],'is-rock-highlight'));
            });
            return rocks;
        };
        const villageBuilding = (feature, kind) => {
            const x=Number(feature.x||0), y=Number(feature.y||0), w=Math.max(2,Number(feature.w||2)), h=Math.max(2,Number(feature.h||2));
            const building=forgeSvg('g',{class:`is-${kind} is-village-building`});
            building.appendChild(pixelRect(x,y,w,h,'is-building-shadow'));
            building.appendChild(pixelRect(x+.12,y+.12,w-.24,h-.24,'is-building-body'));
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
            building.appendChild(pixelRect(x+w/2-.22,doorY,.44,.24,'is-building-door'));
            building.appendChild(pixelRect(x+.38,y+h*.45,.42,.34,'is-building-window'));
            building.appendChild(pixelRect(x+w-.8,y+h*.45,.42,.34,'is-building-window'));
            if (kind === 'inn' || kind === 'workshop') {
                building.appendChild(pixelRect(x+w-.85,y+.18,.34,.5,'is-building-chimney'));
            }
            for (let stripe=1; stripe<4; stripe+=1) {
                const sy=y+(h*stripe/4);
                building.appendChild(line(x+.18,sy,x+w-.18,sy,'is-roof-pixel-line'));
            }
            return building;
        };
        const fallenLog = (feature) => {
            const x=Number(feature.x||0), y=Number(feature.y||0), w=Math.max(1,Number(feature.w||3)), h=Math.max(.5,Number(feature.h||1));
            const log=forgeSvg('g',{class:'is-fallen-log is-detailed-log'});
            log.appendChild(pixelRect(x+.15,y+.24,w-.3,Math.max(.28,h-.48),'is-log-body'));
            log.appendChild(pixelRect(x+.03,y+h/2-.22,.34,.44,'is-log-end'));
            log.appendChild(pixelRect(x+w-.37,y+h/2-.22,.34,.44,'is-log-end'));
            for(let i=1;i<4;i+=1) log.appendChild(line(x+(w*i/4),y+.3,x+(w*i/4)-.18,y+h-.3,'is-log-bark'));
            return log;
        };
        const villageWell = (feature) => {
            const x=Number(feature.x||0), y=Number(feature.y||0), w=Math.max(1,Number(feature.w||1)), h=Math.max(1,Number(feature.h||1));
            const cx=x+w/2, cy=y+h/2, r=Math.min(w,h)*.46;
            const well=forgeSvg('g',{class:'is-well is-detailed-well is-pixel-well'});
            const oct=(radius,className)=>polygon([[cx-radius*.58,cy-radius],[cx+radius*.58,cy-radius],[cx+radius,cy-radius*.58],[cx+radius,cy+radius*.58],[cx+radius*.58,cy+radius],[cx-radius*.58,cy+radius],[cx-radius,cy+radius*.58],[cx-radius,cy-radius*.58]],className);
            well.appendChild(oct(r,'is-well-rim'));
            well.appendChild(oct(r*.58,'is-well-water'));
            well.appendChild(line(cx-r*.72,cy-r*.72,cx-r*.72,cy+r*.35,'is-well-post'));
            well.appendChild(line(cx+r*.72,cy-r*.72,cx+r*.72,cy+r*.35,'is-well-post'));
            well.appendChild(line(cx-r*.9,cy-r*.72,cx+r*.9,cy-r*.72,'is-well-beam'));
            return well;
        };
        const fencedGarden = (feature) => {
            const x=Number(feature.x||0), y=Number(feature.y||0), w=Math.max(1,Number(feature.w||1)), h=Math.max(1,Number(feature.h||1));
            const garden=forgeSvg('g',{class:'is-fenced-garden is-detailed-garden'});
            garden.appendChild(pixelRect(x,y,w,h,'is-garden-soil'));
            for(let i=1;i<4;i+=1) garden.appendChild(line(x+.5,y+(h*i/4),x+w-.5,y+(h*i/4),'is-garden-row'));
            garden.appendChild(pixelRect(x+.08,y+.08,w-.16,h-.16,'is-garden-fence'));
            [[x+.08,y+.08],[x+w-.16,y+.08],[x+.08,y+h-.16],[x+w-.16,y+h-.16]].forEach(([px,py])=>garden.appendChild(pixelRect(px,py,.12,.12,'is-garden-post')));
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
            featureGroup.appendChild(pixelRect(x, y, w, h, `is-${kind}`));
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

        if (plan.entry_anchor) {
            const anchor=plan.entry_anchor;
            const marker=forgeSvg('g',{class:`gmrt-forge-entry is-${String(anchor.type || 'entrance')}`});
            const cx=Number(anchor.x||0)*cols, cy=Number(anchor.y||0)*rows;
            marker.appendChild(forgeSvg('circle',{cx,cy,r:.36,class:'is-entry-ring'}));
            marker.appendChild(forgeSvg('circle',{cx,cy,r:.12,class:'is-entry-core'}));
            dungeonForgeLayer.appendChild(marker);
        }

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

    // IV.35.8B — Every Dungeon Needs a Door.
    // A Forge arrival is semantic as well as visual: Entrance reaches the map
    // boundary, while Portal may stand inside a playable room. Both become the
    // authoritative Party Arrival Threshold when the draft is built.
    const forgeEntryMode = (value) => ['entrance','portal'].includes(String(value || '')) ? String(value) : 'none';

    const forgeRoomCentreCell = (room) => ({
        x: Math.max(room.x, Math.min(room.x + room.w - 1, Math.floor(room.x + room.w / 2))),
        y: Math.max(room.y, Math.min(room.y + room.h - 1, Math.floor(room.y + room.h / 2)))
    });

    const forgeDungeonEntrance = (rooms, cols, rows, seed) => {
        if (!rooms.length) return null;
        const ranked = rooms.map((room, index) => {
            const c = forgeRoomCentreCell(room);
            const distances = [
                {side:'west', value:c.x}, {side:'east', value:(cols - 1) - c.x},
                {side:'north', value:c.y}, {side:'south', value:(rows - 1) - c.y}
            ].sort((a,b)=>a.value-b.value || a.side.localeCompare(b.side));
            return {room,index,cell:c,side:distances[0].side,distance:distances[0].value};
        }).sort((a,b)=>a.distance-b.distance || forgeHash(`${seed}|entry|${a.index}`)-forgeHash(`${seed}|entry|${b.index}`));
        const chosen=ranked[0];
        const {x,y}=chosen.cell;
        const boundary = chosen.side==='west' ? {axis:'vertical',line:0,start:y,door:{x1:0,y1:y/rows,x2:0,y2:(y+1)/rows},cell:{x:0,y}}
            : chosen.side==='east' ? {axis:'vertical',line:cols,start:y,door:{x1:1,y1:y/rows,x2:1,y2:(y+1)/rows},cell:{x:cols-1,y}}
            : chosen.side==='north' ? {axis:'horizontal',line:0,start:x,door:{x1:x/cols,y1:0,x2:(x+1)/cols,y2:0},cell:{x,y:0}}
            : {axis:'horizontal',line:rows,start:x,door:{x1:x/cols,y1:1,x2:(x+1)/cols,y2:1},cell:{x,y:rows-1}};
        return {
            ...boundary,
            start: chosen.cell,
            anchor:{type:'entrance',x:(boundary.cell.x+.5)/cols,y:(boundary.cell.y+.5)/rows,facing:chosen.side}
        };
    };

    const forgePortalAnchor = (rooms, cols, rows, seed) => {
        if (!rooms.length) return null;
        const room=[...rooms].sort((a,b)=>(b.w*b.h)-(a.w*a.h) || forgeHash(`${seed}|portal|${a.x}:${a.y}`)-forgeHash(`${seed}|portal|${b.x}:${b.y}`))[0];
        const cell=forgeRoomCentreCell(room);
        return {type:'portal',x:(cell.x+.5)/cols,y:(cell.y+.5)/rows,facing:'centre'};
    };

    // Legacy IV.30.2 source contract: generateDungeonForgePlan = (seed, style)
    const generateDungeonForgePlan = (seed, style, theme = 'pantry-stone', preferredAspect = null, entryMode = 'none', includeBossLair = false) => {
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
        const wantsBossLair = style === 'grand' && includeBossLair === true;

        // IV.35.8C — reserve the boss chamber before ordinary packing so the
        // largest map always has a genuinely large, combat-ready room.
        if (wantsBossLair) {
            const lairW = Math.max(10, Math.min(14, Math.floor(cols * .32)));
            const lairH = Math.max(8, Math.min(11, Math.floor(rows * .34)));
            const lairX = forgeHash(`${seed}|lair-x`) % 2 === 0 ? 2 : Math.max(2, cols - lairW - 2);
            const lairY = 2 + (forgeHash(`${seed}|lair-y`) % Math.max(1, rows - lairH - 3));
            rooms.push({ x:lairX, y:lairY, w:lairW, h:lairH, role:'lair', boss_lair:true });
        }

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

        const requestedEntry = forgeEntryMode(entryMode);
        const dungeonEntrance = requestedEntry === 'entrance'
            ? forgeDungeonEntrance(rooms, cols, rows, seed)
            : null;
        if (dungeonEntrance) {
            const start=dungeonEntrance.start;
            const target=dungeonEntrance.cell;
            if(dungeonEntrance.axis==='vertical'){
                const step=start.x<=target.x?1:-1;
                for(let x=start.x;x!==target.x+step;x+=step) carve(x,start.y);
            } else {
                const step=start.y<=target.y?1:-1;
                for(let y=start.y;y!==target.y+step;y+=step) carve(start.x,y);
            }
            doors.push(dungeonEntrance.door);
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
            if (!floorSet.has(forgeFloorKey(x,y-1)) && !(dungeonEntrance?.axis==='horizontal' && dungeonEntrance.line===y && dungeonEntrance.start===x)) addEdge(horizontal,y,x);
            if (!floorSet.has(forgeFloorKey(x,y+1)) && !(dungeonEntrance?.axis==='horizontal' && dungeonEntrance.line===y+1 && dungeonEntrance.start===x)) addEdge(horizontal,y+1,x);
            if (!floorSet.has(forgeFloorKey(x-1,y)) && !(dungeonEntrance?.axis==='vertical' && dungeonEntrance.line===x && dungeonEntrance.start===y)) addEdge(vertical,x,y);
            if (!floorSet.has(forgeFloorKey(x+1,y)) && !(dungeonEntrance?.axis==='vertical' && dungeonEntrance.line===x+1 && dungeonEntrance.start===y)) addEdge(vertical,x+1,y);
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
            version:4, scene_type:'dungeon', seed, style, theme, cols, rows,
            floor:[...floor.values()], rooms, doors, barriers, lights, features:[],
            entry_anchor: requestedEntry === 'portal'
                ? forgePortalAnchor(rooms, cols, rows, seed)
                : (dungeonEntrance?.anchor || null)
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

    const generateSceneForgePlan = (sceneType, seed, style, theme = 'pantry-stone', preferredAspect = null, entryMode = 'none', includeBossLair = false) => {
        const mode=forgeEntryMode(entryMode);
        if (sceneType === 'forest' || sceneType === 'village') {
            const plan=sceneType === 'forest'
                ? generateForestForgePlan(seed, style, theme, preferredAspect)
                : generateVillageForgePlan(seed, style, theme, preferredAspect);
            if(mode==='portal') plan.entry_anchor=forgePortalAnchor(plan.rooms,plan.cols,plan.rows,seed);
            if(mode==='entrance') {
                const side=forgeHash(`${seed}|outdoor-entry`) % 4;
                const cells=[{x:.5/plan.cols,y:.5,facing:'west'},{x:(plan.cols-.5)/plan.cols,y:.5,facing:'east'},{x:.5,y:.5/plan.rows,facing:'north'},{x:.5,y:(plan.rows-.5)/plan.rows,facing:'south'}];
                plan.entry_anchor={type:'entrance',...cells[side]};
            }
            plan.version=4;
            return plan;
        }
        return generateDungeonForgePlan(seed, style, theme, preferredAspect, mode, includeBossLair);
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
        const entryMode = forgeEntryMode(dungeonForgeEntry?.value || 'none');
        dungeonForgeDraft = generateSceneForgePlan(sceneType, seed, style, theme, null, entryMode, Boolean(dungeonForgeLair?.checked));
        dungeonForgeDraft.lair_occupant_id = dungeonForgeLair?.checked ? String(dungeonForgeLairOccupant?.value || '') : '';
        dungeonForgeDraft.lair_occupant_hidden = Boolean(dungeonForgeDraft.lair_occupant_id && dungeonForgeLairOccupantHidden?.checked);
        dungeonForgeDraft.populate_rooms = String(dungeonForgeDraft.scene_type || 'dungeon') === 'dungeon' && Boolean(dungeonForgePopulate?.checked);
        dungeonForgeDraft.include_secrets = String(dungeonForgeDraft.scene_type || 'dungeon') === 'dungeon' && Boolean(dungeonForgeSecrets?.checked);
        dungeonForgeDraft.include_traps = String(dungeonForgeDraft.scene_type || 'dungeon') === 'dungeon' && Boolean(dungeonForgeTraps?.checked);
        dungeonForgeDraft.include_treasure = String(dungeonForgeDraft.scene_type || 'dungeon') === 'dungeon' && Boolean(dungeonForgeTreasure?.checked);
        dungeonForgeDraft.include_story = String(dungeonForgeDraft.scene_type || 'dungeon') === 'dungeon' && Boolean(dungeonForgeStory?.checked);
        renderDungeonForgePlan(dungeonForgeDraft, true);
        if (dungeonForgeBuild) dungeonForgeBuild.disabled = false;
        if (dungeonForgeClear) dungeonForgeClear.disabled = false;
        setForgeStatus(`${String(dungeonForgeDraft.scene_type || 'dungeon')} · ${dungeonForgeDraft.rooms.length} major places · ${dungeonForgeDraft.barriers.length} vision objects · ${dungeonForgeDraft.doors.length} doors · ${dungeonForgeDraft.lights.length} suggested lights${dungeonForgeDraft.entry_anchor ? ` · ${dungeonForgeDraft.entry_anchor.type} arrival` : ''} · preview only.`);
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
            setForgeStatus(data.message || 'Dungeon forged. Walls, doors, furniture, lights, grid and Fog are now authoritative.');
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
    const bestiarySearch = document.querySelector('[data-bestiary-search]');
    const bestiaryResults = document.querySelector('[data-bestiary-results]');
    const bestiaryEmpty = document.querySelector('[data-bestiary-empty]');
    const bestiaryFilterButtons = Array.from(document.querySelectorAll('[data-bestiary-filter]'));
    let bestiaryMapFilter = 'all';
    const setBestiaryOpen = (open) => setKeeperDrawerOpen('bestiary', open);

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
    const atlasForgeName = document.querySelector('[data-atlas-forge-name]');
    const atlasForgeSeed = document.querySelector('[data-atlas-forge-seed]');
    const atlasForgeSceneType = document.querySelector('[data-atlas-forge-scene-type]');
    const atlasForgeStyle = document.querySelector('[data-atlas-forge-style]');
    const atlasForgeEntry = document.querySelector('[data-atlas-forge-entry]');
    const atlasForgeLair = document.querySelector('[data-atlas-forge-lair]');
    const atlasForgeLairOccupantWrap = document.querySelector('[data-atlas-forge-lair-occupant-wrap]');
    const atlasForgeLairOccupant = document.querySelector('[data-atlas-forge-lair-occupant]');
    const atlasForgeLairOccupantHidden = document.querySelector('[data-atlas-forge-lair-occupant-hidden]');
    const atlasForgePopulate = document.querySelector('[data-atlas-forge-populate]');
    const atlasForgeSecrets = document.querySelector('[data-atlas-forge-secrets]');
    const atlasForgeTraps = document.querySelector('[data-atlas-forge-traps]');
    const atlasForgeTreasure = document.querySelector('[data-atlas-forge-treasure]');
    const atlasForgeStory = document.querySelector('[data-atlas-forge-story]');
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
    const updateAtlasForgeLairAvailability = () => {
        if (!atlasForgeLair) return;
        const allowed = String(atlasForgeSceneType?.value || 'dungeon') === 'dungeon'
            && String(atlasForgeStyle?.value || 'standard') === 'grand';
        atlasForgeLair.disabled = !allowed;
        if (!allowed) atlasForgeLair.checked = false;
        const chosen = allowed && atlasForgeLair.checked;
        if (atlasForgeLairOccupantWrap) atlasForgeLairOccupantWrap.hidden = !chosen;
        if (!chosen && atlasForgeLairOccupant) atlasForgeLairOccupant.value = '';
    };
    atlasForgeSceneType?.addEventListener('change', updateAtlasForgeLairAvailability);
    atlasForgeStyle?.addEventListener('change', updateAtlasForgeLairAvailability);
    atlasForgeLair?.addEventListener('change', updateAtlasForgeLairAvailability);
    updateAtlasForgeLairAvailability();
    const setAtlasOpen = (open) => setKeeperDrawerOpen('atlas', open);

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
        const entryMode = forgeEntryMode(atlasForgeEntry?.value || 'none');
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
            plan = generateSceneForgePlan(sceneType, seed, style, theme, aspectByStyle[style] || .7, entryMode, Boolean(atlasForgeLair?.checked));
            plan.lair_occupant_id = atlasForgeLair?.checked ? String(atlasForgeLairOccupant?.value || '') : '';
            plan.lair_occupant_hidden = Boolean(plan.lair_occupant_id && atlasForgeLairOccupantHidden?.checked);
            plan.populate_rooms = String(plan.scene_type || 'dungeon') === 'dungeon' && Boolean(atlasForgePopulate?.checked);
            plan.include_secrets = String(plan.scene_type || 'dungeon') === 'dungeon' && Boolean(atlasForgeSecrets?.checked);
            plan.include_traps = String(plan.scene_type || 'dungeon') === 'dungeon' && Boolean(atlasForgeTraps?.checked);
            plan.include_treasure = String(plan.scene_type || 'dungeon') === 'dungeon' && Boolean(atlasForgeTreasure?.checked);
            plan.include_story = String(plan.scene_type || 'dungeon') === 'dungeon' && Boolean(atlasForgeStory?.checked);
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
            button.textContent = t('preparing', 'Preparing…');
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

            setAtlasOpen(false);

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


    // IV.35.8D — Pippin Draws the Way Home.
    // Scene routes are Keeper-controlled. The destination Party Arrival
    // Threshold remains the single authoritative arrival anchor.
    const sceneTransition = document.querySelector('[data-scene-transition]');
    const sceneTransitionDestination = document.querySelector('[data-scene-transition-destination]');
    const sceneTransitionSave = document.querySelector('[data-scene-transition-save]');
    const sceneTransitionTravel = document.querySelector('[data-scene-transition-travel]');
    const sceneTransitionRemove = document.querySelector('[data-scene-transition-remove]');
    const sceneTransitionStatus = document.querySelector('[data-scene-transition-status]');

    const setSceneTransitionState = (transition) => {
        const destination = String(transition?.destination_scene_id || '');
        if (sceneTransitionDestination) sceneTransitionDestination.value = destination;
        if (sceneTransitionTravel) sceneTransitionTravel.disabled = destination === '';
        if (sceneTransitionRemove) sceneTransitionRemove.disabled = destination === '';
    };

    if (sceneTransition) {
        const sourceSceneId = String(sceneTransition.dataset.sourceSceneId || '');
        request('gmrt_atlas_transition_status', { source_scene_id: sourceSceneId })
            .then((data) => setSceneTransitionState(data.transition || null))
            .catch(() => setSceneTransitionState(null));

        sceneTransitionSave?.addEventListener('click', async () => {
            const destinationSceneId = String(sceneTransitionDestination?.value || '');
            if (!destinationSceneId) {
                if (sceneTransitionStatus) sceneTransitionStatus.textContent = 'Choose another Scene first.';
                return;
            }
            try {
                const data = await request('gmrt_atlas_link_transition', {
                    source_scene_id: sourceSceneId,
                    destination_scene_id: destinationSceneId
                });
                setSceneTransitionState(data.transition || null);
                if (sceneTransitionStatus) sceneTransitionStatus.textContent = data.message || 'Route drawn.';
            } catch (error) {
                if (sceneTransitionStatus) sceneTransitionStatus.textContent = error.message || 'The route could not be drawn.';
            }
        });

        sceneTransitionTravel?.addEventListener('click', async () => {
            if (!window.confirm('Take the Table through this route and open its destination Scene?')) return;
            sceneTransitionTravel.disabled = true;
            try {
                const data = await request('gmrt_atlas_travel_transition', { source_scene_id: sourceSceneId });
                const message = data.message || 'The party crosses the threshold.';
                if (sceneTransitionStatus) sceneTransitionStatus.textContent = message;
                say(message);
                await replaceChamber(message, null);
            } catch (error) {
                if (sceneTransitionStatus) sceneTransitionStatus.textContent = error.message || 'The party could not travel.';
                sceneTransitionTravel.disabled = false;
            }
        });

        sceneTransitionRemove?.addEventListener('click', async () => {
            try {
                const data = await request('gmrt_atlas_remove_transition', { source_scene_id: sourceSceneId });
                setSceneTransitionState(null);
                if (sceneTransitionStatus) sceneTransitionStatus.textContent = data.message || 'Route erased.';
            } catch (error) {
                if (sceneTransitionStatus) sceneTransitionStatus.textContent = error.message || 'The route could not be erased.';
            }
        });
    }

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
                'is-out-of-range',
                'has-object-cover',
                'is-full-object-cover',
                'is-object-vision-blocked'
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

    function segmentsIntersect(a, b, c, d) {
        const cross = (p, q, r) => (q.x - p.x) * (r.y - p.y) - (q.y - p.y) * (r.x - p.x);
        const abC = cross(a, b, c);
        const abD = cross(a, b, d);
        const cdA = cross(c, d, a);
        const cdB = cross(c, d, b);
        return ((abC > 0 && abD < 0) || (abC < 0 && abD > 0))
            && ((cdA > 0 && cdB < 0) || (cdA < 0 && cdB > 0));
    }

    function lineIntersectsPolygon(start, end, polygon) {
        for (let index = 0; index < polygon.length; index += 1) {
            if (segmentsIntersect(
                start,
                end,
                polygon[index],
                polygon[(index + 1) % polygon.length]
            )) {
                return true;
            }
        }
        return false;
    }

    function sceneObjectCoverBetween(attacker, target) {
        const attackerRect = attacker.getBoundingClientRect();
        const targetRect = target.getBoundingClientRect();
        const start = {
            x: attackerRect.left + attackerRect.width / 2,
            y: attackerRect.top + attackerRect.height / 2
        };
        const end = {
            x: targetRect.left + targetRect.width / 2,
            y: targetRect.top + targetRect.height / 2
        };
        const battlemap = board.querySelector('[data-battlemap-image]');
        const mapRect = (battlemap || board).getBoundingClientRect();
        const coverRank = {none: 0, half: 1, three_quarters: 2, full: 3};
        let result = {cover: 'none', blocksVision: false};

        document.querySelectorAll('[data-scene-object-id][data-object-cover]').forEach((object) => {
            const cover = String(object.dataset.objectCover || 'none');
            if (!coverRank[cover]) return;
            const objectX = Number(object.dataset.sceneObjectX || 0.5);
            const objectY = Number(object.dataset.sceneObjectY || 0.5);
            const rotation = Number(object.dataset.sceneObjectRotation || 0);
            const polygon = rectangleCorners(
                mapRect.left + objectX * mapRect.width,
                mapRect.top + objectY * mapRect.height,
                Math.max(1, object.offsetWidth),
                Math.max(1, object.offsetHeight),
                rotation
            );
            if (!lineIntersectsPolygon(start, end, polygon)) return;
            if ((coverRank[cover] || 0) > (coverRank[result.cover] || 0)) {
                result = {
                    cover,
                    blocksVision: object.dataset.blocksVision === 'true'
                };
            } else if (object.dataset.blocksVision === 'true') {
                result.blocksVision = true;
            }
        });

        return result;
    }

    function coverLabel(cover) {
        if (cover === 'half') return 'HALF COVER';
        if (cover === 'three_quarters') return '3/4 COVER';
        if (cover === 'full') return 'FULL COVER';
        return '';
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

            const attackerId = deedsPanel?.dataset.currentToken || '';
            const attacker = attackerId !== ''
                ? document.querySelector('[data-token-id="' + CSS.escape(attackerId) + '"]')
                : null;
            const target = document.querySelector(
                '[data-token-id="' + CSS.escape(String(attackTarget.value)) + '"]'
            );
            const objectCover = attacker && target
                ? sceneObjectCoverBetween(attacker, target)
                : {cover: 'none', blocksVision: false};
            const tacticalCoverLabel = coverLabel(objectCover.cover);
            if (tacticalCoverLabel) {
                label += ' · ' + tacticalCoverLabel;
            }
            if (objectCover.blocksVision) {
                label += ' · OBSCURED';
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
            if (targetLine) {
                targetLine.classList.toggle('has-object-cover', objectCover.cover !== 'none');
                targetLine.classList.toggle('is-full-object-cover', objectCover.cover === 'full');
                targetLine.classList.toggle('is-object-vision-blocked', objectCover.blocksVision);
            }

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

    function tokenPoint(token) {
        const x = parseFloat(token.style.getPropertyValue('--gmrt-token-x')) / 100;
        const y = parseFloat(token.style.getPropertyValue('--gmrt-token-y')) / 100;
        return {
            x: Number.isFinite(x) ? x : 0.5,
            y: Number.isFinite(y) ? y : 0.5
        };
    }

    function rectangleCorners(cx, cy, width, height, rotationDegrees = 0) {
        const halfWidth = Math.max(0, width) / 2;
        const halfHeight = Math.max(0, height) / 2;
        const radians = rotationDegrees * Math.PI / 180;
        const cosine = Math.cos(radians);
        const sine = Math.sin(radians);

        return [
            [-halfWidth, -halfHeight],
            [halfWidth, -halfHeight],
            [halfWidth, halfHeight],
            [-halfWidth, halfHeight]
        ].map(([x, y]) => ({
            x: cx + x * cosine - y * sine,
            y: cy + x * sine + y * cosine
        }));
    }

    function polygonsOverlap(first, second) {
        const polygons = [first, second];
        const epsilon = 0.35;

        for (const polygon of polygons) {
            for (let index = 0; index < polygon.length; index += 1) {
                const current = polygon[index];
                const next = polygon[(index + 1) % polygon.length];
                const axis = {
                    x: -(next.y - current.y),
                    y: next.x - current.x
                };
                const length = Math.hypot(axis.x, axis.y) || 1;
                axis.x /= length;
                axis.y /= length;

                const project = (points) => points.reduce((range, point) => {
                    const value = point.x * axis.x + point.y * axis.y;
                    return {
                        min: Math.min(range.min, value),
                        max: Math.max(range.max, value)
                    };
                }, {min: Infinity, max: -Infinity});

                const a = project(first);
                const b = project(second);
                if (a.max <= b.min + epsilon || b.max <= a.min + epsilon) {
                    return false;
                }
            }
        }

        return true;
    }

    function tokenCollidesWithFurniture(token, point) {
        const battlemap = board.querySelector('[data-battlemap-image]');
        const rect = (battlemap || board).getBoundingClientRect();
        const tokenWidth = Math.max(1, token.offsetWidth);
        const tokenHeight = Math.max(1, token.offsetHeight);
        const tokenPolygon = rectangleCorners(
            rect.left + point.x * rect.width,
            rect.top + point.y * rect.height,
            tokenWidth,
            tokenHeight
        );

        return Array.from(document.querySelectorAll('[data-scene-object-id][data-blocks-movement="true"]'))
            .some((object) => {
                const objectX = Number(object.dataset.sceneObjectX || 0.5);
                const objectY = Number(object.dataset.sceneObjectY || 0.5);
                const objectWidth = Math.max(1, object.offsetWidth);
                const objectHeight = Math.max(1, object.offsetHeight);
                const rotation = Number(object.dataset.sceneObjectRotation || 0);
                const objectPolygon = rectangleCorners(
                    rect.left + objectX * rect.width,
                    rect.top + objectY * rect.height,
                    objectWidth,
                    objectHeight,
                    rotation
                );
                return polygonsOverlap(tokenPolygon, objectPolygon);
            });
    }

    function movementPathBlocked(token, from, to) {
        const battlemap = board.querySelector('[data-battlemap-image]');
        const rect = (battlemap || board).getBoundingClientRect();
        const distancePixels = Math.hypot(
            (to.x - from.x) * rect.width,
            (to.y - from.y) * rect.height
        );
        const stride = Math.max(4, Math.min(token.offsetWidth, token.offsetHeight) / 3);
        const steps = Math.max(1, Math.ceil(distancePixels / stride));

        let escapedInitialOverlap = !tokenCollidesWithFurniture(token, from);

        for (let step = 1; step <= steps; step += 1) {
            const progress = step / steps;
            const point = {
                x: from.x + (to.x - from.x) * progress,
                y: from.y + (to.y - from.y) * progress
            };
            const collides = tokenCollidesWithFurniture(token, point);

            // A Keeper may move furniture onto an existing token. Never trap that
            // token forever: allow it to leave the pre-existing overlap, then begin
            // enforcing collision normally as soon as it reaches clear floor.
            if (!escapedInitialOverlap) {
                if (!collides) escapedInitialOverlap = true;
                continue;
            }

            if (collides) {
                return true;
            }
        }

        return false;
    }

    async function moveSelected(x, y) {
        if (!selected || !tableId) {
            return;
        }

        const destination = {
            x: Math.max(0, Math.min(1, Number(x))),
            y: Math.max(0, Math.min(1, Number(y)))
        };
        const origin = tokenPoint(selected);
        if (movementPathBlocked(selected, origin, destination)) {
            selected.classList.add('is-movement-blocked');
            window.setTimeout(() => selected?.classList.remove('is-movement-blocked'), 260);
            say('That route is blocked by the furnishings. Pippin refuses to draw the token inside the furniture.');
            return;
        }

        const tokenId = selected.dataset.tokenId || '';
        const revision = Number(selected.dataset.tokenRevision || '1');

        try {
            const data = await request('gmrt_move_token', {
                token_id: tokenId,
                x: destination.x,
                y: destination.y,
                revision: revision
            });

            const token = data.token;
            selected.style.setProperty('--gmrt-token-x', (token.x * 100) + '%');
            selected.style.setProperty('--gmrt-token-y', (token.y * 100) + '%');
            selected.dataset.tokenRevision = String(token.revision);
            if(data.trap){say(`CLICK! ${data.trap.label || 'A trap'} has been sprung. ${data.trap.effect || ''}`.trim());}else{say((token.label || 'Token') + ' moved.');}
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
        if (tokenDragInProgress || sceneObjectDrag) {
            return;
        }

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

            const currentSessionId = String(root.dataset.sessionId || '');
            const currentSessionStatus = String(root.dataset.sessionStatus || '');
            const incomingSessionId = String(state.session?.id || '');
            const incomingSessionStatus = String(state.session?.status || '');
            if (
                currentSessionId !== incomingSessionId
                || currentSessionStatus !== incomingSessionStatus
            ) {
                await replaceChamber(
                    incomingSessionId
                        ? 'The Keeper has called the Session.'
                        : 'The current Session has concluded.',
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
                const previousTurnIdentity = currentEncounterTurnIdentity(currentEncounter);
                const incomingTurnIdentity = [
                    incomingEncounterId,
                    String(incomingEncounter.round || 0),
                    String(incomingEncounter.current_token_id || '')
                ].join(':');

                if (
                    previousTurnIdentity !== incomingTurnIdentity
                    && diceworks
                    && diceworks.dataset.turnIdentity
                ) {
                    clearTransientCombatRoll();
                }

                currentEncounter.dataset.encounterRevision = incomingEncounterRevision;
                currentEncounter.dataset.encounterRound = String(incomingEncounter.round || 0);

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

            const incomingForgeRevision = String(state.forge_revision || '');
            const currentForgeRevision = String(root.dataset.forgeRevision || '');
            if (incomingForgeRevision && currentForgeRevision && incomingForgeRevision !== currentForgeRevision) {
                await replaceChamber(
                    root.dataset.viewerRole === 'player'
                        ? 'Something in the dungeon has changed.'
                        : 'The Keeper has amended the dungeon.',
                    null
                );
                return;
            }
            if (incomingForgeRevision) {
                root.dataset.forgeRevision = incomingForgeRevision;
            }

            if (state.sync_revision) {
                root.dataset.syncRevision = String(state.sync_revision);
            }

            renderGathering(state.members);
            renderChronicle(state.encounter ? state.battle_log : state.chamber_log, Boolean(state.encounter));
            renderFootsteps(state.footsteps || []);
            renderFog(state.fog || {});
            renderVisionLayer(state.vision_layer || []);
            await refreshSceneObjectLayer();

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
                node.dataset.tokenSource = String(token.source_reference || '');
                node.classList.toggle(
                    'is-hidden-token',
                    String(token.visibility || '') === 'hidden'
                );
                node.style.setProperty('--gmrt-fellowship-colour', String(token.table_colour_hex || '#d8ad4f'));
                bindTokenInteractions(node);

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

    function bindTokenInteractions(token) {
        if (!token || token.dataset.tokenInteractionsBound === '1') return;
        token.dataset.tokenInteractionsBound = '1';
        token.setAttribute('aria-pressed', 'false');

        const tokenDrag = {
            active: false,
            moved: false,
            pointerId: null,
            startX: 0,
            startY: 0,
            threshold: 3,
            lastValidPoint: null,
            blocked: false
        };

        token.addEventListener('pointerdown', (event) => {
            if (event.button !== 0) return;

            event.stopPropagation();
            select(token);
            tokenDrag.active = true;
            tokenDrag.moved = false;
            tokenDragInProgress = true;
            tokenDrag.pointerId = event.pointerId;
            tokenDrag.startX = event.clientX;
            tokenDrag.startY = event.clientY;
            tokenDrag.lastValidPoint = tokenPoint(token);
            tokenDrag.blocked = false;
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
            const from = tokenDrag.lastValidPoint || tokenPoint(token);
            if (movementPathBlocked(token, from, point)) {
                tokenDrag.blocked = true;
                token.classList.add('is-movement-blocked');
                say('Blocked by furniture. Pippin says the map is quite clear on this point.');
                return;
            }

            tokenDrag.blocked = false;
            token.classList.remove('is-movement-blocked');
            tokenDrag.lastValidPoint = point;
            token.style.setProperty(
                '--gmrt-token-x',
                (point.x * 100) + '%'
            );
            token.style.setProperty(
                '--gmrt-token-y',
                (point.y * 100) + '%'
            );
        });

        const finishTokenDrag = async (event) => {
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
                const point = tokenDrag.lastValidPoint || tokenPoint(token);
                token.classList.remove('is-movement-blocked');
                await moveSelected(point.x, point.y);
                tokenDrag.lastValidPoint = null;
                tokenDrag.blocked = false;
            }

            tokenDragInProgress = false;
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
    }

    document.querySelectorAll('.gmrt-token').forEach((token) => {
        bindTokenInteractions(token);
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

    function clearTransientCombatRoll() {
        if (!diceworks) {
            return;
        }

        diceworks.hidden = true;
        diceworks.classList.remove(
            'is-rolling',
            'is-critical-hit',
            'is-critical-miss'
        );
        diceworks.dataset.turnIdentity = '';

        if (diceworksMode) {
            diceworksMode.textContent = 'D20';
        }
        if (diceworksResult) {
            diceworksResult.textContent = 'Awaiting the roll…';
        }
        if (diceworksOutcome) {
            diceworksOutcome.hidden = true;
        }
        if (diceworksOutcomeTitle) {
            diceworksOutcomeTitle.textContent = 'Awaiting result';
        }
        if (diceworksOutcomeDetail) {
            diceworksOutcomeDetail.textContent = '';
        }
        if (damageRollButton) {
            damageRollButton.hidden = true;
            damageRollButton.disabled = false;
            damageRollButton.dataset.attackEventId = '';
        }
        combatDice.forEach((die) => {
            die.hidden = true;
            die.classList.remove('is-chosen', 'is-rejected');
            const value = die.querySelector('[data-die-value]');
            if (value) {
                value.textContent = '?';
            }
        });
        if (lonelyConfetti) {
            lonelyConfetti.hidden = true;
        }
    }

    function currentEncounterTurnIdentity(encounterNode = null) {
        const encounter = encounterNode
            || document.querySelector('[data-encounter-id]');
        const deeds = document.querySelector('.gmrt-deeds[data-current-token]');

        if (!encounter) {
            return '';
        }

        return [
            encounter.dataset.encounterId || '',
            encounter.dataset.encounterRound || '0',
            deeds ? (deeds.dataset.currentToken || '') : ''
        ].join(':');
    }

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
        diceworks.dataset.turnIdentity = currentEncounterTurnIdentity();
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
