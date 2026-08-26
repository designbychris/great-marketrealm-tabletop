<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressBattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressVitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Repositories\WordPressConditionRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Services\ConditionCombatRules;
use GreatMarketrealmTabletop\Tabletop\Encounters\Repositories\WordPressEncounterRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Services\SystemTableClock;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;

defined('ABSPATH') || exit;

final class BattleDeedManagerFactory
{
    public static function make(): BattleDeedManager
    {
        return new BattleDeedManager(
            new WordPressEncounterRepository(),
            new WordPressTableMembershipRepository(),
            new WordPressTableTokenRepository(),
            new WordPressBattleEventRepository(),
            new SystemTableClock(),
            new WordPressVitalityRepository(),
            new WordPressConditionRepository(),
            new ConditionCombatRules()
        );
    }
}
