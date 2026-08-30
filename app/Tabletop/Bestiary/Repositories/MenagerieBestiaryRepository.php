<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Bestiary\Repositories;

use GreatMarketrealmTabletop\Tabletop\Bestiary\Contracts\BestiaryRepository;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Contracts\BestiarySource;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Models\BestiaryCreature;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Services\ExternalBestiaryMapper;

defined('ABSPATH') || exit;

/** Training shelf + zero or more external shelves, deduplicated by stable ID. */
final class MenagerieBestiaryRepository implements BestiaryRepository
{
    /** @param array<int,BestiarySource> $sources */
    public function __construct(
        private BestiaryRepository $fallback,
        private array $sources = [],
        private ?ExternalBestiaryMapper $mapper = null
    ) { $this->mapper ??= new ExternalBestiaryMapper(); }

    /** @return array<int,BestiaryCreature> */
    public function all(): array
    {
        $records = [];
        foreach ($this->fallback->all() as $creature) $records[$creature->id()] = $creature;
        foreach ($this->sources as $source) {
            if (! $source instanceof BestiarySource || ! $source->available()) continue;
            foreach ($source->records() as $record) {
                $creature = $this->mapper->map($record);
                if ($creature !== null) $records[$creature->id()] = $creature;
            }
        }
        uasort($records, static fn (BestiaryCreature $a, BestiaryCreature $b): int => strcasecmp($a->name(), $b->name()));
        return array_values($records);
    }

    public function find(string $id): ?BestiaryCreature
    {
        foreach ($this->all() as $creature) if ($creature->id() === $id) return $creature;
        return null;
    }
}
