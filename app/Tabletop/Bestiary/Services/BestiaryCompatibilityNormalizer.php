<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Bestiary\Services;

use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\AttackKind;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageType;
use InvalidArgumentException;

defined('ABSPATH') || exit;

/**
 * IV.35.10A — normalises neutral Bestiary vocabulary at the integration edge.
 *
 * Companion and future sources are allowed to use presentation-friendly
 * labels such as "Piercing Damage" or "Melee Weapon Attack". Tabletop keeps
 * its own small canonical combat vocabulary internally.
 */
final class BestiaryCompatibilityNormalizer
{
    public function damageType(string $value): string
    {
        $original = trim($value);
        $value = strtolower($original);
        $value = str_replace(['_', '-'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;
        $value = trim($value);

        $value = preg_replace('/\s+damage$/', '', $value) ?? $value;
        $value = trim($value);

        $aliases = [
            'blunt' => DamageType::BLUDGEONING,
            'bludgeon' => DamageType::BLUDGEONING,
            'physical' => DamageType::BLUDGEONING,
            'frost' => DamageType::COLD,
            'ice' => DamageType::COLD,
            'electric' => DamageType::LIGHTNING,
            'electricity' => DamageType::LIGHTNING,
            'holy' => DamageType::RADIANT,
            'shadow' => DamageType::NECROTIC,
            'toxic' => DamageType::POISON,
        ];
        if (isset($aliases[$value])) {
            return $aliases[$value];
        }

        $firstCanonical = null;
        $firstOffset = null;

        foreach (DamageType::all() as $canonical) {
            if ($value === $canonical) {
                return $canonical;
            }

            if (preg_match(
                '/\b' . preg_quote($canonical, '/') . '\b/',
                $value,
                $matches,
                PREG_OFFSET_CAPTURE
            ) !== 1) {
                continue;
            }

            $offset = (int) ($matches[0][1] ?? PHP_INT_MAX);
            if ($firstOffset === null || $offset < $firstOffset) {
                $firstCanonical = $canonical;
                $firstOffset = $offset;
            }
        }

        if ($firstCanonical !== null) {
            return $firstCanonical;
        }

        throw new InvalidArgumentException(sprintf(
            'Unsupported Bestiary damage type "%s".',
            $original === '' ? '(empty)' : $original
        ));
    }

    public function attackKind(string $value): string
    {
        $value = strtolower(trim($value));
        $value = str_replace(['_', '-'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        if (str_contains($value, 'melee')) {
            return AttackKind::MELEE_WEAPON;
        }
        if (str_contains($value, 'ranged')) {
            return AttackKind::RANGED_WEAPON;
        }
        if (str_contains($value, 'spell')) {
            return AttackKind::SPELL;
        }

        try {
            return AttackKind::assert(trim($value));
        } catch (InvalidArgumentException) {
            return AttackKind::IMPROVISED;
        }
    }
}
