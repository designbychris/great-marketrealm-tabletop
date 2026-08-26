<?php

use GreatMarketrealmTabletop\Tabletop\Models\TabletopChamberState;

defined('ABSPATH') || exit;

/** @var TabletopChamberState|null $state */
/** @var string|null $message */

$table = $state?->table() ?? [];
$viewer = $state?->viewer() ?? [];
$scene = $state?->scene();
$tokens = $state?->tokens() ?? [];
$members = $state?->members() ?? [];

$sceneImage = $scene !== null
    ? wp_get_attachment_image_url(
        (int) ($scene['map_attachment_id'] ?? 0),
        'full'
    )
    : false;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <?php wp_head(); ?>
</head>
<body class="gmrt-tabletop-page">
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

    <?php if ($message !== null) : ?>
        <section
            class="gmrt-chamber__notice"
            role="status"
        >
            <h2>The chamber awaits</h2>
            <p><?php echo esc_html($message); ?></p>
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
<?php wp_footer(); ?>
</body>
</html>
