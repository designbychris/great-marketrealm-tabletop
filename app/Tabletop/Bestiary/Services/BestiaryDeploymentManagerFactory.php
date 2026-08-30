<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Bestiary\Services;

use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Repositories\WordPressTableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Services\TableTokenManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Repositories\WordPressThresholdRepository;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Repositories\TrainingBestiaryRepository;

defined('ABSPATH') || exit;

final class BestiaryDeploymentManagerFactory
{
    public static function make(): BestiaryDeploymentManager
    {
        return new BestiaryDeploymentManager(
            new WordPressTableMembershipRepository(),
            new WordPressTableSceneRepository(),
            new WordPressThresholdRepository(),
            new WordPressTableTokenRepository(),
            TableTokenManagerFactory::make(),
            new TrainingBestiaryRepository()
        );
    }
}
