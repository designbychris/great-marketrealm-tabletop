<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Bestiary\Services;

use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Contracts\CombatArsenalRepository;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\ArsenalAttack;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\AttackKind;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\CombatArsenal;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\CombatProfileRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DamageDefenseRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DamageProfileRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\VitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageDefenseProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageType;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\Vitality;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Models\BestiaryCreature;

defined('ABSPATH') || exit;

/**
 * IV.29C — turns a freshly summoned Bestiary instance into a complete
 * Tabletop combatant without coupling the Bestiary definition to battle state.
 *
 * The resulting repositories are an instance snapshot: later catalogue edits
 * do not silently rewrite a creature that is already standing on a Scene.
 */
final class BestiaryCombatProvisioner
{
    public function __construct(
        private CombatProfileRepository $combatProfiles,
        private DamageProfileRepository $damageProfiles,
        private CombatArsenalRepository $arsenals,
        private DamageDefenseRepository $defenses,
        private VitalityRepository $vitality
    ) {}

    public function provision(
        string $tableId,
        TableToken $token,
        BestiaryCreature $creature
    ): void {
        $attacks = $creature->attacks();
        $primary = $attacks[0] ?? null;

        $this->combatProfiles->save(
            $tableId,
            $this->combatProfile($token->id(), $creature, $primary)
        );

        $this->damageProfiles->save(
            $tableId,
            $this->damageProfile($token->id(), $primary)
        );

        $this->arsenals->save(
            $tableId,
            new CombatArsenal(
                $token->id(),
                $this->arsenalAttacks($token->id(), $creature)
            )
        );

        $this->defenses->save(
            $tableId,
            new DamageDefenseProfile(
                $token->id(),
                $creature->resistances(),
                $creature->weaknesses(),
                $creature->immunities()
            )
        );

        $this->vitality->save(
            $tableId,
            new Vitality(
                $token->id(),
                $creature->hitPoints(),
                $creature->hitPoints()
            )
        );
    }

    /** @param array<string,mixed>|null $attack */
    private function combatProfile(
        string $tokenId,
        BestiaryCreature $creature,
        ?array $attack
    ): CombatProfile {
        return new CombatProfile(
            $tokenId,
            $creature->armorClass(),
            (int) ($attack['attack_modifier'] ?? 0),
            $this->range($attack, 'range_feet', 5),
            $this->range($attack, 'long_range_feet', 5)
        );
    }

    /** @param array<string,mixed>|null $attack */
    private function damageProfile(
        string $tokenId,
        ?array $attack
    ): DamageProfile {
        $damage = is_array($attack['damage'] ?? null)
            ? $attack['damage']
            : [];

        return new DamageProfile(
            $tokenId,
            max(1, (int) ($damage['dice_count'] ?? 1)),
            (int) ($damage['die_sides'] ?? 4),
            (int) ($damage['modifier'] ?? 0),
            DamageType::assert((string) ($damage['type'] ?? DamageType::BLUDGEONING))
        );
    }

    /** @return array<int,ArsenalAttack> */
    private function arsenalAttacks(
        string $tokenId,
        BestiaryCreature $creature
    ): array {
        $attacks = [];

        foreach ($creature->attacks() as $record) {
            if (! is_array($record)) {
                continue;
            }

            $id = trim((string) ($record['id'] ?? ''));
            $name = trim((string) ($record['name'] ?? ''));
            if ($id === '' || $name === '') {
                continue;
            }

            $damage = is_array($record['damage'] ?? null)
                ? $record['damage']
                : [];
            $range = $this->range($record, 'range_feet', 5);
            $longRange = max(
                $range,
                $this->range($record, 'long_range_feet', $range)
            );

            $attacks[] = new ArsenalAttack(
                $id,
                $tokenId,
                $name,
                AttackKind::assert((string) ($record['kind'] ?? AttackKind::IMPROVISED)),
                new CombatProfile(
                    $tokenId,
                    $creature->armorClass(),
                    (int) ($record['attack_modifier'] ?? 0),
                    $range,
                    $longRange
                ),
                new DamageProfile(
                    $tokenId,
                    max(1, (int) ($damage['dice_count'] ?? 1)),
                    (int) ($damage['die_sides'] ?? 4),
                    (int) ($damage['modifier'] ?? 0),
                    DamageType::assert((string) ($damage['type'] ?? DamageType::BLUDGEONING))
                ),
                is_array($record['properties'] ?? null)
                    ? array_map('strval', $record['properties'])
                    : [],
                'bestiary',
                'gmrt-bestiary:' . $creature->id() . ':' . $id
            );
        }

        return $attacks;
    }

    /** @param array<string,mixed>|null $attack */
    private function range(?array $attack, string $key, int $fallback): int
    {
        $range = (int) ($attack[$key] ?? $fallback);
        return max(5, $range);
    }
}
