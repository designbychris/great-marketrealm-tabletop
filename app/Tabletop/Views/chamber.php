<?php

use GreatMarketrealmTabletop\Tabletop\Models\TabletopChamberState;

defined('ABSPATH') || exit;

/** @var TabletopChamberState|null $state */
/** @var string|null $message */
/** @var bool $canPrepareTestTable */

$table = $state?->table() ?? [];
$viewer = $state?->viewer() ?? [];
$scene = $state?->scene();
$tokens = $state?->tokens() ?? [];
$members = $state?->members() ?? [];
$encounter = $state?->encounter();
$vitality = $state?->vitality() ?? [];
$deathSaves = $state?->deathSaves() ?? [];

$tokenLabels = [];
foreach ($tokens as $token) {
    $tokenId = (string) ($token['id'] ?? '');
    if ($tokenId !== '') {
        $tokenLabels[$tokenId] = (string) (
            $token['label'] ?? 'Unknown combatant'
        );
    }
}

$sceneImage = $scene !== null
    ? wp_get_attachment_image_url(
        (int) ($scene['map_attachment_id'] ?? 0),
        'full'
    )
    : false;
?>
<main
    class="gmrt-chamber"
    id="main-content"
    data-table-id="<?php echo esc_attr(
        (string) ($table['id'] ?? '')
    ); ?>"
    data-viewer-role="<?php echo esc_attr(
        (string) ($viewer['role'] ?? '')
    ); ?>"
