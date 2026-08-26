<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Encounters\Services;

use GreatMarketrealmTabletop\Tabletop\Encounters\Repositories\WordPressEncounterRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Repositories\WordPressTableSceneRepository;
use GreatMarketrealmTabletop\Tables\Services\SystemTableClock;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;

defined('ABSPATH') || exit;

final class EncounterManagerFactory
{
    public static function make(): EncounterManager
    {
        return new EncounterManager(
            new WordPressTableRepository(),
            new WordPressTableMembershipRepository(),
            new WordPressTableSceneRepository(),
            new WordPressTableTokenRepository(),
            new WordPressEncounterRepository(),
            new UuidEncounterIdGenerator(),
            new SystemTableClock(),
            new EncounterControlPolicy()
        );
    }
}
