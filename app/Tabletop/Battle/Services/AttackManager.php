<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\BattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\CombatProfileRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Exceptions\AttackDenied;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleDeed;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use RuntimeException;

defined('ABSPATH') || exit;

final class AttackManager
{
    public function __construct(
        private BattleDeedManager $deeds,
        private EncounterRepository $encounters,
        private TableMembershipRepository $members,
        private TableTokenRepository $tokens,
        private CombatProfileRepository $profiles,
        private BattleEventRepository $events,
        private AttackResolver $resolver,
        private TableClock $clock
    ) {}

    /** @return array<string,mixed> */
    public function attack(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        string $targetTokenId,
        int $expectedRevision
    ): array {
        $encounter = $this->encounters->find(
            $tableId,
            $encounterId
        );

        if ($encounter === null) {
            throw new RuntimeException(
                'The requested Encounter could not be found.'
            );
        }

        $combatant = $encounter->currentCombatant();

        if ($combatant === null) {
            throw new AttackDenied(
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

        if ($attacker === null || $target === null) {
            throw new AttackDenied(
                'Both attacker and target must exist at this Table.'
            );
        }

        if ($attacker->id() === $target->id()) {
            throw new AttackDenied(
                'A combatant cannot target itself with this attack.'
            );
        }

        if ($attacker->sceneId() !== $target->sceneId()) {
            throw new AttackDenied(
                'Attack targets must share the active Scene.'
            );
        }

        $member = $this->members->find(
            $tableId,
            $viewerUserId
        );

        if (
            $member === null
            || ! $member->isActive()
        ) {
            throw new AttackDenied(
                'Only an active Table member may attack.'
            );
        }

        if (
            ! $member->isDungeonMaster()
            && ! $target->isVisible()
        ) {
            throw new AttackDenied(
                'That target is hidden from this Player.'
            );
        }

        $attackerProfile = $this->profiles->forToken(
            $tableId,
            $attacker->id()
        );
        $targetProfile = $this->profiles->forToken(
            $tableId,
            $target->id()
        );

        $deed = $this->deeds->perform(
            $tableId,
            $viewerUserId,
            $encounterId,
            BattleDeed::ATTACK,
            $expectedRevision
        );

        $outcome = $this->resolver->resolve(
            $attackerProfile,
            $targetProfile
        );

        $updatedEncounter = $deed['encounter'];
        $event = new BattleEvent(
            bin2hex(random_bytes(12)),
            $tableId,
            $encounterId,
            'attack-resolved',
            $attacker->id(),
            $updatedEncounter->round(),
            $updatedEncounter->turnIndex(),
            $this->clock->now(),
            [
                'deed' => BattleDeed::ATTACK,
                'target_token_id' => $target->id(),
            ] + $outcome->toArray()
        );

        $this->events->append($event);

        return [
            'encounter' => $updatedEncounter,
            'deed_event' => $deed['event'],
            'attack_event' => $event,
            'outcome' => $outcome,
        ];
    }
}
