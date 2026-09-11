<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Movement\Services;

use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Repositories\WordPressTableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Repositories\WordPressConditionRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Services\ConditionCombatRules;
use GreatMarketrealmTabletop\Tabletop\Fog\Services\FogOfWarFactory;
use GreatMarketrealmTabletop\Tabletop\Footsteps\Repositories\WordPressFootstepTrailRepository;
use GreatMarketrealmTabletop\Tabletop\Footsteps\Services\FootstepTrailRecorder;
use GreatMarketrealmTabletop\Tabletop\Cartography\Repositories\WordPressDungeonForgeRepository;
use GreatMarketrealmTabletop\Tabletop\Cartography\Services\ForgeTrapTrigger;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Services\AdventureEventRecorder;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Repositories\WordPressChamberChronicleRepository;
use GreatMarketrealmTabletop\Tables\Services\SystemTableClock;

defined('ABSPATH') || exit;

final class TabletopMovementFactory
{
    public static function make(): TabletopMovement
    {
        return new TabletopMovement(
            new WordPressTableRepository(),
            new WordPressTableMembershipRepository(),
            new WordPressTableSceneRepository(),
            new WordPressTableTokenRepository(),
            new TabletopMovementPolicy(),
            new WordPressConditionRepository(),
            new ConditionCombatRules(),
            FogOfWarFactory::make(),
            new FootstepTrailRecorder(new WordPressFootstepTrailRepository()),
            new ForgeTrapTrigger(
                new WordPressDungeonForgeRepository(),
                new AdventureEventRecorder(
                    new WordPressChamberChronicleRepository(),
                    new SystemTableClock()
                )
            )
        );
    }
}
