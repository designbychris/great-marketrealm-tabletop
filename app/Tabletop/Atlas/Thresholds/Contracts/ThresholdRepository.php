<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Contracts;

use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Models\ThresholdMarker;

interface ThresholdRepository
{
    /** @return array<int,ThresholdMarker> */
    public function forScene(string $tableId, string $sceneId): array;
    public function find(string $tableId, string $sceneId, string $markerId): ?ThresholdMarker;
    public function save(ThresholdMarker $marker): void;
    public function delete(string $tableId, string $sceneId, string $markerId): void;
}
