<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Atlas\Services;

defined('ABSPATH') || exit;

use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Services\TableSceneManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Cartography\Services\BattlemapInspector;
use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Services\ThresholdManagerFactory;

final class KeepersAtlasFactory
{
    public static function make(): KeepersAtlas
    {
        return new KeepersAtlas(
            new WordPressTableMembershipRepository(),
            TableSceneManagerFactory::make(),
            new BattlemapInspector(),
            new SceneShelfCleaner(),
            ThresholdManagerFactory::make()
        );
    }
}
