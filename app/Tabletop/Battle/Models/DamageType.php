<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class DamageType
{
    public const ACID = 'acid';
    public const BLUDGEONING = 'bludgeoning';
    public const COLD = 'cold';
    public const FIRE = 'fire';
    public const FORCE = 'force';
    public const LIGHTNING = 'lightning';
    public const NECROTIC = 'necrotic';
    public const PIERCING = 'piercing';
    public const POISON = 'poison';
    public const PSYCHIC = 'psychic';
    public const RADIANT = 'radiant';
    public const SLASHING = 'slashing';
    public const THUNDER = 'thunder';

    /** @return array<int,string> */
    public static function all(): array
    {
        return [
            self::ACID,
            self::BLUDGEONING,
            self::COLD,
            self::FIRE,
            self::FORCE,
            self::LIGHTNING,
            self::NECROTIC,
            self::PIERCING,
            self::POISON,
            self::PSYCHIC,
            self::RADIANT,
            self::SLASHING,
            self::THUNDER,
        ];
    }

    public static function assert(string $type): string
    {
        if (! in_array($type, self::all(), true)) {
            throw new InvalidArgumentException(
                'Unsupported damage type.'
            );
        }

        return $type;
    }
}
