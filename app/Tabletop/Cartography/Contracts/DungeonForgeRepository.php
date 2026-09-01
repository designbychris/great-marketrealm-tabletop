<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Cartography\Contracts;

defined('ABSPATH') || exit;

interface DungeonForgeRepository
{
    /** @return array<string,mixed>|null */
    public function forScene(string $tableId, string $sceneId): ?array;

    /** @param array<string,mixed> $plan */
    public function save(string $tableId, string $sceneId, array $plan): void;
}
