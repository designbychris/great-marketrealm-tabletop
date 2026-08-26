<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class TurnResource
{
    public const ACTION = 'action';
    public const BONUS_ACTION = 'bonus-action';
    public const MOVEMENT = 'movement';
    public const REACTION = 'reaction';

    /** @return array<int,string> */
    public static function all(): array
    {
        return [
            self::ACTION,
            self::BONUS_ACTION,
            self::MOVEMENT,
            self::REACTION,
        ];
    }

    public static function assert(string $resource): string
    {
        if (! in_array($resource, self::all(), true)) {
            throw new InvalidArgumentException(
                'Unsupported turn resource.'
            );
        }

        return $resource;
    }
}
