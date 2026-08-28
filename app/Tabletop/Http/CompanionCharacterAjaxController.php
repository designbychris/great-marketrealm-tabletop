<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Integration\Companion\CompanionCharacterGateway;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Memberships\Services\TableGathering;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Services\TableTokenManager;
use Throwable;

final class CompanionCharacterAjaxController
{
    public function __construct(
        private CompanionCharacterGateway $companion,
        private TableGathering $gathering,
        private TableMembershipRepository $members,
        private TableSceneRepository $scenes,
        private TableTokenRepository $tokens,
        private TableTokenManager $tokenManager
    ) {}

    public function select(): void
    {
        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');
        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please sign in first.'], 401);
        }

        try {
            $tableId = sanitize_text_field((string) ($_POST['table_id'] ?? ''));
            $characterId = sanitize_text_field((string) ($_POST['character_id'] ?? ''));
            $userId = get_current_user_id();
            $member = $this->members->find($tableId, $userId);

            if ($member === null || $member->status() !== TableMemberStatus::ACTIVE) {
                throw new \RuntimeException('Only an active Table member may bring a Character to the Table.');
            }

            $character = $this->companion->characterForUser($userId, $characterId);
            if ($character === null) {
                throw new \RuntimeException('That Companion Character does not belong to your Guild account.');
            }

            $this->gathering->selectCompanionCharacter($tableId, $userId, $characterId);

            $token = null;
            foreach ($this->scenes->forTable($tableId) as $scene) {
                if (! $scene->isActive()) {
                    continue;
                }
                foreach ($this->tokens->forScene($tableId, $scene->id()) as $candidate) {
                    if ($candidate->sourceReference() === $characterId) {
                        $token = $candidate;
                        break 2;
                    }
                }
                $token = $this->tokenManager->place(
                    $tableId,
                    $scene->id(),
                    (string) ($character['name'] ?? 'Adventurer'),
                    TableTokenType::CHARACTER,
                    $characterId,
                    $userId,
                    0.12,
                    0.12
                );
                break;
            }

            wp_send_json_success([
                'message' => (string) ($character['name'] ?? 'Your Character') . ' has taken their place at the Table.',
                'character' => $character,
                'token' => $token?->toArray(),
            ]);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }
}
