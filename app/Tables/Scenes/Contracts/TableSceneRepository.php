<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Scenes\Contracts;

use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;

defined('ABSPATH') || exit;

interface TableSceneRepository
{
    /** @return array<int,TableScene> */
    public function forTable(string $tableId): array;

    public function find(string $tableId, string $sceneId): ?TableScene;

    public function save(TableScene $scene): void;
}
