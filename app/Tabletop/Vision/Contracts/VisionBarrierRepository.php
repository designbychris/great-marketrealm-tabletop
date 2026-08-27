<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Vision\Contracts;
use GreatMarketrealmTabletop\Tabletop\Vision\Models\VisionBarrier;
defined('ABSPATH') || exit;
interface VisionBarrierRepository
{
    /** @return array<int,VisionBarrier> */ public function forScene(string $tableId,string $sceneId):array;
    public function save(string $tableId,VisionBarrier $barrier):void;
    public function delete(string $tableId,string $sceneId,string $barrierId):void;
}
