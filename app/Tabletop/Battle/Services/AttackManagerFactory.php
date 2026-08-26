<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressBattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressCombatProfileRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressDamageProfileRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressDamageDefenseRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressDeathSaveRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressVitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Repositories\WordPressConditionRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Services\ConditionCombatRules;
use GreatMarketrealmTabletop\Tabletop\Encounters\Repositories\WordPressEncounterRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Services\SystemTableClock;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;

defined('ABSPATH') || exit;

final class AttackManagerFactory
{
    public static function make(): AttackManager
    {
        $encounters = new WordPressEncounterRepository();
        $members = new WordPressTableMembershipRepository();
        $tokens = new WordPressTableTokenRepository();
        $events = new WordPressBattleEventRepository();
        $clock = new SystemTableClock();

        return new AttackManager(
            new BattleDeedManager(
                $encounters,
                $members,
                $tokens,
                $events,
                $clock,
                new WordPressVitalityRepository(),
                new WordPressConditionRepository(),
                new ConditionCombatRules()
            ),
            $encounters,
            $members,
            $tokens,
            new WordPressCombatProfileRepository(),
            new WordPressDamageProfileRepository(),
            new WordPressDamageDefenseRepository(),
            new WordPressVitalityRepository(),
            new WordPressDeathSaveRepository(),
            $events,
            new AttackResolver(new SecureD20Roller()),
            new DamageResolver(new SecureDamageDieRoller()),
            new DamageDefenseResolver(),
            $clock,
            new WordPressConditionRepository(),
            new ConditionCombatRules()
        );
    }
}
