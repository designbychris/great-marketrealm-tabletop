<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Bestiary\Services;

use GreatMarketrealmTabletop\Tabletop\Bestiary\Models\BestiaryCreature;

defined('ABSPATH') || exit;

final class ExternalBestiaryMapper
{
    /** @param array<string,mixed> $record */
    public function map(array $record): ?BestiaryCreature
    {
        $id = trim((string) ($record['id'] ?? $record['key'] ?? ''));
        $name = trim((string) ($record['name'] ?? ''));
        $ac = (int) ($record['armor_class'] ?? $record['ac'] ?? 0);
        $hp = (int) ($record['hit_points'] ?? $record['hp'] ?? 0);
        if ($id === '' || $name === '' || $ac < 1 || $hp < 1) return null;

        $attacks = is_array($record['attacks'] ?? null) ? $record['attacks'] : [];
        return new BestiaryCreature(
            $id,
            $name,
            (string) ($record['kind'] ?? $record['creature_type'] ?? $record['type'] ?? 'creature'),
            (string) ($record['size'] ?? 'Unknown'),
            $ac,
            $hp,
            max(0, (int) ($record['speed_feet'] ?? 0)),
            $attacks,
            $this->list($record['resistances'] ?? []),
            $this->list($record['immunities'] ?? []),
            $this->list($record['weaknesses'] ?? $record['vulnerabilities'] ?? []),
            $this->list($record['traits'] ?? []),
            is_array($record['ability_scores'] ?? null) ? $record['ability_scores'] : [],
            is_array($record['saving_throws'] ?? null) ? $record['saving_throws'] : [],
            $this->list($record['senses'] ?? []),
            (string) ($record['source'] ?? 'external-bestiary')
        );
    }

    /** @return array<int,string> */
    private function list(mixed $value): array
    {
        if (is_string($value)) $value = preg_split('/\s*,\s*/', trim($value)) ?: [];
        if (! is_array($value)) return [];
        return array_values(array_filter(array_map(static fn ($item): string => trim((string) $item), $value)));
    }
}
