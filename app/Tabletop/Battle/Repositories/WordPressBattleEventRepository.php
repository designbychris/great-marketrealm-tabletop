<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Repositories;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\BattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;
use GreatMarketrealmTabletop\Tabletop\Sessions\Repositories\WordPressTableSessionRepository;

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

    /** @return array<int,BattleEvent> */
    public function forSession(string $tableId, string $sessionId): array
    {
        $events = [];
        foreach (($this->records()[$tableId] ?? []) as $encounterRecords) {
            foreach ((array) $encounterRecords as $record) {
                if (is_array($record) && (string) ($record['session_id'] ?? '') === $sessionId) {
                    $events[] = BattleEvent::reconstitute($record);
                }
            }
        }
        return $events;
    }

    public function append(BattleEvent $event): void
    {
        if ($event->sessionId() === '') {
            $initial = $event->toArray();
            $session = (new WordPressTableSessionRepository())->currentForTable((string) ($initial['table_id'] ?? ''));
            if ($session !== null) {
                $event = $event->withSessionId($session->id());
            }
        }
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
