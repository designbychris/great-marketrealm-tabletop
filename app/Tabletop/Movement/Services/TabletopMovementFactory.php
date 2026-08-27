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
            FogOfWarFactory::make()
        );
    }
}
