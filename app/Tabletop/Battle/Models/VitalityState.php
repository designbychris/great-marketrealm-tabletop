<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

defined('ABSPATH') || exit;

final class VitalityState
{
    public const HEALTHY = 'healthy';
    public const WOUNDED = 'wounded';
    public const DOWN = 'down';

    /** @return array<int,string> */
    public static function all(): array
    {
        return [
            self::HEALTHY,
            self::WOUNDED,
            self::DOWN,
        ];
    }
}
