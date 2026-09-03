<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Sessions\Services;

use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;
use GreatMarketrealmTabletop\Tables\Services\SystemTableClock;
use GreatMarketrealmTabletop\Tabletop\Sessions\Repositories\WordPressTableSessionRepository;
use GreatMarketrealmTabletop\Tabletop\Sessions\Repositories\WordPressSessionRecapRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressBattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Repositories\WordPressChamberChronicleRepository;
use GreatMarketrealmTabletop\Tabletop\Encounters\Repositories\WordPressEncounterRepository;

defined('ABSPATH') || exit;

final class TableSessionManagerFactory
{
    public static function make(): TableSessionManager
    {
        return new TableSessionManager(
            new WordPressTableRepository(),
            new WordPressTableSessionRepository(),
            new SystemTableClock(),
            new SessionRecapBuilder(
                new WordPressBattleEventRepository(),
                new WordPressChamberChronicleRepository(),
                new WordPressEncounterRepository()
            ),
            new WordPressSessionRecapRepository()
        );
    }
}
