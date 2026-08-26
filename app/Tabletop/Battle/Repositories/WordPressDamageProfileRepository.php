<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Repositories;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DamageProfileRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageProfile;

defined('ABSPATH') || exit;

final class WordPressDamageProfileRepository implements DamageProfileRepository
{
    private const OPTION = 'gmrt_damage_profiles';

    public function forToken(
        string $tableId,
        string $tokenId
    ): DamageProfile {
        $records = $this->records();
        $record = $records[$tableId][$tokenId] ?? null;

        return is_array($record)
            ? DamageProfile::reconstitute($record)
            : new DamageProfile($tokenId);
    }

    public function save(
        string $tableId,
        DamageProfile $profile
    ): void {
        $records = $this->records();

        $records[$tableId][$profile->tokenId()]
            = $profile->toArray();

        update_option(
            self::OPTION,
            $records,
            false
        );
    }

    /** @return array<string,mixed> */
    private function records(): array
    {
        $records = get_option(
            self::OPTION,
            []
        );

        return is_array($records)
            ? $records
            : [];
    }
}
