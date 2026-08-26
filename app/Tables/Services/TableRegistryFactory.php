<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Services;

use GreatMarketrealmTabletop\Tables\Policies\WordPressTableLeasePolicy;
use GreatMarketrealmTabletop\Tables\Policies\WordPressTableStewardOverride;
use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Services\TableGathering;

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

        $gathering = new TableGathering(
            $tables,
            new WordPressTableMembershipRepository(),
            $clock
        );

        return new TableRegistry(
            $tables,
            new WordPressTableCapacityPolicy(),
            $clock,
            new UuidTableIdGenerator(),
            $leases,
            new WordPressTableStewardOverride(),
            $gathering
        );
    }
}
