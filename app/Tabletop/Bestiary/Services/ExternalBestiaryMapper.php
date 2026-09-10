<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Bestiary\Services;

use GreatMarketrealmTabletop\Tabletop\Bestiary\Models\BestiaryCreature;

defined('ABSPATH') || exit;

final class ExternalBestiaryMapper
{
    private BestiaryCompatibilityNormalizer $compatibility;

    public function __construct(?BestiaryCompatibilityNormalizer $compatibility = null)
    {
        $this->compatibility = $compatibility ?? new BestiaryCompatibilityNormalizer();
    }

    /** @param array<string,mixed> $record */
    public function map(array $record): ?BestiaryCreature
    {
        $id = trim((string) ($record['id'] ?? $record['key'] ?? $record['slug'] ?? ''));
        $name = trim((string) ($record['name'] ?? $record['label'] ?? ''));
        $stats = is_array($record['stats'] ?? null) ? $record['stats'] : [];
        $combat = is_array($record['combat'] ?? null) ? $record['combat'] : [];

        $ac = (int) (
            $record['armor_class']
            ?? $record['ac']
            ?? $record['armorClass']
            ?? $stats['armor_class']
            ?? $stats['ac']
            ?? $combat['armor_class']
            ?? $combat['ac']
            ?? 0
        );
        $hp = (int) (
            $record['hit_points']
            ?? $record['hp']
            ?? $record['hitPoints']
            ?? $stats['hit_points']
            ?? $stats['hp']
            ?? $combat['hit_points']
            ?? $combat['hp']
            ?? 0
        );
        if ($id === '' || $name === '' || $ac < 1 || $hp < 1) return null;

        $attacks = $this->attacks($record['attacks'] ?? $combat['attacks'] ?? []);
        return new BestiaryCreature(
            $id,
            $name,
            (string) ($record['kind'] ?? $record['creature_type'] ?? $record['type'] ?? 'creature'),
            (string) ($record['size'] ?? 'Unknown'),
            $ac,
            $hp,
            max(0, (int) (
                $record['speed_feet']
                ?? $record['speed']
                ?? $record['speedFeet']
                ?? $stats['speed_feet']
                ?? $stats['speed']
                ?? $combat['speed_feet']
                ?? $combat['speed']
                ?? 0
            )),
            $attacks,
            $this->list($record['resistances'] ?? []),
            $this->list($record['immunities'] ?? []),
            $this->list($record['weaknesses'] ?? $record['vulnerabilities'] ?? []),
            $this->list($record['traits'] ?? []),
            is_array($record['ability_scores'] ?? null) ? $record['ability_scores'] : [],
            is_array($record['saving_throws'] ?? null) ? $record['saving_throws'] : [],
            $this->list($record['senses'] ?? []),
            (string) ($record['source'] ?? 'external-bestiary'),
            (string) ($record['expansion_key'] ?? ''),
            (string) ($record['expansion_label'] ?? ''),
            (string) ($record['canonical_id'] ?? ''),
            $this->list($record['reference_actions'] ?? [])
        );
    }


    /** @return array<int,array<string,mixed>> */
    private function attacks(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $attacks = [];
        foreach ($value as $index => $attack) {
            if (! is_array($attack)) {
                continue;
            }

            $damage = is_array($attack['damage'] ?? null) ? $attack['damage'] : [];
            $damageType = (string) (
                $damage['type']
                ?? $attack['damage_type']
                ?? $attack['damageType']
                ?? 'bludgeoning'
            );

            try {
                $damageType = $this->compatibility->damageType($damageType);
            } catch (\InvalidArgumentException) {
                // Preserve the original value here. The deployment boundary will
                // return a precise compatibility message rather than silently
                // dropping the creature from the Keeper's catalogue.
            }

            $damage['type'] = $damageType;
            if (! isset($damage['dice_count']) && isset($attack['damage_dice_count'])) {
                $damage['dice_count'] = (int) $attack['damage_dice_count'];
            }
            if (! isset($damage['die_sides']) && isset($attack['damage_die_sides'])) {
                $damage['die_sides'] = (int) $attack['damage_die_sides'];
            }
            if (! isset($damage['modifier']) && isset($attack['damage_modifier'])) {
                $damage['modifier'] = (int) $attack['damage_modifier'];
            }

            $attacks[] = array_merge($attack, [
                'id' => (string) ($attack['id'] ?? $attack['key'] ?? 'attack-' . ($index + 1)),
                'name' => (string) ($attack['name'] ?? $attack['label'] ?? 'Attack ' . ($index + 1)),
                'kind' => $this->compatibility->attackKind((string) ($attack['kind'] ?? $attack['type'] ?? 'improvised')),
                'damage' => $damage,
            ]);
        }

        return $attacks;
    }

    /** @return array<int,string> */
    private function list(mixed $value): array
    {
        if (is_string($value)) $value = preg_split('/\s*,\s*/', trim($value)) ?: [];
        if (! is_array($value)) return [];
        return array_values(array_filter(array_map(static fn ($item): string => trim((string) $item), $value)));
    }
}
