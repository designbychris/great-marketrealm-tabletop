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
        if (event.button !== 0 || isLensInteractiveTarget(event.target)) {
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

    renderFog();

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

    function renderBattleLog(entries) {
        if (!battleLog) {
            return;
        }

        battleLog.replaceChildren();

        const safeEntries = Array.isArray(entries)
            ? entries
            : [];

        safeEntries.forEach((entry) => {
            const item = document.createElement('li');
            item.dataset.battleLogEntry = '';

            const round = document.createElement('small');
            round.textContent =
                'Round ' + String(entry.round || 0);

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

            renderBattleLog(state.battle_log);
            renderFog(state.fog || {});

            const combatantStates =
                state.combatant_states || {};

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


    refreshTimer = window.setInterval(refresh, 5000);

    window.addEventListener('beforeunload', () => {
        if (refreshTimer) {
            window.clearInterval(refreshTimer);
        }
    });
}());
