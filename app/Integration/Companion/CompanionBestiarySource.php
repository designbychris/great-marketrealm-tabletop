<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Integration\Companion;

use GreatMarketrealmTabletop\Tabletop\Bestiary\Contracts\BestiarySource;

defined('ABSPATH') || exit;

/**
 * IV.29D adapter boundary. Tabletop knows only a WordPress filter contract and
 * never imports Companion classes or repositories directly.
 */
final class CompanionBestiarySource implements BestiarySource
{
    public function available(): bool
    {
        return function_exists('apply_filters')
            && (function_exists('gmrc') || class_exists('GreatMarketrealmCompanion\\Core\\Application', false));
    }

    public function label(): string { return 'Great Marketrealm Companion'; }

    public function records(): array
    {
        if (! $this->available()) return [];

        $shelves = [
            apply_filters('gmrc_tabletop_bestiary_records', []),
            apply_filters('gmrc_tabletop_bestiary_supplemental_records', []),
            apply_filters('gmrc_tabletop_bestiary_workshop_records', []),
        ];

        $records = [];
        foreach ($shelves as $shelf) {
            if (! is_array($shelf)) {
                continue;
            }
            foreach ($shelf as $record) {
                if (! is_array($record)) {
                    continue;
                }
                $id = trim((string) ($record['id'] ?? $record['key'] ?? $record['slug'] ?? ''));
                if ($id === '') {
                    continue;
                }
                // Later shelves may enrich an already-published creature, but
                // never create a duplicate row in the Keeper's Menagerie.
                $records[$id] = $record;
            }
        }

        return array_values($records);
    }
}
