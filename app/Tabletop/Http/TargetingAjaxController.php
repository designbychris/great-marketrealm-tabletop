<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Battlefield\Services\TargetingService;
use Throwable;

defined('ABSPATH') || exit;

final class TargetingAjaxController
{
    public function __construct(
        private TargetingService $targeting
    ) {}

    public function measure(): void
    {
        if (! is_user_logged_in()) {
            wp_send_json_error(
                ['message' => 'Authentication required.'],
                401
            );
        }

        check_ajax_referer(
            TabletopAjaxController::NONCE_ACTION,
            'nonce'
        );

        try {
            wp_send_json_success(
                $this->targeting->measure(
                    sanitize_text_field(
                        (string) ($_POST['table_id'] ?? '')
                    ),
                    get_current_user_id(),
                    sanitize_text_field(
                        (string) ($_POST['encounter_id'] ?? '')
                    ),
                    sanitize_text_field(
                        (string) ($_POST['target_token_id'] ?? '')
                    )
                )
            );
        } catch (Throwable $exception) {
            wp_send_json_error(
                ['message' => $exception->getMessage()],
                400
            );
        }
    }
}
