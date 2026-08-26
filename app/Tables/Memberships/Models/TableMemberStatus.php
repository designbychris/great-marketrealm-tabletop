<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Memberships\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class TableMemberStatus
{
    public const INVITED = 'invited';
    public const ACTIVE = 'active';
    public const LEFT = 'left';

    /** @return array<int,string> */
    public static function all(): array
    {
        return [
            self::INVITED,
            self::ACTIVE,
            self::LEFT,
        ];
    }

    public static function assert(string $status): string
    {
        if (! in_array($status, self::all(), true)) {
            throw new InvalidArgumentException(
                'Unknown Table member status: ' . $status
            );
        }

        return $status;
    }
}
