<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Campaigns;

use GreatMarketrealmTabletop\Tables\Services\TableRegistry;
use RuntimeException;

defined('ABSPATH') || exit;

/**
 * Permanently removes a Keeper-owned Tabletop and its table-keyed records.
 *
 * This is deliberately campaign-scoped: options whose first key is a Table ID
 * are purged together so an abandoned campaign does not remain on the shelf.
 */
final class WordPressTabletopRemover
{
    /** @var array<int,string> */
    private const TABLE_KEYED_OPTIONS = [
        'gmrt_tables',
        'gmrt_table_memberships',
        'gmrt_table_scenes',
        'gmrt_table_tokens',
        'gmrt_table_encounters',
        'gmrt_chamber_chronicle',
        'gmrt_dungeon_forge_plans',
        'gmrt_vision_barriers',
        'gmrt_scene_thresholds',
        'gmrt_fog_of_war',
        'gmrt_carried_lights',
        'gmrt_dropped_lights',
        'gmrt_magical_lights',
        'gmrt_environmental_lights',
        'gmrt_footstep_trails',
    ];

    public function __construct(private TableRegistry $tables) {}

    public function remove(string $tableId, int $userId): void
    {
        $tableId = trim($tableId);
        $table = $this->tables->find($tableId);

        if ($table === null) {
            throw new RuntimeException('That Tabletop could not be found.');
        }
        if ($userId < 1 || $table->dungeonMasterUserId() !== $userId) {
            throw new RuntimeException('Only this Tabletop\'s Dungeon Master may remove it.');
        }

        foreach (self::TABLE_KEYED_OPTIONS as $option) {
            $records = get_option($option, []);
            if (! is_array($records) || ! array_key_exists($tableId, $records)) {
                continue;
            }
            unset($records[$tableId]);
            update_option($option, $records, false);
        }
    }
}
