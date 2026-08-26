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

        update_option(
            'gmrt_table_lease_seconds',
            900,
            false
        );

        update_option(
            'gmrt_table_heartbeat_grace_seconds',
            120,
            false
        );

        update_option(
            'gmrt_capacity_override_user_ids',
            [],
            false
        );
    }
}
