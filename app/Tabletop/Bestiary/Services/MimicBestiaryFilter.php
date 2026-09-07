<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Bestiary\Services;

use GreatMarketrealmTabletop\Tabletop\Bestiary\Models\BestiaryCreature;

defined('ABSPATH') || exit;

/**
 * Keeps Mimic conversion deliberately narrower than the full Bestiary.
 *
 * Canonical integrations may identify a Mimic through its name, creature kind,
 * or a trait. This lets imported MarketRealm Mimics participate without
 * maintaining a second Mimic catalogue.
 */
final class MimicBestiaryFilter
{
    public static function isMimic(BestiaryCreature $creature): bool
    {
        return self::arrayIsMimic($creature->toArray());
    }

    /** @param array<string,mixed> $creature */
    public static function arrayIsMimic(array $creature): bool
    {
        $haystack = [
            (string) ($creature['name'] ?? ''),
            (string) ($creature['kind'] ?? ''),
        ];
        foreach ((array) ($creature['traits'] ?? []) as $trait) {
            $haystack[] = (string) $trait;
        }

        foreach ($haystack as $value) {
            if (stripos($value, 'mimic') !== false) {
                return true;
            }
        }
        return false;
    }
}
