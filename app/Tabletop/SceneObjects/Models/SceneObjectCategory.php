<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\SceneObjects\Models;

defined('ABSPATH') || exit;

final class SceneObjectCategory
{
    public const DECORATIVE = 'decorative';
    public const STRUCTURAL = 'structural';
    public const INTERACTIVE = 'interactive';

    /** @return array<int,string> */
    public static function all(): array
    {
        return [self::DECORATIVE, self::STRUCTURAL, self::INTERACTIVE];
    }
}
