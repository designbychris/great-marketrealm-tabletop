<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Integration\Companion\CompanionCampaignBridge;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tabletop\Sessions\Contracts\TableSessionRepository;
use RuntimeException;
use Throwable;

final class CompanionCampaignAjaxController
{
    public function __construct(
        private TableRepository $tables,
        private TableSessionRepository $sessions,
        private CompanionCampaignBridge $companion
    ) {}

    public function link(): void
    {
        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Authentication required.'], 401);
        }
        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');

        try {
            $tableId = sanitize_text_field(wp_unslash((string) ($_POST['table_id'] ?? '')));
            $campaignId = sanitize_text_field(wp_unslash((string) ($_POST['campaign_id'] ?? '')));
            $userId = get_current_user_id();
            $table = $this->tables->find($tableId);

            if ($table === null || $table->dungeonMasterUserId() !== $userId) {
                throw new RuntimeException('Only this Tabletop\'s Dungeon Master may link its Companion Campaign.');
            }
            if ($campaignId === '') {
                throw new RuntimeException('Choose a Companion Campaign to link to this Tabletop.');
            }

            $result = $this->companion->link($tableId, $campaignId, $userId);
            if (empty($result['available'])) {
                throw new RuntimeException('The Great Marketrealm Companion campaign bridge is not available.');
            }
            if (empty($result['linked'])) {
                throw new RuntimeException((string) ($result['message'] ?? 'The Companion Campaign could not be linked.'));
            }

            $synced = 0;
            foreach ($this->sessions->forTable($tableId) as $session) {
                $sync = $this->companion->synchronise($session, $userId);
                if (! empty($sync['synchronised'])) {
                    $synced++;
                }
            }

            wp_send_json_success([
                'message' => (string) ($result['message'] ?? 'Companion Campaign linked.'),
                'campaign' => is_array($result['campaign'] ?? null) ? $result['campaign'] : [],
                'sessions_synchronised' => $synced,
            ]);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 422);
        }
    }
}
