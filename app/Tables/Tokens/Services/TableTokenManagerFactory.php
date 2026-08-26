<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Tokens\Services;

use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Repositories\WordPressTableSceneRepository;
use GreatMarketrealmTabletop\Tables\Services\SystemTableClock;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;

defined('ABSPATH') || exit;

final class TableTokenManagerFactory
{
    public static function make(): TableTokenManager
    {
        return new TableTokenManager(
            new WordPressTableRepository(),
            new WordPressTableSceneRepository(),
            new WordPressTableTokenRepository(),
            new UuidTableTokenIdGenerator(),
            new SystemTableClock()
        );
    }
}
