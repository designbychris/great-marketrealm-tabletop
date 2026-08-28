<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Tokens\Services\TableTokenRemoval;
use Throwable;

final class TableTokenRemovalAjaxController
{
    public function __construct(private TableTokenRemoval $removal) {}

    public function remove(): void
    {
        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');

        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please sign in first.'], 401);
        }

        try {
            $tableId = sanitize_text_field((string) ($_POST['table_id'] ?? ''));
            $tokenId = sanitize_text_field((string) ($_POST['token_id'] ?? ''));

            if ($tableId === '' || $tokenId === '') {
                throw new \RuntimeException('A Table and token are required.');
            }

            $token = $this->removal->remove($tableId, get_current_user_id(), $tokenId);

            wp_send_json_success([
                'message' => $token->label() . ' has left the Chamber.',
                'token_id' => $token->id(),
            ]);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }
}
