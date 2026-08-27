<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Fog\Contracts;

use GreatMarketrealmTabletop\Tabletop\Fog\Models\FogOfWarState;

defined('ABSPATH') || exit;

interface FogOfWarRepository
{
    public function forScene(
        string $tableId,
        string $sceneId
    ): FogOfWarState;

    public function save(
        string $tableId,
        FogOfWarState $state
    ): void;
}
