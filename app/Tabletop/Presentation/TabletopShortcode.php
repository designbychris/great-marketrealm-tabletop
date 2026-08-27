<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Presentation;

use GreatMarketrealmTabletop\Tabletop\Exceptions\TabletopAccessDenied;
use GreatMarketrealmTabletop\Tabletop\Services\TabletopChamber;
use Throwable;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;

defined('ABSPATH') || exit;

final class TabletopShortcode
{
    public const TAG = 'great_marketrealm_tabletop';

    public function __construct(
        private TabletopChamber $chamber,
        private TabletopChamberRenderer $renderer,
        private ?TableMembershipRepository $members = null
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
                'No Table is selected. Prepare a test Table to begin screen testing.',
                true
            );
        }

        try {
            $state = $this->chamber->state(
                $tableId,
                get_current_user_id()
            );

            return $this->renderer->render($state);
        } catch (TabletopAccessDenied $exception) {
            $member = $this->members?->find(
                $tableId,
                get_current_user_id()
            );

            if ($member?->status() === TableMemberStatus::INVITED) {
                return $this->renderer->render(
                    null,
                    null,
                    false,
                    [
                        'table_id' => $tableId,
                        'role' => $member->role(),
                        'status' => $member->status(),
                    ]
                );
            }

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
        if (is_user_logged_in()) {
            wp_enqueue_media();
        }

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
