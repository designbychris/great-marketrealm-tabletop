<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\SceneObjects\Repositories;

use GreatMarketrealmTabletop\Tabletop\SceneObjects\Contracts\SceneObjectRepository;
use GreatMarketrealmTabletop\Tabletop\SceneObjects\Models\SceneObject;

defined('ABSPATH') || exit;

final class WordPressSceneObjectRepository implements SceneObjectRepository
{
    public const OPTION_NAME = 'gmrt_scene_objects';

    public function forScene(string $tableId, string $sceneId): array
    {
        $objects = [];
        foreach ($this->records() as $record) {
            if (($record['table_id'] ?? '') !== $tableId || ($record['scene_id'] ?? '') !== $sceneId) {
                continue;
            }
            $objects[] = SceneObject::fromArray($record);
        }
        return $objects;
    }

    public function find(string $tableId, string $sceneId, string $objectId): ?SceneObject
    {
        foreach ($this->forScene($tableId, $sceneId) as $object) {
            if ($object->id() === $objectId) {
                return $object;
            }
        }
        return null;
    }

    public function save(SceneObject $object): void
    {
        $records = $this->records();
        $records[$object->id()] = $object->toArray();
        update_option(self::OPTION_NAME, $records, false);
    }

    public function remove(string $tableId, string $sceneId, string $objectId): void
    {
        $records = $this->records();
        $record = $records[$objectId] ?? null;
        if (!is_array($record) || ($record['table_id'] ?? '') !== $tableId || ($record['scene_id'] ?? '') !== $sceneId) {
            return;
        }
        unset($records[$objectId]);
        update_option(self::OPTION_NAME, $records, false);
    }

    public function clearScene(string $tableId, string $sceneId): void
    {
        $records = array_filter(
            $this->records(),
            static fn (array $record): bool => ($record['table_id'] ?? '') !== $tableId || ($record['scene_id'] ?? '') !== $sceneId
        );
        update_option(self::OPTION_NAME, $records, false);
    }

    /** @return array<string,array<string,mixed>> */
    private function records(): array
    {
        $records = get_option(self::OPTION_NAME, []);
        return is_array($records) ? $records : [];
    }
}
