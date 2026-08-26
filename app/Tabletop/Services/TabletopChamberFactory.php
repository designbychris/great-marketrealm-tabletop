<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Services;

use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Repositories\WordPressTableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;

defined('ABSPATH') || exit;

final class TabletopChamberFactory
{
    public static function make(): TabletopChamber
    {
        return new TabletopChamber(
            new WordPressTableRepository(),
            new WordPressTableMembershipRepository(),
            new WordPressTableSceneRepository(),
            new WordPressTableTokenRepository()
        );
    }
}
