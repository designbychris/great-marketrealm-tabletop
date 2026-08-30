<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Repositories;

use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Contracts\ThresholdRepository;
use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Models\ThresholdMarker;

defined('ABSPATH') || exit;

final class WordPressThresholdRepository implements ThresholdRepository
{
    private const OPTION = 'gmrt_scene_thresholds';

    /** @return array<int,ThresholdMarker> */
    public function forScene(string $tableId, string $sceneId): array
    {
        $markers = [];
        foreach ($this->records()[$tableId][$sceneId] ?? [] as $record) {
            if (is_array($record)) {
                $markers[] = ThresholdMarker::reconstitute($record);
            }
        }
        return $markers;
    }

    public function find(string $tableId, string $sceneId, string $markerId): ?ThresholdMarker
    {
        $record = $this->records()[$tableId][$sceneId][$markerId] ?? null;
        return is_array($record) ? ThresholdMarker::reconstitute($record) : null;
    }

    public function save(ThresholdMarker $marker): void
    {
        $records = $this->records();
        $records[$marker->tableId()][$marker->sceneId()][$marker->id()] = $marker->toArray();
        update_option(self::OPTION, $records, false);
    }

    public function delete(string $tableId, string $sceneId, string $markerId): void
    {
        $records = $this->records();
        unset($records[$tableId][$sceneId][$markerId]);
        if (($records[$tableId][$sceneId] ?? []) === []) {
            unset($records[$tableId][$sceneId]);
        }
        if (($records[$tableId] ?? []) === []) {
            unset($records[$tableId]);
        }
        update_option(self::OPTION, $records, false);
    }

    /** @return array<string,mixed> */
    private function records(): array
    {
        $records = get_option(self::OPTION, []);
        return is_array($records) ? $records : [];
    }
}
