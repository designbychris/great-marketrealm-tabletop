<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Atlas\Services;

defined('ABSPATH') || exit;

/**
 * Removes WordPress-option state owned exclusively by one Scene.
 * Table-wide membership and Chronicle records deliberately survive.
 */
final class SceneShelfCleaner
{
    public function clear(string $tableId, string $sceneId): void
    {
        $tokenIds = array_keys($this->nestedScene('gmrt_table_tokens', $tableId, $sceneId));
        $encounterIds = array_keys($this->nestedScene('gmrt_table_encounters', $tableId, $sceneId));

        foreach ([
            'gmrt_table_tokens',
            'gmrt_table_encounters',
            'gmrt_fog_of_war',
            'gmrt_vision_barriers',
            'gmrt_footstep_trails',
            'gmrt_carried_lights',
            'gmrt_dropped_lights',
            'gmrt_environmental_lights',
            'gmrt_scene_thresholds',
        ] as $option) {
            $this->forgetScene($option, $tableId, $sceneId);
        }

        foreach ([
            'gmrt_token_vitality',
            'gmrt_death_saves',
            'gmrt_token_conditions',
            'gmrt_combat_arsenals',
            'gmrt_combat_profiles',
            'gmrt_damage_defenses',
            'gmrt_damage_profiles',
        ] as $option) {
            $this->forgetKeys($option, $tableId, $tokenIds);
        }

        $this->forgetKeys('gmrt_battle_events', $tableId, $encounterIds);
        $this->forgetMagicalLights($tableId, $sceneId);
        $this->forgetScene('gmrt_table_scenes', $tableId, $sceneId);
    }

    /** @return array<string,mixed> */
    private function nestedScene(string $option, string $tableId, string $sceneId): array
    {
        $all = get_option($option, []);
        $value = is_array($all) ? ($all[$tableId][$sceneId] ?? []) : [];
        return is_array($value) ? $value : [];
    }

    private function forgetScene(string $option, string $tableId, string $sceneId): void
    {
        $all = get_option($option, []);
        if (! is_array($all)) {
            return;
        }
        unset($all[$tableId][$sceneId]);
        if (($all[$tableId] ?? []) === []) {
            unset($all[$tableId]);
        }
        update_option($option, $all, false);
    }

    /** @param array<int,string> $keys */
    private function forgetKeys(string $option, string $tableId, array $keys): void
    {
        $all = get_option($option, []);
        if (! is_array($all)) {
            return;
        }
        foreach ($keys as $key) {
            unset($all[$tableId][$key]);
        }
        if (($all[$tableId] ?? []) === []) {
            unset($all[$tableId]);
        }
        update_option($option, $all, false);
    }

    private function forgetMagicalLights(string $tableId, string $sceneId): void
    {
        $all = get_option('gmrt_magical_lights', []);
        if (! is_array($all)) {
            return;
        }
        foreach ($all as $id => $row) {
            if (! is_array($row)) {
                continue;
            }
            if ((string) ($row['table_id'] ?? '') === $tableId && (string) ($row['scene_id'] ?? '') === $sceneId) {
                unset($all[$id]);
            }
        }
        update_option('gmrt_magical_lights', $all, false);
    }
}
