<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Encounters\Repositories;

use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tabletop\Encounters\Models\Encounter;

defined('ABSPATH') || exit;

final class WordPressEncounterRepository implements EncounterRepository
{
    private const OPTION = 'gmrt_table_encounters';

    /** @return array<int,Encounter> */
    public function forScene(string $tableId, string $sceneId): array
    {
        $encounters = [];

        foreach ($this->records()[$tableId][$sceneId] ?? [] as $record) {
            if (is_array($record)) {
                $encounters[] = Encounter::reconstitute($record);
            }
        }

        return $encounters;
    }

    /** @return array<int,Encounter> */
    public function forSession(string $tableId, string $sessionId): array
    {
        $matches = [];
        foreach ($this->records()[$tableId] ?? [] as $records) {
            foreach ((array) $records as $record) {
                if (is_array($record) && (string) ($record['session_id'] ?? '') === $sessionId) {
                    $matches[] = Encounter::reconstitute($record);
                }
            }
        }
        return $matches;
    }

    public function find(string $tableId, string $encounterId): ?Encounter
    {
        foreach ($this->records()[$tableId] ?? [] as $records) {
            if (
                is_array($records)
                && isset($records[$encounterId])
                && is_array($records[$encounterId])
            ) {
                return Encounter::reconstitute($records[$encounterId]);
            }
        }

        return null;
    }

    public function currentForScene(
        string $tableId,
        string $sceneId
    ): ?Encounter {
        $encounters = array_reverse(
            $this->forScene($tableId, $sceneId)
        );

        foreach ($encounters as $encounter) {
            if (! $encounter->isEnded()) {
                return $encounter;
            }
        }

        return null;
    }

    public function save(Encounter $encounter): void
    {
        $records = $this->records();

        $records[$encounter->tableId()]
            [$encounter->sceneId()]
            [$encounter->id()] = $encounter->toArray();

        update_option(self::OPTION, $records, false);
    }

    /**
     * @return array<string,array<string,array<string,array<string,mixed>>>>
     */
    private function records(): array
    {
        $records = get_option(self::OPTION, []);
        return is_array($records) ? $records : [];
    }
}
