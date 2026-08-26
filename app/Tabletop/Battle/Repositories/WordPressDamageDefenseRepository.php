<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Repositories;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DamageDefenseRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageDefenseProfile;

defined('ABSPATH') || exit;

final class WordPressDamageDefenseRepository implements DamageDefenseRepository
{
    private const OPTION = 'gmrt_damage_defenses';

    public function forToken(
        string $tableId,
        string $tokenId
    ): DamageDefenseProfile {
        $records = $this->records();
        $record = $records[$tableId][$tokenId] ?? null;

        return is_array($record)
            ? DamageDefenseProfile::reconstitute($record)
            : new DamageDefenseProfile($tokenId);
    }

    public function save(
        string $tableId,
        DamageDefenseProfile $profile
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
