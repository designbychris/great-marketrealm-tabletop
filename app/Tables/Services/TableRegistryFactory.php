<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Services;

use GreatMarketrealmTabletop\Tables\Policies\WordPressTableLeasePolicy;
use GreatMarketrealmTabletop\Tables\Policies\WordPressTableStewardOverride;
use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;

defined('ABSPATH') || exit;

final class TableRegistryFactory
{
    public static function make(): TableRegistry
    {
        $tables = new WordPressTableRepository();
        $clock = new SystemTableClock();
        $leases = new TableLeaseManager(
            $tables,
            new WordPressTableLeasePolicy(),
            $clock
        );

        return new TableRegistry(
            $tables,
            new WordPressTableCapacityPolicy(),
            $clock,
            new UuidTableIdGenerator(),
            $leases,
            new WordPressTableStewardOverride()
        );
    }
}
