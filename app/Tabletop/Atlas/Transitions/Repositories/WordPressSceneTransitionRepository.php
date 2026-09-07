<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Atlas\Transitions\Repositories;

defined('ABSPATH') || exit;

final class WordPressSceneTransitionRepository
{
    private const OPTION = 'gmrt_scene_transitions';

    /** @return array<string,mixed>|null */
    public function forSource(string $tableId, string $sourceSceneId): ?array
    {
        $records = get_option(self::OPTION, []);
        if (! is_array($records)) {
            return null;
        }
        $transition = $records[$tableId][$sourceSceneId] ?? null;
        return is_array($transition) ? $transition : null;
    }

    /** @param array<string,mixed> $transition */
    public function save(string $tableId, string $sourceSceneId, array $transition): void
    {
        $records = get_option(self::OPTION, []);
        if (! is_array($records)) {
            $records = [];
        }
        $records[$tableId][$sourceSceneId] = $transition;
        update_option(self::OPTION, $records, false);
    }

    public function remove(string $tableId, string $sourceSceneId): void
    {
        $records = get_option(self::OPTION, []);
        if (! is_array($records) || ! isset($records[$tableId])) {
            return;
        }
        unset($records[$tableId][$sourceSceneId]);
        foreach ($records[$tableId] as $source => $transition) {
            if (is_array($transition) && ($transition['destination_scene_id'] ?? '') === $sourceSceneId) {
                unset($records[$tableId][$source]);
            }
        }
        update_option(self::OPTION, $records, false);
    }
}
