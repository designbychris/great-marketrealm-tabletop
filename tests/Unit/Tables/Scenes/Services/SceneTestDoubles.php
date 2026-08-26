<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Scenes\Services;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneIdGenerator;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;

final class SceneTableRepository implements TableRepository
{
    /** @var array<string,Table> */
    public array $items = [];
    public function all(): array { return array_values($this->items); }
    public function find(string $id): ?Table { return $this->items[$id] ?? null; }
    public function save(Table $table): void { $this->items[$table->id()] = $table; }
    public function activeCount(): int { return count(array_filter($this->items, fn(Table $t) => $t->isActive())); }
}

final class SceneRepository implements TableSceneRepository
{
    /** @var array<string,array<string,TableScene>> */
    public array $items = [];
    public function forTable(string $tableId): array { return array_values($this->items[$tableId] ?? []); }
    public function find(string $tableId, string $sceneId): ?TableScene { return $this->items[$tableId][$sceneId] ?? null; }
    public function save(TableScene $scene): void { $this->items[$scene->tableId()][$scene->id()] = $scene; }
}

final class SceneIds implements TableSceneIdGenerator
{
    public int $next = 1;
    public function generate(): string { return 'scene-' . $this->next++; }
}

final class SceneClock implements TableClock
{
    public function __construct(private DateTimeImmutable $now) {}
    public function now(): DateTimeImmutable { return $this->now; }
}
