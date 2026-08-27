<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Cartography\Exceptions\CartographyDenied;
use GreatMarketrealmTabletop\Tabletop\Cartography\Services\CartographersTable;
use Throwable;

defined('ABSPATH') || exit;

final class CartographyAjaxController
{
    public function __construct(
        private CartographersTable $cartography
    ) {}

    public function replaceBattlemap(): void
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
            $image = $this->cartography->replaceActiveBattlemap(
                sanitize_text_field(
                    (string) ($_POST['table_id'] ?? '')
                ),
                get_current_user_id(),
                absint($_POST['attachment_id'] ?? 0)
            );

            wp_send_json_success([
                'battlemap' => $image->toArray(),
            ]);
        } catch (CartographyDenied $exception) {
            wp_send_json_error(
                ['message' => $exception->getMessage()],
                403
            );
        } catch (Throwable $exception) {
            wp_send_json_error(
                ['message' => $exception->getMessage()],
                400
            );
        }
    }
}
