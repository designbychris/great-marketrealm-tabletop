<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Cartography\Services;

use GreatMarketrealmTabletop\Tabletop\Cartography\Contracts\DungeonForgeRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;

 defined('ABSPATH') || exit;

/**
 * Bridges a forged Boss Lair occupant into the ordinary Encounter lifecycle.
 *
 * The Forge owns preparation metadata; the Encounter owns battle state. This
 * service deliberately does not create a second boss-combat model. It only
 * reveals the exact forged occupant when the Keeper has chosen that token as
 * a combatant in a newly-started Encounter.
 */
final class LairEncounterParticipant
{
    public function __construct(
        private DungeonForgeRepository $forge,
        private TableTokenRepository $tokens
    ) {}

    /**
     * @param array<int,string> $combatantTokenIds
     */
    public function awakenIfParticipating(
        string $tableId,
        string $sceneId,
        array $combatantTokenIds
    ): ?string {
        $plan = $this->forge->forScene($tableId, $sceneId);
        if (! is_array($plan)) {
            return null;
        }

        $chosen = array_fill_keys(array_map('strval', $combatantTokenIds), true);
        $participants = [];
        $bossTokenId = trim((string) ($plan['lair_occupant_token_id'] ?? ''));
        $bossCreatureId = trim((string) ($plan['lair_occupant_id'] ?? ''));
        if ($bossTokenId !== '' && $bossCreatureId !== '') {
            $participants[] = ['token_id' => $bossTokenId, 'creature_id' => $bossCreatureId];
        }
        foreach (is_array($plan['forge_occupants'] ?? null) ? $plan['forge_occupants'] : [] as $occupant) {
            if (! is_array($occupant)) continue;
            $participants[] = [
                'token_id' => trim((string) ($occupant['token_id'] ?? '')),
                'creature_id' => trim((string) ($occupant['creature_id'] ?? '')),
            ];
        }

        $awakened = null;
        foreach ($participants as $participant) {
            $tokenId = $participant['token_id']; $creatureId = $participant['creature_id'];
            if ($tokenId === '' || $creatureId === '' || ! isset($chosen[$tokenId])) continue;
            $token = $this->tokens->find($tableId, $tokenId);
            if ($token === null || $token->sceneId() !== $sceneId
                || (string) ($token->sourceReference() ?? '') !== 'gmrt-bestiary:' . $creatureId) continue;
            $token->show();
            $this->tokens->save($token);
            $awakened ??= $token->id();
        }
        return $awakened;
    }
}
