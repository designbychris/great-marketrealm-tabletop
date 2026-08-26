<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Tokens\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class TableTokenVisibility
{
    public const VISIBLE = 'visible';
    public const HIDDEN = 'hidden';

    /** @return array<int,string> */
    public static function all(): array
    {
        return [
            self::VISIBLE,
            self::HIDDEN,
        ];
    }

    public static function assert(string $visibility): string
    {
        if (! in_array($visibility, self::all(), true)) {
            throw new InvalidArgumentException(
                'Unsupported Table token visibility.'
            );
        }

        return $visibility;
    }
}
