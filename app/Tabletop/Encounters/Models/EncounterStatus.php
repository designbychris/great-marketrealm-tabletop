<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Encounters\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class EncounterStatus
{
    public const PREPARING = 'preparing';
    public const ACTIVE = 'active';
    public const PAUSED = 'paused';
    public const ENDED = 'ended';

    /** @return array<int,string> */
    public static function all(): array
    {
        return [
            self::PREPARING,
            self::ACTIVE,
            self::PAUSED,
            self::ENDED,
        ];
    }

    public static function assert(string $status): string
    {
        if (! in_array($status, self::all(), true)) {
            throw new InvalidArgumentException(
                'Unsupported Encounter status.'
            );
        }

        return $status;
    }
}
