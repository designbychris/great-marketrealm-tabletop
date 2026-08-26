<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Services;

use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Repositories\WordPressTableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;
use GreatMarketrealmTabletop\Tabletop\Encounters\Repositories\WordPressEncounterRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressVitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressDeathSaveRepository;

defined('ABSPATH') || exit;

final class TabletopChamberFactory
{
    public static function make(): TabletopChamber
    {
        return new TabletopChamber(
            new WordPressTableRepository(),
            new WordPressTableMembershipRepository(),
            new WordPressTableSceneRepository(),
            new WordPressTableTokenRepository(),
            new WordPressEncounterRepository(),
            new WordPressVitalityRepository(),
            new WordPressDeathSaveRepository()
        );
    }
}
