<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Core;

defined('ABSPATH') || exit;

final class Activation
{
    public const SCHEMA_VERSION = '1';

    public static function activate(): void
    {
        update_option(
            'gmrt_schema_version',
            self::SCHEMA_VERSION,
            false
        );

        update_option(
            'gmrt_active_table_capacity',
            2,
            false
        );
    }
}
