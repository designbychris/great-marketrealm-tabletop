<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressBattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressDeathSaveRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressVitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Encounters\Repositories\WordPressEncounterRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Services\SystemTableClock;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;

defined('ABSPATH') || exit;

final class DeathSaveManagerFactory
{
    public static function make(): DeathSaveManager
    {
        return new DeathSaveManager(
            new WordPressEncounterRepository(),
            new WordPressTableMembershipRepository(),
            new WordPressTableTokenRepository(),
            new WordPressVitalityRepository(),
            new WordPressDeathSaveRepository(),
            new WordPressBattleEventRepository(),
            new DeathSaveResolver(
                new SecureDeathSaveRoller()
            ),
            new SystemTableClock()
        );
    }
}
