<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Sessions\Exceptions\SessionControlDenied;
use GreatMarketrealmTabletop\Tabletop\Sessions\Services\TableSessionManager;
use GreatMarketrealmTabletop\Integration\Companion\CompanionCampaignBridge;
use Throwable;

defined('ABSPATH') || exit;

final class TableSessionAjaxController
{
    public function __construct(
        private TableSessionManager $sessions,
        private CompanionCampaignBridge $companion
    ) {}

    public function start(): void
    {
        $this->guard();
        $this->respond(fn () => $this->sessions->start(
            $this->tableId(),
            get_current_user_id(),
            sanitize_text_field(wp_unslash((string) ($_POST['title'] ?? '')))
        ));
    }

    public function end(): void
    {
        $this->guard();
        $this->respond(fn () => $this->sessions->end(
            $this->tableId(),
            get_current_user_id()
        ));
    }

    private function respond(callable $callback): void
    {
        try {
            $session = $callback();
            wp_send_json_success([
                'session' => $session->toArray(),
                'companion' => $this->companion->synchronise($session, get_current_user_id()),
            ]);
        } catch (SessionControlDenied $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 403);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 422);
        }
    }

    private function guard(): void
    {
        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Authentication required.'], 401);
        }
        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');
    }

    private function tableId(): string
    {
        return sanitize_text_field(wp_unslash((string) ($_POST['table_id'] ?? '')));
    }
}
