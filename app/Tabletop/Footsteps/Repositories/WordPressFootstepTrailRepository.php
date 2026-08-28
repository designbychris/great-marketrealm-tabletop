<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Footsteps\Repositories;

use GreatMarketrealmTabletop\Tabletop\Footsteps\Contracts\FootstepTrailRepository;

defined('ABSPATH') || exit;

final class WordPressFootstepTrailRepository implements FootstepTrailRepository
{
    private const OPTION = 'gmrt_footstep_trails';
    private const MAX_PER_TOKEN = 6;

    public function forScene(string $tableId, string $sceneId): array
    {
        $result = [];
        foreach ($this->records()[$tableId][$sceneId] ?? [] as $tokenSteps) {
            if (! is_array($tokenSteps)) {
                continue;
            }
            foreach ($tokenSteps as $step) {
                if (is_array($step)) {
                    $result[] = $step;
                }
            }
        }

        usort($result, static fn (array $a, array $b): int =>
            ((int) ($a['sequence'] ?? 0)) <=> ((int) ($b['sequence'] ?? 0))
        );

        return $result;
    }

    public function append(string $tableId, string $sceneId, string $tokenId, array $step): void
    {
        $records = $this->records();
        $steps = $records[$tableId][$sceneId][$tokenId] ?? [];
        $steps = is_array($steps) ? $steps : [];
        $steps[] = $step;
        $records[$tableId][$sceneId][$tokenId] = array_slice($steps, -self::MAX_PER_TOKEN);
        update_option(self::OPTION, $records, false);
    }

    public function forgetToken(string $tableId, string $sceneId, string $tokenId): void
    {
        $records = $this->records();
        unset($records[$tableId][$sceneId][$tokenId]);
        if (($records[$tableId][$sceneId] ?? []) === []) {
            unset($records[$tableId][$sceneId]);
        }
        if (($records[$tableId] ?? []) === []) {
            unset($records[$tableId]);
        }
        update_option(self::OPTION, $records, false);
    }

    private function records(): array
    {
        $records = get_option(self::OPTION, []);
        return is_array($records) ? $records : [];
    }
}
