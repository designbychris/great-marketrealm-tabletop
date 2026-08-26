<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Conditions\Exceptions\ConditionControlDenied;
use GreatMarketrealmTabletop\Tabletop\Conditions\Services\ConditionManager;
use Throwable;

defined('ABSPATH') || exit;

final class ConditionAjaxController
{
    public function __construct(
        private ConditionManager $conditions
    ) {}

    public function apply(): void
    {
        $this->guard();

        try {
            $duration = (int) ($_POST['turns_remaining'] ?? 0);
            $condition = $this->conditions->apply(
                $this->tableId(),
                get_current_user_id(),
                $this->encounterId(),
                $this->tokenId(),
                sanitize_key(
                    (string) ($_POST['condition'] ?? '')
                ),
                $duration > 0 ? $duration : null
            );

            wp_send_json_success([
                'condition' => $condition->toArray(),
            ]);
        } catch (ConditionControlDenied $exception) {
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

    public function remove(): void
    {
        $this->guard();

        try {
            $this->conditions->remove(
                $this->tableId(),
                get_current_user_id(),
                $this->encounterId(),
                $this->tokenId(),
                sanitize_key(
                    (string) ($_POST['condition'] ?? '')
                )
            );

            wp_send_json_success(['removed' => true]);
        } catch (ConditionControlDenied $exception) {
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

    private function guard(): void
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
    }

    private function tableId(): string
    {
        return sanitize_text_field(
            (string) ($_POST['table_id'] ?? '')
        );
    }

    private function encounterId(): string
    {
        return sanitize_text_field(
            (string) ($_POST['encounter_id'] ?? '')
        );
    }

    private function tokenId(): string
    {
        return sanitize_text_field(
            (string) ($_POST['token_id'] ?? '')
        );
    }
}
