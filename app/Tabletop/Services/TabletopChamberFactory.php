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
use GreatMarketrealmTabletop\Tabletop\Conditions\Repositories\WordPressConditionRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressBattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Presentation\BattleLogProjector;
use GreatMarketrealmTabletop\Tabletop\Presentation\CombatantStateProjector;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Repositories\WordPressCombatArsenalRepository;

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
            new WordPressDeathSaveRepository(),
            new WordPressConditionRepository(),
            new WordPressBattleEventRepository(),
            new BattleLogProjector(),
            new CombatantStateProjector(),
            new WordPressCombatArsenalRepository()
        );
    }
}
