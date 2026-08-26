<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Repositories;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\VitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\Vitality;

defined('ABSPATH') || exit;

final class WordPressVitalityRepository implements VitalityRepository
{
    private const OPTION = 'gmrt_token_vitality';

    public function forToken(
        string $tableId,
        string $tokenId
    ): Vitality {
        $records = $this->records();
        $record = $records[$tableId][$tokenId] ?? null;

        if (! is_array($record)) {
            return new Vitality(
                $tokenId,
                10,
                10,
                0
            );
        }

        return Vitality::reconstitute($record);
    }

    public function save(
        string $tableId,
        Vitality $vitality
    ): void {
        $records = $this->records();

        $records[$tableId][$vitality->tokenId()]
            = $vitality->toArray();

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
