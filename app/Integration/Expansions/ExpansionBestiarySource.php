<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Integration\Expansions;

use GreatMarketrealmTabletop\Integration\Companion\CompanionCampaignBridge;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Contracts\BestiarySource;
use Throwable;

defined('ABSPATH') || exit;

/**
 * V.11B — campaign-scoped GMREXP Bestiary shelf.
 *
 * Tabletop never copies expansion monsters. The linked Companion Campaign
 * supplies the shared Almanac keys; GMREXP remains the canonical definition
 * owner and its Active Content API remains the site-activation authority.
 */
final class ExpansionBestiarySource implements BestiarySource
{
    public const MINIMUM_ACTIVE_CONTENT_API_VERSION = '1.0.0';

    public function __construct(
        private string $tableId,
        private int $viewerUserId,
        private ?CompanionCampaignBridge $campaigns = null
    ) {
        $this->campaigns ??= new CompanionCampaignBridge();
    }

    public function available(): bool
    {
        return $this->tableId !== ''
            && $this->viewerUserId > 0
            && $this->catalogue() !== null
            && $this->sharedExpansionKeys() !== [];
    }

    public function label(): string
    {
        return 'Great MarketRealm Expansions';
    }

    /** @return array<int,array<string,mixed>> */
    public function records(): array
    {
        $catalogue = $this->catalogue();
        $keys = $this->sharedExpansionKeys();

        if ($catalogue === null || $keys === []) {
            return [];
        }

        $records = [];
        foreach ($keys as $expansionKey) {
            try {
                $entries = $catalogue->fromExpansionAndType(
                    $expansionKey,
                    'monster'
                );
            } catch (Throwable) {
                continue;
            }

            if (! is_array($entries)) {
                continue;
            }

            foreach ($entries as $entry) {
                $record = $this->project($entry, $expansionKey);
                if ($record !== null) {
                    $records[(string) $record['id']] = $record;
                }
            }
        }

        return array_values($records);
    }

    /** @return string[] */
    private function sharedExpansionKeys(): array
    {
        try {
            $campaign = $this->campaigns?->linkedCampaign(
                $this->tableId,
                $this->viewerUserId
            );
        } catch (Throwable) {
            return [];
        }

        if (! is_array($campaign)) {
            return [];
        }

        $keys = [];
        foreach ((array) ($campaign['expansion_keys'] ?? []) as $key) {
            if (! is_scalar($key)) {
                continue;
            }

            $normalised = $this->normaliseKey((string) $key);
            if ($normalised !== '') {
                $keys[$normalised] = true;
            }
        }

        $keys = array_keys($keys);
        sort($keys);

        return $keys;
    }

    private function catalogue(): ?object
    {
        $function = 'GreatMarketrealmExpansions\\active_content';
        if (! function_exists($function)) {
            return null;
        }

        try {
            $catalogue = $function();
        } catch (Throwable) {
            return null;
        }

        if (! is_object($catalogue)
            || ! method_exists($catalogue, 'fromExpansionAndType')) {
            return null;
        }

        if (method_exists($catalogue, 'apiVersion')) {
            try {
                $version = (string) $catalogue->apiVersion();
            } catch (Throwable) {
                return null;
            }

            if ($version === '' || version_compare(
                $version,
                self::MINIMUM_ACTIVE_CONTENT_API_VERSION,
                '<'
            )) {
                return null;
            }
        }

        return $catalogue;
    }

