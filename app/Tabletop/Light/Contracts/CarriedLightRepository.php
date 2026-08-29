<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Light\Contracts;
defined('ABSPATH') || exit;
interface CarriedLightRepository
{
    public function isLit(string $tableId, string $sceneId, string $tokenId): bool;
    public function setLit(string $tableId, string $sceneId, string $tokenId, bool $lit): void;
}
