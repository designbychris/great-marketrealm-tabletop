<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Tokens\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class TableTokenType
{
    public const CHARACTER = 'character';
    public const CREATURE = 'creature';
    public const OBJECT = 'object';

    /** @return array<int,string> */
    public static function all(): array
    {
        return [
            self::CHARACTER,
            self::CREATURE,
            self::OBJECT,
        ];
    }

    public static function assert(string $type): string
    {
        if (! in_array($type, self::all(), true)) {
            throw new InvalidArgumentException(
                'Unsupported Table token type.'
            );
        }

        return $type;
    }
}
