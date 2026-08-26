<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Movement;

use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;

final class MovementTables implements TableRepository
{
    /** @var array<string,Table> */
    public array $items = [];
    public function all(): array { return array_values($this->items); }
    public function find(string $id): ?Table { return $this->items[$id] ?? null; }
    public function save(Table $table): void { $this->items[$table->id()] = $table; }
    public function activeCount(): int { return 0; }
}

final class MovementMembers implements TableMembershipRepository
{
    /** @var array<string,array<int,TableMember>> */
    public array $items = [];
    public function forTable(string $tableId): array { return array_values($this->items[$tableId] ?? []); }
    public function find(string $tableId, int $userId): ?TableMember { return $this->items[$tableId][$userId] ?? null; }
    public function save(TableMember $member): void { $this->items[$member->tableId()][$member->userId()] = $member; }
}

final class MovementScenes implements TableSceneRepository
{
    /** @var array<string,array<string,TableScene>> */
    public array $items = [];
    public function forTable(string $tableId): array { return array_values($this->items[$tableId] ?? []); }
    public function find(string $tableId, string $sceneId): ?TableScene { return $this->items[$tableId][$sceneId] ?? null; }
    public function save(TableScene $scene): void { $this->items[$scene->tableId()][$scene->id()] = $scene; }
}

final class MovementTokens implements TableTokenRepository
{
    /** @var array<string,TableToken> */
    public array $items = [];
    public function forScene(string $tableId, string $sceneId): array { return array_values(array_filter($this->items, static fn (TableToken $token): bool => $token->tableId() === $tableId && $token->sceneId() === $sceneId)); }
    public function find(string $tableId, string $tokenId): ?TableToken
    {
        $token = $this->items[$tokenId] ?? null;
        return $token !== null && $token->tableId() === $tableId ? $token : null;
    }
    public function save(TableToken $token): void { $this->items[$token->id()] = $token; }
}
