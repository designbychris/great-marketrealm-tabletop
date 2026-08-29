<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Fog\Services\FogOfWarManager;
use Throwable;

defined('ABSPATH') || exit;

final class FogOfWarAjaxController
{
    public function __construct(
        private FogOfWarManager $fog
    ) {}

    public function configure(): void
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
            $state = $this->fog->configure(
                sanitize_text_field(
                    (string) ($_POST['table_id'] ?? '')
                ),
                get_current_user_id(),
                filter_var(
                    $_POST['enabled'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                ),
                filter_var(
                    $_POST['clear'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                ),
                sanitize_text_field((string) ($_POST['scene_id'] ?? ''))
            );

            wp_send_json_success([
                'fog' => $state,
            ]);
        } catch (Throwable $exception) {
            wp_send_json_error(
                ['message' => $exception->getMessage()],
                403
            );
        }
    }
}
