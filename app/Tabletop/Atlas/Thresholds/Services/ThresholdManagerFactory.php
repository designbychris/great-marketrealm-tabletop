<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Services;

use GreatMarketrealmTabletop\Integration\Companion\WordPressCompanionCharacterGateway;
use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Repositories\WordPressTableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Services\TableTokenManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Repositories\WordPressThresholdRepository;

defined('ABSPATH') || exit;

final class ThresholdManagerFactory
{
    public static function make(): ThresholdManager
    {
        return new ThresholdManager(
            new WordPressTableMembershipRepository(),
            new WordPressTableSceneRepository(),
            new WordPressThresholdRepository(),
            new WordPressTableTokenRepository(),
            TableTokenManagerFactory::make(),
            new WordPressCompanionCharacterGateway()
        );
    }
}
