<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Repositories;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\CombatProfileRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;

defined('ABSPATH') || exit;

final class WordPressCombatProfileRepository implements CombatProfileRepository
{
    private const OPTION = 'gmrt_combat_profiles';

    public function forToken(
        string $tableId,
        string $tokenId
    ): CombatProfile {
        $records = $this->records();
        $record = $records[$tableId][$tokenId] ?? null;

        if (! is_array($record)) {
            return new CombatProfile($tokenId);
        }

        return CombatProfile::reconstitute($record);
    }

    public function save(
        string $tableId,
        CombatProfile $profile
    ): void {
        $records = $this->records();
        $records[$tableId][$profile->tokenId()]
            = $profile->toArray();

        update_option(self::OPTION, $records, false);
    }

    /** @return array<string,mixed> */
    private function records(): array
    {
        $records = get_option(self::OPTION, []);

        return is_array($records) ? $records : [];
    }
}
