<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressBattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressCombatProfileRepository;
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
                $clock
            ),
            $encounters,
            $members,
            $tokens,
            new WordPressCombatProfileRepository(),
            $events,
            new AttackResolver(new SecureD20Roller()),
            $clock
        );
    }
}
