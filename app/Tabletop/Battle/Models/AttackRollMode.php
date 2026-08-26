<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class AttackRollMode
{
    public const NORMAL = 'normal';
    public const ADVANTAGE = 'advantage';
    public const DISADVANTAGE = 'disadvantage';

    public static function assert(string $mode): string
    {
        if (! in_array(
            $mode,
            [
                self::NORMAL,
                self::ADVANTAGE,
                self::DISADVANTAGE,
            ],
            true
        )) {
            throw new InvalidArgumentException(
                'Unsupported attack roll mode.'
            );
        }

        return $mode;
    }
}
