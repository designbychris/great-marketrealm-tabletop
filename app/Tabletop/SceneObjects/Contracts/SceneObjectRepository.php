<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\SceneObjects\Contracts;

use GreatMarketrealmTabletop\Tabletop\SceneObjects\Models\SceneObject;

defined('ABSPATH') || exit;

interface SceneObjectRepository
{
    /** @return array<int,SceneObject> */
    public function forScene(string $tableId, string $sceneId): array;
    public function find(string $tableId, string $sceneId, string $objectId): ?SceneObject;
    public function save(SceneObject $object): void;
    public function remove(string $tableId, string $sceneId, string $objectId): void;
    public function clearScene(string $tableId, string $sceneId): void;
}
