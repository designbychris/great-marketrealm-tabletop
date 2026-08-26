<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class TableStatus
{
    public const PREPARING = 'preparing';
    public const ACTIVE = 'active';
    public const ENDED = 'ended';

    /** @return array<int,string> */
    public static function all(): array
    {
        return [
            self::PREPARING,
            self::ACTIVE,
            self::ENDED,
        ];
    }

    public static function assert(string $status): string
    {
        if (! in_array($status, self::all(), true)) {
            throw new InvalidArgumentException(
                'Unknown Table status: ' . $status
            );
        }

        return $status;
    }
}
