<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\D20Roller;
use Throwable;

defined('ABSPATH') || exit;

final class KeeperSecretRollAjaxController
{
    public function __construct(
        private TableMembershipRepository $members,
        private D20Roller $roller
    ) {}

    public function roll(): void
    {
        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');

        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please sign in first.'], 401);
        }

        try {
            $tableId = sanitize_text_field((string) ($_POST['table_id'] ?? ''));
            if ($tableId === '') {
                throw new \RuntimeException('A Table ID is required.');
            }

            $member = $this->members->find($tableId, get_current_user_id());
            if (
                $member === null
                || ! $member->isDungeonMaster()
                || $member->status() !== TableMemberStatus::ACTIVE
            ) {
                throw new \RuntimeException(
                    'Only the active Dungeon Master may roll behind the screen.'
                );
            }

            $roll = $this->roller->roll();

            // Keeper-secret rolls are intentionally response-only. They are not
            // Chronicle events and are never projected into Living Table state.
            wp_send_json_success([
                'roll' => $roll,
                'message' => 'Secret d20: ' . $roll,
            ]);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }
}
