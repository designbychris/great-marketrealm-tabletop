<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battlefield\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\CombatProfileRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackRollMode;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Contracts\CombatArsenalRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\AttackRangeResolver;
use GreatMarketrealmTabletop\Tabletop\Conditions\Contracts\ConditionRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Services\ConditionCombatRules;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use RuntimeException;

defined('ABSPATH') || exit;

final class TargetingService
{
    public function __construct(
        private EncounterRepository $encounters,
        private TableMembershipRepository $members,
        private TableTokenRepository $tokens,
        private TableSceneRepository $scenes,
        private CombatProfileRepository $profiles,
        private BattlefieldMeasure $battlefield,
        private AttackRangeResolver $ranges,
        private ConditionRepository $conditions,
        private ConditionCombatRules $conditionRules,
        private ?CombatArsenalRepository $arsenals = null
    ) {}

    /** @return array<string,mixed> */
    public function measure(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        string $targetTokenId,
        ?string $attackId = null
    ): array {
        $member = $this->members->find(
            $tableId,
            $viewerUserId
        );

        if ($member === null || ! $member->isActive()) {
            throw new RuntimeException(
                'Only an active Table member may measure a target.'
            );
        }

        $encounter = $this->encounters->find(
            $tableId,
            $encounterId
        );

        if ($encounter === null || $encounter->isEnded()) {
            throw new RuntimeException(
                'Targeting requires a current Encounter.'
            );
        }

        $combatant = $encounter->currentCombatant();

        if ($combatant === null) {
            throw new RuntimeException(
                'No combatant currently has the turn.'
            );
        }

        $attacker = $this->tokens->find(
            $tableId,
            $combatant->tokenId()
        );
        $target = $this->tokens->find(
            $tableId,
            $targetTokenId
        );

        if (
            $attacker === null
            || $target === null
            || $attacker->id() === $target->id()
        ) {
            throw new RuntimeException(
                'Choose another combatant as the target.'
            );
        }

        if ($attacker->sceneId() !== $target->sceneId()) {
            throw new RuntimeException(
                'The target is not on the same battlefield.'
            );
        }

        if (
            ! $member->isDungeonMaster()
            && ! $target->isVisible()
        ) {
            throw new RuntimeException(
                'That target is hidden from this Player.'
            );
        }

        $scene = $this->scenes->find(
            $tableId,
            $attacker->sceneId()
        );

        if ($scene === null || ! $scene->isActive()) {
            throw new RuntimeException(
                'The active battlefield could not be measured.'
            );
        }

        $distance = $this->battlefield->between(
            $scene,
            $attacker,
            $target
        );

        $selectedAttack = null;

        if ($attackId !== null && $attackId !== '' && $this->arsenals !== null) {
            $selectedAttack = $this->arsenals
                ->forToken($tableId, $attacker->id())
                ->find($attackId);
        }

        $profile = $selectedAttack?->combat()
            ?? $this->profiles->forToken(
                $tableId,
                $attacker->id()
            );

        $range = $this->ranges->assess(
            $distance->feet(),
            $profile
        );

        $factors = $this->conditionRules
            ->attackRollFactors(
                $this->conditions->forToken(
                    $tableId,
                    $attacker->id()
                ),
                $this->conditions->forToken(
                    $tableId,
                    $target->id()
                ),
                $distance->feet()
            );

        $rollMode = AttackRollMode::fromFactors(
            $factors['advantage'],
            $factors['disadvantage']
                || $range->longRange()
        );

        return [
            'attacker_token_id' => $attacker->id(),
            'target_token_id' => $target->id(),
            'attack_id' => $selectedAttack?->id(),
            'attack_name' => $selectedAttack?->name(),
            'distance' => $distance->toArray(),
            'range' => $range->toArray(),
            'roll_mode' => $rollMode,
        ];
    }
}