>
    <header class="gmrt-chamber__masthead">
        <div>
            <p class="gmrt-chamber__eyebrow">
                Great Marketrealm Tabletop
            </p>
            <h1>
                <?php echo esc_html(
                    (string) (
                        $table['name']
                        ?? 'The Tabletop Chamber'
                    )
                ); ?>
            </h1>
        </div>

        <?php if ($state !== null) : ?>
            <div class="gmrt-chamber__viewer">
                <span>
                    <?php echo esc_html(
                        $state->isDungeonMaster()
                            ? 'Dungeon Master'
                            : 'Adventurer'
                    ); ?>
                </span>
                <small>
                    Table <?php echo esc_html(
                        (string) (
                            $table['status']
                            ?? 'unknown'
                        )
                    ); ?>
                </small>
            </div>
        <?php endif; ?>
    </header>


    <?php if ($encounter !== null) : ?>
        <section
            class="gmrt-encounter-strip"
            aria-labelledby="gmrt-encounter-title"
            data-encounter-id="<?php echo esc_attr(
                (string) $encounter['id']
            ); ?>"
            data-encounter-revision="<?php echo esc_attr(
                (string) ($encounter['revision'] ?? 1)
            ); ?>"
        >
            <div>
                <p class="gmrt-chamber__eyebrow">
                    The Turn of Battle
                </p>
                <h2 id="gmrt-encounter-title">
                    <?php echo esc_html(
                        (string) $encounter['name']
                    ); ?>
                </h2>
            </div>

            <div class="gmrt-encounter-strip__state">
                <strong>
                    <?php echo esc_html(
                        strtoupper((string) $encounter['status'])
                    ); ?>
                </strong>

                <?php if ((int) ($encounter['round'] ?? 0) > 0) : ?>
                    <span>
                        Round <?php echo esc_html(
                            (string) $encounter['round']
                        ); ?>
                    </span>
                <?php endif; ?>

                <?php if (! empty($encounter['current_token_id'])) : ?>
                    <?php
                    $turnTokenId = (string) $encounter['current_token_id'];
                    $turnLabel = $tokenLabels[$turnTokenId]
                        ?? 'Unknown combatant';
                    ?>
                    <span class="gmrt-current-turn" data-current-turn-label>
                        Turn:
                        <strong><?php echo esc_html($turnLabel); ?></strong>
                    </span>
                <?php endif; ?>

                <?php if (
                    $state !== null
                    && $state->isDungeonMaster()
                    && ($encounter['status'] ?? '') === 'active'
                ) : ?>
                    <button
                        type="button"
                        class="gmrt-end-turn"
                        data-end-turn
                    >
                        End Turn ▶
                    </button>
                <?php endif; ?>
            </div>

        <?php if (
            ($encounter['status'] ?? '') === 'active'
            && ! empty($encounter['current_token_id'])
        ) : ?>
            <?php
            $currentTokenId = (string) (
                $encounter['current_token_id']
                ?? ''
            );
            $currentVitality = $vitality[$currentTokenId] ?? null;
            $currentDeathSaves = $deathSaves[$currentTokenId] ?? null;
            ?>
            <?php if (
                is_array($currentVitality)
                && (int) $currentVitality['current_hp'] === 0
            ) : ?>
                <div class="gmrt-death-saves" data-death-saves>
                    <strong>DOWN</strong>
                    <?php if (
                        is_array($currentDeathSaves)
                        && ! empty($currentDeathSaves['dead'])
                    ) : ?>
                        <span>Fallen</span>
                    <?php elseif (
                        is_array($currentDeathSaves)
                        && ! empty($currentDeathSaves['stable'])
                    ) : ?>
                        <span>Stable</span>
                    <?php else : ?>
                        <span>
                            Saves <?php echo esc_html(
                                (string) ($currentDeathSaves['successes'] ?? 0)
                            ); ?>/3
                            · Failures <?php echo esc_html(
                                (string) ($currentDeathSaves['failures'] ?? 0)
                            ); ?>/3
                        </span>
                        <button type="button" data-roll-death-save>
                            Roll Death Save
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div
                class="gmrt-deeds"
                aria-label="Battle deeds"
                data-current-token="<?php echo esc_attr(
                    (string) $encounter['current_token_id']
                ); ?>"
            >
                <label class="gmrt-deeds__target">
                    <span>Target</span>
                    <select data-attack-target>
                        <option value="">Choose target…</option>
                        <?php foreach ($tokens as $targetToken) :
                            if (
                                ($targetToken['id'] ?? '')
                                === ($encounter['current_token_id'] ?? '')
                            ) {
                                continue;
                            }
                            ?>
                            <option
                                value="<?php echo esc_attr(
                                    (string) ($targetToken['id'] ?? '')
                                ); ?>"
                            >
                                <?php echo esc_html(
                                    (string) ($targetToken['label'] ?? 'Token')
                                ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <?php foreach (
                    ['attack' => 'Attack', 'dash' => 'Dash',
                     'disengage' => 'Disengage', 'dodge' => 'Dodge',
                     'help' => 'Help']
                    as $deedKey => $deedLabel
                ) : ?>
                    <button
                        type="button"
                        class="gmrt-deed"
                        data-battle-deed="<?php echo esc_attr(
                            $deedKey
                        ); ?>"
                    >
                        <?php echo esc_html($deedLabel); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if ($message !== null) : ?>
        <section
            class="gmrt-chamber__notice"
            role="status"
        >
            <h2>The chamber awaits</h2>
            <p><?php echo esc_html($message); ?></p>
            <?php if (! empty($canPrepareTestTable)) : ?>
                <button type="button" class="gmrt-test-table-button" data-prepare-test-table>
                    Prepare Test Table
                </button>
                <small class="gmrt-test-table-note">
                    Creates Sage's Combat Testing Grounds with Auby,
                    a Training Slime and other test combatants.
                </small>
            <?php endif; ?>
        </section>
    <?php elseif ($scene === null) : ?>
        <section
            class="gmrt-chamber__notice"
            role="status"
        >
            <h2>No active Scene</h2>
            <p>
                This Table has gathered, but the Dungeon Master
                has not opened a battlemap Scene yet.
            </p>
        </section>
    <?php else : ?>
        <div class="gmrt-chamber__layout">
            <section
                class="gmrt-board"
                aria-labelledby="gmrt-scene-title"
            >
                <div class="gmrt-board__heading">
                    <div>
                        <p class="gmrt-chamber__eyebrow">
                            Active Scene
                        </p>
                        <h2 id="gmrt-scene-title">
                            <?php echo esc_html(
                                (string) $scene['name']
                            ); ?>
                        </h2>
                    </div>

                    <span>
                        <?php echo esc_html(
                            (string) $scene['grid_type']
                        ); ?>
                        <?php if (
                            ($scene['grid_type'] ?? '')
                            === 'square'
                        ) : ?>
                            · <?php echo esc_html(
                                (string) $scene['grid_size']
                            ); ?> px grid
                        <?php endif; ?>
                    </span>
                </div>

                <div
                    class="gmrt-board__viewport"
                    data-grid-type="<?php echo esc_attr(
                        (string) $scene['grid_type']
                    ); ?>"
                    style="--gmrt-grid-size: <?php echo esc_attr(
                        (string) max(
                            1,
                            (int) (
                                $scene['grid_size']
                                ?? 1
                            )
                        )
                    ); ?>px;"
                >
                    <?php if ($sceneImage !== false) : ?>
                        <img
                            class="gmrt-board__map"
                            src="<?php echo esc_url(
                                $sceneImage
                            ); ?>"
                            alt=""
                            width="<?php echo esc_attr(
                                (string) $scene['width']
                            ); ?>"
                            height="<?php echo esc_attr(
                                (string) $scene['height']
                            ); ?>"
                        >
                    <?php else : ?>
                        <div
                            class="gmrt-board__missing-map"
                            role="status"
                        >
                            Battlemap artwork is unavailable.
                        </div>
                    <?php endif; ?>

                    <?php if (
                        ($scene['grid_type'] ?? '')
                        === 'square'
                    ) : ?>
                        <div
                            class="gmrt-board__grid"
                            aria-hidden="true"
                        ></div>
                    <?php endif; ?>

                    <div
                        class="gmrt-board__tokens"
                        aria-label="Table tokens"
                    >
                        <?php foreach ($tokens as $token) :
                            $left = max(
                                0,
                                min(
                                    100,
                                    (float) (
                                        $token['x']
                                        ?? 0
                                    ) * 100
                                )
                            );
                            $top = max(
                                0,
                                min(
                                    100,
                                    (float) (
                                        $token['y']
                                        ?? 0
                                    ) * 100
                                )
                            );
                            $width = max(
                                1,
                                (float) (
                                    $token['width_units']
                                    ?? 1
                                )
                            );
                            $height = max(
                                1,
                                (float) (
                                    $token['height_units']
                                    ?? 1
                                )
                            );
                            ?>
                            <div
                                class="gmrt-token gmrt-token--<?php echo esc_attr(
                                    (string) $token['type']
                                ); ?><?php echo (
                                    ($token['visibility'] ?? '')
                                    === 'hidden'
                                ) ? ' is-hidden-token' : ''; ?>"
                                style="
                                    --gmrt-token-x: <?php echo esc_attr(
                                        (string) $left
                                    ); ?>%;
                                    --gmrt-token-y: <?php echo esc_attr(
                                        (string) $top
                                    ); ?>%;
                                    --gmrt-token-width: <?php echo esc_attr(
                                        (string) $width
                                    ); ?>;
                                    --gmrt-token-height: <?php echo esc_attr(
                                        (string) $height
                                    ); ?>;
                                "
                                title="<?php echo esc_attr(
                                    (string) $token['label']
                                ); ?>"
                                data-token-id="<?php echo esc_attr(
                                    (string) $token['id']
                                ); ?>"
                                data-token-revision="<?php echo esc_attr(
                                    (string) ($token['revision'] ?? 1)
                                ); ?>"
                                data-token-controller="<?php echo esc_attr(
                                    (string) ($token['controller_user_id'] ?? '')
                                ); ?>"
                                tabindex="0"
                                role="button"
                                aria-label="<?php echo esc_attr(
                                    'Select token: '
                                    . (string) $token['label']
                                ); ?>"
                            >
                                <span aria-hidden="true">
                                    <?php echo esc_html(
                                        strtoupper(
                                            substr(
                                                (string) $token['label'],
                                                0,
                                                1
                                            )
                                        )
                                    ); ?>
                                </span>
                                <span class="screen-reader-text">
                                    <?php echo esc_html(
                                        (string) $token['label']
                                    ); ?>
                                    <?php if (
                                        ($token['visibility'] ?? '')
                                        === 'hidden'
                                    ) : ?>
                                        — hidden from players
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <aside
                class="gmrt-party"
                aria-labelledby="gmrt-party-title"
            >
                <p class="gmrt-chamber__eyebrow">
                    The Gathering
                </p>
                <h2 id="gmrt-party-title">
                    Adventurers at the Table
                </h2>

                <ul>
                    <?php foreach ($members as $member) : ?>
                        <li>
                            <span>
                                <?php echo esc_html(
                                    ($member['role'] ?? '')
                                    === 'dungeon-master'
                                        ? 'DM'
                                        : 'Player'
                                ); ?>
                            </span>
                            <strong>
                                User #<?php echo esc_html(
                                    (string) (
                                        $member['user_id']
                                        ?? ''
                                    )
                                ); ?>
                            </strong>
                            <small>
                                <?php echo esc_html(
                                    (string) (
                                        $member['status']
                                        ?? ''
                                    )
                                ); ?>
                            </small>
                            <?php
                            $memberCharacter = (string) (
                                $member['companion_character_id']
                                ?? ''
                            );
                            $memberVitality = null;

                            foreach ($tokens as $partyToken) {
                                if (
                                    $memberCharacter !== ''
                                    && (string) (
                                        $partyToken['source_reference']
                                        ?? ''
                                    ) === $memberCharacter
                                ) {
                                    $partyTokenId = (string) (
                                        $partyToken['id']
                                        ?? ''
                                    );
                                    $memberVitality = $vitality[
                                        $partyTokenId
                                    ] ?? null;
                                    break;
                                }
                            }
                            ?>
                            <?php if (is_array($memberVitality)) : ?>
                                <div
                                    class="gmrt-hp"
                                    aria-label="<?php echo esc_attr(
                                        'Hit Points '
                                        . (string) $memberVitality['current_hp']
                                        . ' of '
                                        . (string) $memberVitality['maximum_hp']
                                    ); ?>"
                                >
                                    <div class="gmrt-hp__track">
                                        <span
                                            class="gmrt-hp__fill"
                                            style="--gmrt-hp: <?php echo esc_attr(
                                                (string) $memberVitality['percentage']
                                            ); ?>%;"
                                        ></span>
                                    </div>
                                    <small>
                                        HP <?php echo esc_html(
                                            (string) $memberVitality['current_hp']
                                        ); ?>/<?php echo esc_html(
                                            (string) $memberVitality['maximum_hp']
                                        ); ?>
                                        <?php if (
                                            (int) $memberVitality['temporary_hp'] > 0
                                        ) : ?>
                                            +<?php echo esc_html(
                                                (string) $memberVitality['temporary_hp']
                                            ); ?> temp
                                        <?php endif; ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="gmrt-party__note">
                    <strong>
                        Chamber mode
                    </strong>
                    <p>
                        Select a token and use the arrow keys,
                        or click a position on the battlemap.
                        Movement is validated and persisted by
                        the Tabletop server.
                    </p>
                </div>
            </aside>
        </div>
    <?php endif; ?>

    <div
        class="gmrt-chamber__status"
        id="gmrt-tabletop-status"
        role="status"
        aria-live="polite"
    ></div>
</main>

