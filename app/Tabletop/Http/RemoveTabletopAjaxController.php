<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Campaigns\WordPressTabletopRemover;
use Throwable;

defined('ABSPATH') || exit;

final class RemoveTabletopAjaxController
{
    public function __construct(private WordPressTabletopRemover $remover) {}

    public function remove(): void
    {
        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Authentication required.'], 401);
        }

        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');

        try {
            $this->remover->remove(
                sanitize_text_field(wp_unslash((string) ($_POST['table_id'] ?? ''))),
                get_current_user_id()
            );
            wp_send_json_success(['message' => 'Tabletop removed from Pippin\'s atlas.']);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 403);
        }
    }
}
