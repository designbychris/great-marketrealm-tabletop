<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressBattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressDamageDefenseRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressDeathSaveRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressVitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Encounters\Repositories\WordPressEncounterRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Services\SystemTableClock;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;

defined('ABSPATH') || exit;

final class DamageRollManagerFactory
{
    public static function make(): DamageRollManager
    {
        return new DamageRollManager(
            new WordPressEncounterRepository(),
            new WordPressTableMembershipRepository(),
            new WordPressTableTokenRepository(),
            new WordPressBattleEventRepository(),
            new WordPressDamageDefenseRepository(),
            new WordPressVitalityRepository(),
            new WordPressDeathSaveRepository(),
            new DamageResolver(new SecureDamageDieRoller()),
            new DamageDefenseResolver(),
            new SystemTableClock()
        );
    }
}
