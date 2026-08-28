<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Footsteps\Presentation;

use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tabletop\Fog\Services\FogCellMapper;

defined('ABSPATH') || exit;

final class FootstepTrailProjector
{
    /**
     * @param array<int,array<string,mixed>> $steps
     * @param array<string,mixed> $fog
     * @param array<int,array{key:string,hex:string}> $coloursByUser
     * @return array<int,array<string,mixed>>
     */
    public function project(
        TableScene $scene,
        array $steps,
        int $viewerUserId,
        bool $dungeonMaster,
        array $fog,
        array $coloursByUser
    ): array {
        $visible = array_fill_keys($fog['visible'] ?? [], true);
        $explored = array_fill_keys($fog['explored'] ?? [], true);
        $fogEnabled = ! empty($fog['enabled']);
        $mapper = new FogCellMapper();
        $kept = [];

        foreach ($steps as $step) {
            $owner = (int) ($step['controller_user_id'] ?? 0);
            if ($owner < 1) {
                continue;
            }

            $cell = $mapper->cellFor(
                $scene,
                (float) ($step['x'] ?? 0),
                (float) ($step['y'] ?? 0)
            );
            $cellKey = FogCellMapper::key($cell['column'], $cell['row']);

            $allowed = $dungeonMaster
                || ! $fogEnabled
                || isset($visible[$cellKey])
                || ($owner === $viewerUserId && isset($explored[$cellKey]));

            if (! $allowed) {
                continue;
            }

            $colour = $coloursByUser[$owner] ?? ['key' => 'market-teal', 'hex' => '#65b9ae'];
            $kept[] = [
                'token_id' => (string) ($step['token_id'] ?? ''),
                'controller_user_id' => $owner,
                'x' => (float) ($step['x'] ?? 0),
                'y' => (float) ($step['y'] ?? 0),
                'angle' => (float) ($step['angle'] ?? 0),
                'sequence' => (int) ($step['sequence'] ?? 0),
                'table_colour' => $colour['key'],
                'table_colour_hex' => $colour['hex'],
                'memory' => $fogEnabled && ! isset($visible[$cellKey]),
            ];
        }

        // Fade within each token's bounded trail: newest is clearest.
        $byToken = [];
        foreach ($kept as $index => $step) {
            $byToken[$step['token_id']][] = $index;
        }
        foreach ($byToken as $indexes) {
            $count = count($indexes);
            foreach ($indexes as $position => $index) {
                $age = max(0, $count - 1 - $position);
                $kept[$index]['opacity'] = max(0.12, 0.42 - ($age * 0.055));
                if (! empty($kept[$index]['memory'])) {
                    $kept[$index]['opacity'] *= 0.58;
                }
            }
        }

        return array_values($kept);
    }
}
