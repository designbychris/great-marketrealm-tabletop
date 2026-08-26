<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Repositories;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DeathSaveRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DeathSaveState;

defined('ABSPATH') || exit;

final class WordPressDeathSaveRepository implements DeathSaveRepository
{
    private const OPTION = 'gmrt_death_saves';

    public function forToken(
        string $tableId,
        string $tokenId
    ): DeathSaveState {
        $records = $this->records();
        $record = $records[$tableId][$tokenId] ?? null;

        return is_array($record)
            ? DeathSaveState::reconstitute($record)
            : new DeathSaveState($tokenId);
    }

    public function save(
        string $tableId,
        DeathSaveState $state
    ): void {
        $records = $this->records();
        $records[$tableId][$state->tokenId()] = $state->toArray();

        update_option(self::OPTION, $records, false);
    }

    /** @return array<string,mixed> */
    private function records(): array
    {
        $records = get_option(self::OPTION, []);

        return is_array($records) ? $records : [];
    }
}
