<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Integration\Companion\CompanionCharacterGateway;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use Throwable;

final class AdventuringMeasuresAjaxController
{
    public function __construct(
        private CompanionCharacterGateway $companion,
        private TableMembershipRepository $members
    ) {}

    public function update(): void
    {
        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Authentication required.'], 401);
        }
        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');

        try {
            $tableId = sanitize_text_field((string) ($_POST['table_id'] ?? ''));
            $currentHp = filter_var($_POST['current_hp'] ?? null, FILTER_VALIDATE_INT);
            $temporaryHp = filter_var($_POST['temporary_hp'] ?? null, FILTER_VALIDATE_INT);
            if ($currentHp === false || $temporaryHp === false) {
                throw new \RuntimeException('Adventuring Measures must be whole numbers.');
            }

            $userId = get_current_user_id();
            $member = $this->members->find($tableId, $userId);
            if ($member === null || $member->status() !== TableMemberStatus::ACTIVE) {
                throw new \RuntimeException('Only an active Table member may update Adventuring Measures.');
            }
            $characterId = $member->companionCharacterId();
            if ($characterId === null) {
                throw new \RuntimeException('Bring a Companion Character to the Table first.');
            }

            $character = $this->companion->updateVitalMeasuresForUser(
                $userId, $characterId, (int) $currentHp, (int) $temporaryHp
            );
            if ($character === null) {
                throw new \RuntimeException('Those Adventuring Measures could not be certified by the Companion.');
            }
            $hp = is_array($character['play']['hit_points'] ?? null) ? $character['play']['hit_points'] : [];
            wp_send_json_success([
                'hit_points' => $hp,
                'message' => 'Adventuring Measures updated.',
            ]);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }
}
