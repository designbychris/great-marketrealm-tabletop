<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Scenes\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class GridType
{
    public const SQUARE = 'square';
    public const NONE = 'none';

    /** @return array<int,string> */
    public static function all(): array
    {
        return [self::SQUARE, self::NONE];
    }

    public static function assert(string $value): string
    {
        if (! in_array($value, self::all(), true)) {
            throw new InvalidArgumentException('Unsupported battlemap grid type.');
        }

        return $value;
    }
}
