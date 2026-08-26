<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Conditions\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressBattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Repositories\WordPressConditionRepository;
use GreatMarketrealmTabletop\Tabletop\Encounters\Repositories\WordPressEncounterRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Services\SystemTableClock;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;

defined('ABSPATH') || exit;

final class ConditionManagerFactory
{
    public static function make(): ConditionManager
    {
        return new ConditionManager(
            new WordPressConditionRepository(),
            new WordPressEncounterRepository(),
            new WordPressTableMembershipRepository(),
            new WordPressTableTokenRepository(),
            new WordPressBattleEventRepository(),
            new SystemTableClock()
        );
    }

    public static function lifecycle(): ConditionLifecycle
    {
        return new ConditionLifecycle(
            new WordPressConditionRepository(),
            new WordPressBattleEventRepository(),
            new SystemTableClock()
        );
    }
}
