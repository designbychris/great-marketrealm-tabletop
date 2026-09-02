<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Integration\Companion\CompanionCharacterGateway;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tabletop\Satchel\Services\QuickHandsRoller;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Services\TableChronicleRecorder;
use Throwable;

final class QuickHandsAjaxController
{
    public function __construct(
        private CompanionCharacterGateway $companion,
        private TableMembershipRepository $members,
        private QuickHandsRoller $quickHands,
        private TableChronicleRecorder $chronicle
    ) {}

    public function roll(): void
    {
        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Authentication required.'], 401);
        }
        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');

        try {
            $tableId = sanitize_text_field((string) ($_POST['table_id'] ?? ''));
            $kind = sanitize_key((string) ($_POST['kind'] ?? ''));
            $key = sanitize_key((string) ($_POST['key'] ?? ''));
            $userId = get_current_user_id();
            $member = $this->members->find($tableId, $userId);

            if ($member === null || $member->status() !== TableMemberStatus::ACTIVE) {
                throw new \RuntimeException('Only an active Table member may use Quick Hands.');
            }
            $characterId = $member->companionCharacterId();
            if ($characterId === null) {
                throw new \RuntimeException('Bring a Companion Character to the Table before rolling.');
            }
            $character = $this->companion->characterForUser($userId, $characterId);
            if ($character === null) {
                throw new \RuntimeException('Your seated Companion Character could not be verified.');
            }

            $roll = $this->quickHands->roll($character, $kind, $key);
            $name = (string) ($character['name'] ?? 'Adventurer');
            $sign = ((int) $roll['modifier']) >= 0 ? '+' : '';
            $message = sprintf('%s rolls %s: %d %s%d = %d', $name, $roll['label'], $roll['die'], $sign, $roll['modifier'], $roll['total']);
            $this->chronicle->recordSatchelRoll($tableId, $userId, $character, 'quick-hands', (string) $roll['kind'], $message, $roll, sanitize_text_field((string) ($_POST['encounter_id'] ?? '')));
            wp_send_json_success(['roll' => $roll, 'message' => $message]);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }
}
