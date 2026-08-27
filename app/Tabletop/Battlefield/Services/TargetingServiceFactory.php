<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battlefield\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressCombatProfileRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\AttackRangeResolver;
use GreatMarketrealmTabletop\Tabletop\Conditions\Repositories\WordPressConditionRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Services\ConditionCombatRules;
use GreatMarketrealmTabletop\Tabletop\Encounters\Repositories\WordPressEncounterRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Repositories\WordPressTableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;

defined('ABSPATH') || exit;

final class TargetingServiceFactory
{
    public static function make(): TargetingService
    {
        return new TargetingService(
            new WordPressEncounterRepository(),
            new WordPressTableMembershipRepository(),
            new WordPressTableTokenRepository(),
            new WordPressTableSceneRepository(),
            new WordPressCombatProfileRepository(),
            new BattlefieldMeasure(),
            new AttackRangeResolver(),
            new WordPressConditionRepository(),
            new ConditionCombatRules()
        );
    }
}
