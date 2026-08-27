<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Cartography\Services;

use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Repositories\WordPressTableSceneRepository;

defined('ABSPATH') || exit;

final class CartographersTableFactory
{
    public static function make(): CartographersTable
    {
        return new CartographersTable(
            new WordPressTableMembershipRepository(),
            new WordPressTableSceneRepository(),
            new BattlemapInspector()
        );
    }
}
