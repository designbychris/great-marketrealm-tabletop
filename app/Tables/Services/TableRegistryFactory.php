<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Services;

use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;

defined('ABSPATH') || exit;

final class TableRegistryFactory
{
    public static function make(): TableRegistry
    {
        return new TableRegistry(
            new WordPressTableRepository(),
            new WordPressTableCapacityPolicy(),
            new SystemTableClock(),
            new UuidTableIdGenerator()
        );
    }
}
