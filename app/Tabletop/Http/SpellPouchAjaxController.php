<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Integration\Companion\CompanionCharacterGateway;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tabletop\Satchel\Services\SpellPouchRoller;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Services\TableChronicleRecorder;
use Throwable;

final class SpellPouchAjaxController
{
    public function __construct(
        private CompanionCharacterGateway $companion,
        private TableMembershipRepository $members,
        private SpellPouchRoller $spells,
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
            $action = sanitize_key((string) ($_POST['spell_action'] ?? ''));
            $spellId = sanitize_key((string) ($_POST['spell_id'] ?? ''));
            $userId = get_current_user_id();
            $member = $this->members->find($tableId, $userId);

            if ($member === null || $member->status() !== TableMemberStatus::ACTIVE) {
                throw new \RuntimeException('Only an active Table member may reach into the Spell Pouch.');
            }

            $characterId = $member->companionCharacterId();
            if ($characterId === null) {
                throw new \RuntimeException('Bring a Companion Character to the Table before invoking magic.');
            }

            $character = $this->companion->characterForUser($userId, $characterId);
            if ($character === null) {
                throw new \RuntimeException('Your seated Companion Character could not be verified.');
            }

            $roll = $this->spells->roll($character, $action, $spellId);
            $name = (string) ($character['name'] ?? 'Adventurer');
            $modifier = (int) ($roll['modifier'] ?? 0);
            $sign = $modifier >= 0 ? '+' : '';

            if ($roll['action'] === 'attack') {
                $message = sprintf(
                    '%s casts %s: %d %s%d = %d to hit',
                    $name,
                    $roll['label'],
                    $roll['die'],
                    $sign,
                    $modifier,
                    $roll['total']
                );
            } elseif ($roll['action'] === 'healing') {
                $message = sprintf(
                    '%s channels %s: %s %s%d = %d HP restored',
                    $name,
                    $roll['label'],
                    implode(' + ', array_map('strval', $roll['rolls'])),
                    $sign,
                    $modifier,
                    $roll['total']
                );
            } else {
                $damageType = trim((string) ($roll['damage_type'] ?? ''));
                $message = sprintf(
                    '%s casts %s for damage: %s %s%d = %d%s',
                    $name,
                    $roll['label'],
                    implode(' + ', array_map('strval', $roll['rolls'])),
                    $sign,
                    $modifier,
                    $roll['total'],
                    $damageType === '' ? '' : ' ' . $damageType
                );
            }

            $this->chronicle->recordSatchelRoll($tableId, $userId, $character, 'spell-pouch', (string) $roll['action'], $message, $roll);

            wp_send_json_success(['roll' => $roll, 'message' => $message]);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }
}
