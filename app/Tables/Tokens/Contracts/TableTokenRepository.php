<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Tokens\Contracts;

use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;

defined('ABSPATH') || exit;

interface TableTokenRepository
{
    /** @return array<int,TableToken> */
    public function forScene(string $tableId, string $sceneId): array;

    public function find(
        string $tableId,
        string $tokenId
    ): ?TableToken;

    public function save(TableToken $token): void;

    public function delete(string $tableId, string $tokenId): void;
}
