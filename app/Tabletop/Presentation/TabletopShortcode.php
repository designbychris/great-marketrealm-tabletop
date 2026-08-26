<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Presentation;

use GreatMarketrealmTabletop\Tabletop\Exceptions\TabletopAccessDenied;
use GreatMarketrealmTabletop\Tabletop\Services\TabletopChamber;
use Throwable;

defined('ABSPATH') || exit;

final class TabletopShortcode
{
    public const TAG = 'great_marketrealm_tabletop';

    public function __construct(
        private TabletopChamber $chamber,
        private TabletopChamberRenderer $renderer
    ) {}

    /** @param array<string,mixed>|string $attributes */
    public function render(
        array|string $attributes = []
    ): string {
        $attributes = shortcode_atts(
            ['table' => ''],
            is_array($attributes) ? $attributes : [],
            self::TAG
        );

        $this->enqueueAssets();

        if (! is_user_logged_in()) {
            return $this->renderer->render(
                null,
                'Please sign in to enter the Tabletop Chamber.'
            );
        }

        $tableId = $this->tableId(
            (string) ($attributes['table'] ?? '')
        );

        if ($tableId === '') {
            return $this->renderer->render(
                null,
                'Choose an active Table to enter the Tabletop Chamber.'
            );
        }

        try {
            $state = $this->chamber->state(
                $tableId,
                get_current_user_id()
            );

            return $this->renderer->render($state);
        } catch (TabletopAccessDenied $exception) {
            return $this->renderer->render(
                null,
                $exception->getMessage()
            );
        } catch (Throwable $exception) {
            return $this->renderer->render(
                null,
                $exception->getMessage()
            );
        }
    }

    private function tableId(string $attribute): string
    {
        $attribute = sanitize_text_field($attribute);

        if ($attribute !== '') {
            return $attribute;
        }

        return sanitize_text_field(
            (string) (
                $_GET['table']
                ?? $_GET['gmrt_table']
                ?? ''
            )
        );
    }

    private function enqueueAssets(): void
    {
        wp_enqueue_style(
            'gmrt-tabletop',
            GMRT_URL . 'assets/css/tabletop.css',
            [],
            GMRT_VERSION
        );

        wp_enqueue_script(
            'gmrt-tabletop',
            GMRT_URL . 'assets/js/tabletop.js',
            [],
            GMRT_VERSION,
            true
        );

        wp_localize_script(
            'gmrt-tabletop',
            'gmrtTabletop',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce(
                    \GreatMarketrealmTabletop\Tabletop\Http\TabletopAjaxController::NONCE_ACTION
                ),
            ]
        );
    }
}
