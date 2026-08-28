<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Chronicle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\BattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Contracts\ChamberChronicleRepository;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Models\ChamberChronicleEvent;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;

defined('ABSPATH') || exit;

final class TableChronicleRecorder
{
    public function __construct(
        private TableSceneRepository $scenes,
        private EncounterRepository $encounters,
        private TableTokenRepository $tokens,
        private BattleEventRepository $battleEvents,
        private ChamberChronicleRepository $chamberEvents,
        private TableClock $clock
    ) {}

    /** @param array<string,mixed> $character @param array<string,mixed> $roll */
    public function recordSatchelRoll(
        string $tableId,
        int $userId,
        array $character,
        string $kind,
        string $action,
        string $summary,
        array $roll
    ): void {
        $characterId = trim((string) ($character['id'] ?? ''));
        $characterName = trim((string) ($character['name'] ?? 'Adventurer'));
        if ($tableId === '' || $userId < 1 || $characterId === '' || trim($summary) === '') {
            return;
        }

        $activeScene = null;
        foreach ($this->scenes->forTable($tableId) as $scene) {
            if ($scene->isActive()) {
                $activeScene = $scene;
                break;
            }
        }

        $encounter = $activeScene !== null
            ? $this->encounters->currentForScene($tableId, $activeScene->id())
            : null;

        if ($encounter !== null) {
            $tokenId = 'companion-character:' . $characterId;
            if ($activeScene !== null) {
                foreach ($this->tokens->forScene($tableId, $activeScene->id()) as $token) {
                    if ($token->controllerUserId() === $userId && $token->sourceReference() === $characterId) {
                        $tokenId = $token->id();
                        break;
                    }
                }
            }

            $this->battleEvents->append(new BattleEvent(
                bin2hex(random_bytes(12)),
                $tableId,
                $encounter->id(),
                'satchel-roll',
                $tokenId,
                $encounter->round(),
                $encounter->turnIndex(),
                $this->clock->now(),
                [
                    'kind' => $kind,
                    'action' => $action,
                    'character_id' => $characterId,
                    'character_name' => $characterName,
                    'summary' => $summary,
                    'roll' => $roll,
                ]
            ));
            return;
        }

        $this->chamberEvents->append(new ChamberChronicleEvent(
            bin2hex(random_bytes(12)),
            $tableId,
            $userId,
            $characterId,
            $characterName,
            $kind,
            $action,
            $summary,
            $this->clock->now(),
            ['roll' => $roll]
        ));
    }
}
