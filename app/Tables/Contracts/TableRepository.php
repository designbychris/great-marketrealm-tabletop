<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Contracts;

use GreatMarketrealmTabletop\Tables\Models\Table;

defined('ABSPATH') || exit;

interface TableRepository
{
    /** @return array<int,Table> */
    public function all(): array;

    public function find(string $id): ?Table;

    public function save(Table $table): void;

    public function activeCount(): int;
}
