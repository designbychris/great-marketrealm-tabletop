<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\SceneObjects\Repositories;

defined('ABSPATH') || exit;

final class WordPressCustomFurnitureRepository
{
    private const OPTION = 'gmrt_custom_furniture_catalogue';

    /** @return array<string,array<string,mixed>> */
    public function all(): array
    {
        $records = get_option(self::OPTION, []);
        if (! is_array($records)) {
            return [];
        }

        $clean = [];
        foreach ($records as $kind => $record) {
            if (! is_array($record)) {
                continue;
            }
            $key = sanitize_key((string) $kind);
            if ($key === '') {
                continue;
            }
            $clean[$key] = $record;
        }
        ksort($clean);
        return $clean;
    }

    /** @return array<string,mixed>|null */
    public function find(string $kind): ?array
    {
        $kind = sanitize_key($kind);
        return $this->all()[$kind] ?? null;
    }

    /** @param array<string,mixed> $definition */
    public function save(string $kind, array $definition): void
    {
        $kind = sanitize_key($kind);
        if ($kind === '') {
            return;
        }

        $records = $this->all();
        $records[$kind] = $definition;
        ksort($records);
        update_option(self::OPTION, $records, false);
    }

    public function remove(string $kind): void
    {
        $kind = sanitize_key($kind);
        $records = $this->all();
        if (! array_key_exists($kind, $records)) {
            return;
        }
        unset($records[$kind]);
        update_option(self::OPTION, $records, false);
    }
}