    /** @return array<string,mixed>|null */
    private function project(mixed $entry, string $fallbackExpansion): ?array
    {
        if (! is_object($entry)) {
            return null;
        }

        foreach (['id', 'type', 'key', 'expansionKey', 'data'] as $method) {
            if (! method_exists($entry, $method)) {
                return null;
            }
        }

        try {
            if ((string) $entry->type() !== 'monster') {
                return null;
            }

            $id = trim((string) $entry->id());
            $key = $this->normaliseKey((string) $entry->key());
            $expansion = $this->normaliseKey((string) $entry->expansionKey());
            $data = $entry->data();
        } catch (Throwable) {
            return null;
        }

        if ($expansion === '') {
            $expansion = $this->normaliseKey($fallbackExpansion);
        }

        if ($id === '' || $key === '' || $expansion === '' || ! is_array($data)) {
            return null;
        }

        $name = trim((string) ($data['name'] ?? ''));
        $armour = is_array($data['armour_class'] ?? null) ? $data['armour_class'] : [];
        $hitPoints = is_array($data['hit_points'] ?? null) ? $data['hit_points'] : [];
        $speed = is_array($data['speed'] ?? null) ? $data['speed'] : [];

        $ac = (int) ($armour['value'] ?? 0);
        $hp = (int) ($hitPoints['average'] ?? 0);

        if ($name === '' || $ac < 1 || $hp < 1) {
            return null;
        }

        return [
            'id' => $id,
            'key' => $key,
            'name' => $name,
            'kind' => (string) ($data['creature_type'] ?? 'creature'),
            'size' => (string) ($data['size'] ?? 'Unknown'),
            'armor_class' => $ac,
            'hit_points' => $hp,
            'speed_feet' => max(0, (int) ($speed['walk'] ?? 0)),
            'attacks' => $this->structuredAttacks((array) ($data['actions'] ?? [])),
            'resistances' => $this->stringList($data['damage_resistances'] ?? []),
            'immunities' => $this->stringList($data['damage_immunities'] ?? []),
            'weaknesses' => $this->stringList($data['damage_vulnerabilities'] ?? []),
            'traits' => $this->namedDescriptions($data['traits'] ?? []),
            'ability_scores' => is_array($data['abilities'] ?? null) ? $data['abilities'] : [],
            'saving_throws' => is_array($data['saving_throws'] ?? null) ? $data['saving_throws'] : [],
            'senses' => $this->senseList($data['senses'] ?? []),
            'source' => 'gmrexp:' . $id,
            'canonical_id' => $id,
            'expansion_key' => $expansion,
            'expansion_label' => $this->labelForKey($expansion),
            'reference_actions' => $this->namedDescriptions($data['actions'] ?? []),
        ];
    }

    /**
     * Only mechanically structured action rules cross into the live attack
     * arsenal. Free-form sourcebook prose remains visible in GMREXP rather
     * than being guessed into dice/range mechanics by Tabletop.
     *
     * @param array<int,mixed> $actions
     * @return array<int,array<string,mixed>>
     */
    private function structuredAttacks(array $actions): array
    {
        $attacks = [];
        foreach ($actions as $action) {
            if (! is_array($action)) {
                continue;
            }

            $rules = is_array($action['rules'] ?? null) ? $action['rules'] : [];
            foreach ($rules as $rule) {
                if (! is_array($rule)
                    || strtolower((string) ($rule['kind'] ?? '')) !== 'attack') {
                    continue;
                }

                $damage = is_array($rule['damage'] ?? null) ? $rule['damage'] : [];
                $id = trim((string) ($action['key'] ?? $rule['key'] ?? ''));
                $name = trim((string) ($action['name'] ?? $rule['name'] ?? ''));

                if ($id === '' || $name === '') {
                    continue;
                }

                $attacks[] = [
                    'id' => $id,
                    'name' => $name,
                    'kind' => (string) ($rule['attack_kind'] ?? $rule['type'] ?? 'improvised'),
                    'attack_modifier' => (int) ($rule['attack_modifier'] ?? 0),
                    'range_feet' => max(5, (int) ($rule['range_feet'] ?? 5)),
                    'long_range_feet' => max(5, (int) ($rule['long_range_feet'] ?? ($rule['range_feet'] ?? 5))),
                    'damage' => $damage,
                    'properties' => is_array($rule['properties'] ?? null) ? $rule['properties'] : [],
                ];
            }
        }

        return $attacks;
    }

    /** @return string[] */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $item): string => is_scalar($item) ? trim((string) $item) : '',
            $value
        )));
    }

    /** @return string[] */
    private function namedDescriptions(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $records = [];
        foreach ($value as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $name = trim((string) ($entry['name'] ?? ''));
            $description = trim((string) ($entry['description'] ?? ''));
            if ($name === '' && $description === '') {
                continue;
            }

            $records[] = $description === ''
                ? $name
                : ($name === '' ? $description : $name . ': ' . $description);
        }

        return $records;
    }

    /** @return string[] */
    private function senseList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $records = [];
        foreach ($value as $sense => $distance) {
            if (! is_scalar($distance)) {
                continue;
            }

            $label = ucwords(str_replace('_', ' ', (string) $sense));
            $records[] = $sense === 'passive_perception'
                ? $label . ' ' . (string) $distance
                : $label . ' ' . (string) $distance . ' ft';
        }

        return $records;
    }

    private function normaliseKey(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_-]+/', '-', $value);
        $value = is_string($value) ? preg_replace('/-+/', '-', $value) : '';

        return is_string($value) ? trim($value, '-') : '';
    }

    private function labelForKey(string $key): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $key));
    }
}
