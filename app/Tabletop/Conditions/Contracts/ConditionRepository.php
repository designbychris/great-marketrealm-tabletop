<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Conditions\Contracts;

use GreatMarketrealmTabletop\Tabletop\Conditions\Models\TokenCondition;

defined('ABSPATH') || exit;

interface ConditionRepository
{
    /** @return array<int,TokenCondition> */
    public function forToken(string $tableId, string $tokenId): array;

    public function save(
        string $tableId,
        TokenCondition $condition
    ): void;

    public function remove(
        string $tableId,
        string $tokenId,
        string $condition
    ): void;
}
