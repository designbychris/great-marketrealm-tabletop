<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Scenes\Services;

use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Repositories\WordPressTableSceneRepository;
use GreatMarketrealmTabletop\Tables\Services\SystemTableClock;

defined('ABSPATH') || exit;

final class TableSceneManagerFactory
{
    public static function make(): TableSceneManager
    {
        return new TableSceneManager(
            new WordPressTableRepository(),
            new WordPressTableSceneRepository(),
            new UuidTableSceneIdGenerator(),
            new SystemTableClock()
        );
    }
}
