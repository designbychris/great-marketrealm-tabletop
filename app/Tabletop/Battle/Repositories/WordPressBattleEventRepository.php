<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Repositories;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\BattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;

defined('ABSPATH') || exit;

final class WordPressBattleEventRepository implements BattleEventRepository
{
    private const OPTION = 'gmrt_battle_events';

    public function forEncounter(
        string $tableId,
        string $encounterId
    ): array {
        $events = [];

        foreach (
            $this->records()[$tableId][$encounterId] ?? []
            as $record
        ) {
            if (is_array($record)) {
                $events[] = BattleEvent::reconstitute($record);
            }
        }

        return $events;
    }

    public function append(BattleEvent $event): void
    {
        $record = $event->toArray();
        $records = $this->records();

        $records[(string) $record['table_id']]
            [(string) $record['encounter_id']][] = $record;

        update_option(
            self::OPTION,
            $records,
            false
        );
    }

    /** @return array<string,mixed> */
    private function records(): array
    {
        $records = get_option(self::OPTION, []);

        return is_array($records) ? $records : [];
    }
}
