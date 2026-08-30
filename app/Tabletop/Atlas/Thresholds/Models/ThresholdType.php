<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class ThresholdType
{
    public const PARTY = 'party';
    public const MONSTER = 'monster';

    /** @return array<int,string> */
    public static function all(): array
    {
        return [self::PARTY, self::MONSTER];
    }

    public static function assert(string $type): string
    {
        if (! in_array($type, self::all(), true)) {
            throw new InvalidArgumentException('Unknown Threshold Marker type.');
        }

        return $type;
    }
}
