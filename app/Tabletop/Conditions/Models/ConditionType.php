<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Conditions\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class ConditionType
{
    public const BLINDED = 'blinded';
    public const CHARMED = 'charmed';
    public const FRIGHTENED = 'frightened';
    public const GRAPPLED = 'grappled';
    public const POISONED = 'poisoned';
    public const PRONE = 'prone';
    public const RESTRAINED = 'restrained';
    public const STUNNED = 'stunned';

    /** @return array<int,string> */
    public static function all(): array
    {
        return [
            self::BLINDED,
            self::CHARMED,
            self::FRIGHTENED,
            self::GRAPPLED,
            self::POISONED,
            self::PRONE,
            self::RESTRAINED,
            self::STUNNED,
        ];
    }

    public static function assert(string $condition): string
    {
        if (! in_array($condition, self::all(), true)) {
            throw new InvalidArgumentException(
                'Unsupported combat condition.'
            );
        }

        return $condition;
    }
}
