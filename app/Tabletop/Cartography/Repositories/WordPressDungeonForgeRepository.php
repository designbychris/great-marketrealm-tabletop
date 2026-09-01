<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Cartography\Repositories;

use GreatMarketrealmTabletop\Tabletop\Cartography\Contracts\DungeonForgeRepository;

defined('ABSPATH') || exit;

final class WordPressDungeonForgeRepository implements DungeonForgeRepository
{
    private const OPTION = 'gmrt_dungeon_forge_plans';

    public function forScene(string $tableId, string $sceneId): ?array
    {
        $records = get_option(self::OPTION, []);
        if (! is_array($records)) {
            return null;
        }

        $plan = $records[$tableId][$sceneId] ?? null;
        return is_array($plan) ? $plan : null;
    }

    public function save(string $tableId, string $sceneId, array $plan): void
    {
        $records = get_option(self::OPTION, []);
        if (! is_array($records)) {
            $records = [];
        }

        $records[$tableId][$sceneId] = $plan;
        update_option(self::OPTION, $records, false);
    }
}
