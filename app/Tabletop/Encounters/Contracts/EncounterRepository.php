<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Encounters\Contracts;

use GreatMarketrealmTabletop\Tabletop\Encounters\Models\Encounter;

defined('ABSPATH') || exit;

interface EncounterRepository
{
    /** @return array<int,Encounter> */
    public function forScene(string $tableId, string $sceneId): array;

    public function find(string $tableId, string $encounterId): ?Encounter;

    public function currentForScene(
        string $tableId,
        string $sceneId
    ): ?Encounter;

    public function save(Encounter $encounter): void;
}
