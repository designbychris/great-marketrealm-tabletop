<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class BattleDeed
{
    public const ATTACK = 'attack';
    public const DASH = 'dash';
    public const DISENGAGE = 'disengage';
    public const DODGE = 'dodge';
    public const HELP = 'help';

    /** @return array<int,string> */
    public static function all(): array
    {
        return [
            self::ATTACK,
            self::DASH,
            self::DISENGAGE,
            self::DODGE,
            self::HELP,
        ];
    }

    public static function assert(string $deed): string
    {
        if (! in_array($deed, self::all(), true)) {
            throw new InvalidArgumentException(
                'Unsupported battle deed.'
            );
        }

        return $deed;
    }

    public static function resource(string $deed): string
    {
        self::assert($deed);

        return TurnResource::ACTION;
    }
}
