<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\BattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DamageDefenseRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DeathSaveRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\VitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Exceptions\AttackDenied;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackOutcome;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageProfile;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use RuntimeException;

/**
 * IV.29C.1A — lets the acting human explicitly roll already-earned damage.
 *
 * The attack event is the server-owned receipt. The browser submits only that
 * opaque receipt ID; dice, modifier, damage type, target, critical state and
 * defenses are all re-read from authoritative server state.
 */
final class DamageRollManager
{
    public function __construct(
        private EncounterRepository $encounters,
        private TableMembershipRepository $members,
        private TableTokenRepository $tokens,
        private BattleEventRepository $events,
        private DamageDefenseRepository $damageDefenses,
        private VitalityRepository $vitality,
        private DeathSaveRepository $deathSaves,
        private DamageResolver $damageResolver,
        private DamageDefenseResolver $defenseResolver,
        private TableClock $clock
    ) {}

    /** @return array<string,mixed> */
    public function roll(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        string $attackEventId
    ): array {
        $encounter = $this->encounters->find($tableId, $encounterId);
        if ($encounter === null) {
            throw new RuntimeException('The requested Encounter could not be found.');
        }

        $member = $this->members->find($tableId, $viewerUserId);
        if ($member === null || ! $member->isActive()) {
            throw new AttackDenied('Only an active Table member may roll damage.');
        }

        $attackRecord = null;
        foreach ($this->events->forEncounter($tableId, $encounterId) as $event) {
            $record = $event->toArray();
            if ((string) ($record['id'] ?? '') === $attackEventId) {
                $attackRecord = $record;
            }

            $payload = is_array($record['payload'] ?? null) ? $record['payload'] : [];
            if (
                ($record['type'] ?? '') === 'damage-applied'
                && (string) ($payload['attack_event_id'] ?? '') === $attackEventId
            ) {
                throw new AttackDenied('Damage has already been rolled for that attack.');
            }
        }

        if ($attackRecord === null || ($attackRecord['type'] ?? '') !== 'attack-resolved') {
            throw new AttackDenied('That attack is not available for a damage roll.');
        }

        $payload = is_array($attackRecord['payload'] ?? null) ? $attackRecord['payload'] : [];
        if (! (bool) ($payload['hit'] ?? false)) {
            throw new AttackDenied('A missed attack has no damage to roll.');
        }

        $attackerId = (string) ($attackRecord['token_id'] ?? '');
        $targetId = (string) ($payload['target_token_id'] ?? '');
        $attacker = $this->tokens->find($tableId, $attackerId);
        $target = $this->tokens->find($tableId, $targetId);

        if ($attacker === null || $target === null) {
            throw new AttackDenied('The attacker and target must still exist at this Table.');
        }

        if (! $member->isDungeonMaster() && $attacker->controllerUserId() !== $viewerUserId) {
            throw new AttackDenied('Only the acting adventurer or Dungeon Master may roll this damage.');
        }

        $combatant = $encounter->currentCombatant();
        if (
            $combatant === null
            || $combatant->tokenId() !== $attackerId
            || $encounter->round() !== (int) ($attackRecord['round'] ?? -1)
            || $encounter->turnIndex() !== (int) ($attackRecord['turn_index'] ?? -1)
        ) {
            throw new AttackDenied('Roll this damage before the current combatant ends their turn.');
        }

        $profileRecord = is_array($payload['damage_profile'] ?? null)
            ? $payload['damage_profile']
            : [];
        $profile = DamageProfile::reconstitute($profileRecord);
        $critical = (string) ($payload['result'] ?? '') === AttackOutcome::CRITICAL_HIT;
        $damageRoll = $this->damageResolver->resolve($profile, $critical);
        $adjustment = $this->defenseResolver->resolve(
            $damageRoll->total(),
            $profile->damageType(),
            $this->damageDefenses->forToken($tableId, $targetId)
        );

        $vitality = $this->vitality->forToken($tableId, $targetId);
        $hpBefore = $vitality->currentHp();
        $application = $vitality->damage($adjustment->resolvedDamage());
        $deathSaveState = $this->deathSaves->forToken($tableId, $targetId);
        $deathConsequence = 'none';

        if ($hpBefore === 0) {
            $failureCount = $critical ? 2 : 1;
            $deathSaveState->recordDamageFailure($failureCount);
            $deathConsequence = $failureCount === 2
                ? 'critical-damage-failures'
                : 'damage-failure';
        } elseif (
            $vitality->currentHp() === 0
            && (int) ($application['excess_damage'] ?? 0) >= $vitality->maximumHp()
        ) {
            $deathSaveState->markFallen();
            $deathConsequence = 'massive-damage';
        }

        $this->vitality->save($tableId, $vitality);
        $this->deathSaves->save($tableId, $deathSaveState);

        $damageEvent = new BattleEvent(
            bin2hex(random_bytes(12)),
            $tableId,
            $encounterId,
            'damage-applied',
            $attackerId,
            $encounter->round(),
            $encounter->turnIndex(),
            $this->clock->now(),
            [
                'attack_event_id' => $attackEventId,
                'target_token_id' => $targetId,
                'attack_id' => $payload['attack_id'] ?? null,
                'attack_name' => $payload['attack_name'] ?? null,
                'damage' => $damageRoll->toArray(),
                'damage_adjustment' => $adjustment->toArray(),
                'application' => $application,
                'vitality' => $vitality->toArray(),
                'death_consequence' => $deathConsequence,
                'death_saves' => $deathSaveState->toArray(),
            ]
        );
        $this->events->append($damageEvent);

        return [
            'damage' => $damageRoll,
            'damage_adjustment' => $adjustment,
            'vitality' => $vitality,
            'death_saves' => $deathSaveState,
            'damage_event' => $damageEvent,
        ];
    }
}
