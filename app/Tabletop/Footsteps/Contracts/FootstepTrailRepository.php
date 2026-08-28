<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Footsteps\Contracts;

defined('ABSPATH') || exit;

interface FootstepTrailRepository
{
    /** @return array<int,array<string,mixed>> */
    public function forScene(string $tableId, string $sceneId): array;

    /** @param array<string,mixed> $step */
    public function append(string $tableId, string $sceneId, string $tokenId, array $step): void;

    public function forgetToken(string $tableId, string $sceneId, string $tokenId): void;
}
