<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Sessions\Services;

use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;
use GreatMarketrealmTabletop\Tables\Services\SystemTableClock;
use GreatMarketrealmTabletop\Tabletop\Sessions\Repositories\WordPressTableSessionRepository;

defined('ABSPATH') || exit;

final class TableSessionManagerFactory
{
    public static function make(): TableSessionManager
    {
        return new TableSessionManager(
            new WordPressTableRepository(),
            new WordPressTableSessionRepository(),
            new SystemTableClock()
        );
    }
}
