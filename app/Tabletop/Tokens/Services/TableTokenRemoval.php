<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Tokens\Services;

use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tabletop\Light\Contracts\MagicalLightRepository;
use RuntimeException;

final class TableTokenRemoval
{
    public function __construct(
        private TableMembershipRepository $members,
        private TableTokenRepository $tokens,
        private EncounterRepository $encounters,
        private ?MagicalLightRepository $magicalLights = null
    ) {}

    public function remove(string $tableId, int $userId, string $tokenId): TableToken
    {
        $member = $this->members->find($tableId, $userId);

        if ($member === null || $member->status() !== TableMemberStatus::ACTIVE) {
            throw new RuntimeException('Only an active Table member may remove a token from the Chamber.');
        }

        $token = $this->tokens->find($tableId, $tokenId);

        if ($token === null) {
            throw new RuntimeException('That token is no longer present in this Chamber.');
        }

        if (! $member->isDungeonMaster()) {
            $ownsCharacter = $token->type() === TableTokenType::CHARACTER
                && $token->controllerUserId() === $userId;

            if (! $ownsCharacter) {
                throw new RuntimeException('Players may only remove their own Companion Character token.');
            }
        }

        $encounter = $this->encounters->currentForScene($tableId, $token->sceneId());

        if ($encounter !== null && $encounter->removeCombatant($tokenId)) {
            $this->encounters->save($encounter);
        }

        $this->magicalLights?->deleteForToken($tableId, $token->sceneId(), $tokenId);
        $this->tokens->delete($tableId, $tokenId);

        return $token;
    }
}
