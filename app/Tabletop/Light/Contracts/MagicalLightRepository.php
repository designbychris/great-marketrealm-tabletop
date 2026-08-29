<?php

declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Light\Contracts;
use GreatMarketrealmTabletop\Tabletop\Light\Models\MagicalLight;
interface MagicalLightRepository
{
    /** @return array<int,MagicalLight> */
    public function forScene(string $tableId,string $sceneId): array;
    public function forToken(string $tableId,string $sceneId,string $tokenId): ?MagicalLight;
    public function save(MagicalLight $light): void;
    public function deleteForToken(string $tableId,string $sceneId,string $tokenId): void;
}
