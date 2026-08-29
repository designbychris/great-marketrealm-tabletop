<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Light\Contracts;
use GreatMarketrealmTabletop\Tabletop\Light\Models\DroppedLight;
defined('ABSPATH') || exit;
interface DroppedLightRepository
{
    /** @return array<int,DroppedLight> */
    public function forScene(string $tableId, string $sceneId): array;
    public function save(DroppedLight $light): void;
    public function delete(string $tableId, string $sceneId, string $lightId): void;
}
