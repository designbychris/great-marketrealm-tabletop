<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Sessions\Repositories;

use GreatMarketrealmTabletop\Tabletop\Sessions\Models\SessionRecap;

final class WordPressSessionRecapRepository
{
    private const OPTION = 'gmrt_session_recaps';

    public function find(string $tableId, string $sessionId): ?SessionRecap
    {
        $records = get_option(self::OPTION, []);
        $record = is_array($records) ? ($records[$tableId][$sessionId] ?? null) : null;
        return is_array($record) ? SessionRecap::reconstitute($record) : null;
    }

    public function save(SessionRecap $recap): void
    {
        $records = get_option(self::OPTION, []);
        $records = is_array($records) ? $records : [];
        $records[$recap->tableId()][$recap->sessionId()] = $recap->toArray();
        update_option(self::OPTION, $records, false);
    }
}
