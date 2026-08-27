<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Fog\Repositories;

use GreatMarketrealmTabletop\Tabletop\Fog\Contracts\FogOfWarRepository;
use GreatMarketrealmTabletop\Tabletop\Fog\Models\FogOfWarState;

defined('ABSPATH') || exit;

final class WordPressFogOfWarRepository implements FogOfWarRepository
{
    private const OPTION = 'gmrt_fog_of_war';

    public function forScene(
        string $tableId,
        string $sceneId
    ): FogOfWarState {
        $records = $this->records();
        $record = $records[$tableId][$sceneId] ?? null;

        return is_array($record)
            ? FogOfWarState::reconstitute($record)
            : new FogOfWarState($sceneId);
    }

    public function save(
        string $tableId,
        FogOfWarState $state
    ): void {
        $records = $this->records();
        $records[$tableId][$state->sceneId()]
            = $state->toArray();

        update_option(
            self::OPTION,
            $records,
            false
        );
    }

    /** @return array<string,mixed> */
    private function records(): array
    {
        $records = get_option(self::OPTION, []);
        return is_array($records) ? $records : [];
    }
}
