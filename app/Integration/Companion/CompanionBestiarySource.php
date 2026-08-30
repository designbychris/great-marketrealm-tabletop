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
        $records = apply_filters('gmrc_tabletop_bestiary_records', []);
        return is_array($records) ? array_values(array_filter($records, 'is_array')) : [];
    }
}
