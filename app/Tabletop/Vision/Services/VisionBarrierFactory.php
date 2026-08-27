<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Vision\Services;
use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Repositories\WordPressTableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;
use GreatMarketrealmTabletop\Tabletop\Fog\Repositories\WordPressFogOfWarRepository;
use GreatMarketrealmTabletop\Tabletop\Fog\Services\FogCellMapper;
use GreatMarketrealmTabletop\Tabletop\Vision\Repositories\WordPressVisionBarrierRepository;
defined('ABSPATH') || exit;
final class VisionBarrierFactory
{
    public static function make():VisionBarrierManager{return new VisionBarrierManager(new WordPressVisionBarrierRepository(),new WordPressTableMembershipRepository(),new WordPressTableSceneRepository(),new WordPressFogOfWarRepository(),new WordPressTableTokenRepository(),new FogCellMapper());}
}
