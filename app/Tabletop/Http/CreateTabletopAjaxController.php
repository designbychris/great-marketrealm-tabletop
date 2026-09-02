<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Campaigns\TabletopCreator;
use GreatMarketrealmTabletop\Tabletop\Campaigns\WordPressDungeonMasterPolicy;
use Throwable;

defined('ABSPATH') || exit;

final class CreateTabletopAjaxController
{
    public function __construct(
        private TabletopCreator $creator,
        private WordPressDungeonMasterPolicy $policy
    ) {}

    public function create(): void
    {
        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Authentication required.'], 401);
        }

        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');
        $userId = get_current_user_id();

        if (! $this->policy->mayCreate($userId)) {
            wp_send_json_error(['message' => 'Only a Dungeon Master may create a Tabletop.'], 403);
        }

        try {
            $table = $this->creator->create(
                $userId,
                sanitize_text_field(wp_unslash((string) ($_POST['name'] ?? ''))),
                sanitize_textarea_field(wp_unslash((string) ($_POST['description'] ?? '')))
            );
            wp_send_json_success(['table_id' => $table->id()]);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }
}
