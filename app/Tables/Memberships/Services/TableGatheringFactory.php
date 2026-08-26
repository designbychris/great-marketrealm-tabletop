<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Memberships\Services;

use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;
use GreatMarketrealmTabletop\Tables\Services\SystemTableClock;

defined('ABSPATH') || exit;

final class TableGatheringFactory
{
    public static function make(): TableGathering
    {
        return new TableGathering(
            new WordPressTableRepository(),
            new WordPressTableMembershipRepository(),
            new SystemTableClock()
        );
    }
}
