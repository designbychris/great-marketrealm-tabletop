<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Arsenal\Services;

use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\ArsenalAttack;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\AttackKind;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\CombatArsenal;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageType;

defined('ABSPATH') || exit;

/**
 * Converts the Companion's owner-certified Weapons to Hand projection into the
 * Tabletop Arsenal format consumed by targeting and authoritative attacks.
 *
 * The browser still supplies only an attack ID. Attack bonus, range, damage
 * dice, modifier and type all originate from the Companion projection.
 */
final class CompanionCombatArsenalProjector
{
    /** @param array<string,mixed> $character */
    public function project(string $tokenId, array $character): CombatArsenal
    {
        $play = is_array($character['play'] ?? null) ? $character['play'] : [];
        $attacks = is_array($play['attacks'] ?? null) ? $play['attacks'] : [];
        $armorClass = max(1, min(50, (int) ($play['armour_class'] ?? 10)));
        $projected = [];

        foreach ($attacks as $attack) {
            if (! is_array($attack)) {
                continue;
            }

            $id = trim((string) ($attack['id'] ?? ''));
            $name = trim((string) ($attack['label'] ?? ''));
            $damageType = strtolower(trim((string) ($attack['damage_type'] ?? '')));
            $formula = $this->damageFormula((string) ($attack['damage_die'] ?? ''));

            if (
                $id === ''
                || $name === ''
                || $formula === null
                || ! in_array($damageType, DamageType::all(), true)
            ) {
                continue;
            }

            [$normalRange, $longRange] = $this->range((string) ($attack['range'] ?? ''));
            $properties = is_array($attack['properties'] ?? null)
                ? array_values(array_map('strval', $attack['properties']))
                : [];
            $kind = in_array('ranged', $properties, true) || $normalRange > 5
                ? AttackKind::RANGED_WEAPON
                : AttackKind::MELEE_WEAPON;

            $projected[] = new ArsenalAttack(
                $id,
                $tokenId,
                $name,
                $kind,
                new CombatProfile(
                    $tokenId,
                    $armorClass,
                    max(-20, min(30, (int) ($attack['attack_bonus'] ?? 0))),
                    $normalRange,
                    $longRange
                ),
                new DamageProfile(
                    $tokenId,
                    $formula['count'],
                    $formula['sides'],
                    max(-50, min(100, (int) ($attack['damage_modifier'] ?? 0))),
                    $damageType
                ),
                $properties,
                'companion',
                trim((string) ($character['id'] ?? '')) . ':' . $id
            );
        }

        return new CombatArsenal($tokenId, $projected);
    }

    /** @return array{count:int,sides:int}|null */
    private function damageFormula(string $formula): ?array
    {
        if (! preg_match('/^(\d+)d(4|6|8|10|12|20)$/i', trim($formula), $matches)) {
            return null;
        }

        $count = (int) $matches[1];
        if ($count < 1 || $count > 20) {
            return null;
        }

        return ['count' => $count, 'sides' => (int) $matches[2]];
    }

    /** @return array{0:int,1:int} */
    private function range(string $label): array
    {
        if (preg_match('/(\d+)\s*\/\s*(\d+)\s*ft/i', $label, $matches)) {
            return [
                $this->boundedRange((int) $matches[1], 5, 1000),
                $this->boundedRange((int) $matches[2], (int) $matches[1], 2000),
            ];
        }

        if (preg_match('/(\d+)\s*ft/i', $label, $matches)) {
            $range = $this->boundedRange((int) $matches[1], 5, 1000);
            return [$range, $range];
        }

        return [5, 5];
    }

    private function boundedRange(int $feet, int $minimum, int $maximum): int
    {
        $feet = max($minimum, min($maximum, $feet));
        return max(5, (int) (round($feet / 5) * 5));
    }
}
