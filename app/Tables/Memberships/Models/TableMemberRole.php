<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Memberships\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class TableMemberRole
{
    public const DUNGEON_MASTER = 'dungeon-master';
    public const PLAYER = 'player';

    /** @return array<int,string> */
    public static function all(): array
    {
        return [
            self::DUNGEON_MASTER,
            self::PLAYER,
        ];
    }

    public static function assert(string $role): string
    {
        if (! in_array($role, self::all(), true)) {
            throw new InvalidArgumentException(
                'Unknown Table member role: ' . $role
            );
        }

        return $role;
    }
}
