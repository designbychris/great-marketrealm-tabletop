<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Encounters\Services;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterIdGenerator;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tabletop\Encounters\Models\Encounter;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;

final class EncounterTables implements TableRepository
{
    /** @var array<string,Table> */
    public array $items = [];
    public function all(): array { return array_values($this->items); }
    public function find(string $id): ?Table { return $this->items[$id] ?? null; }
    public function save(Table $table): void { $this->items[$table->id()] = $table; }
    public function activeCount(): int { return 0; }
}

final class EncounterMembers implements TableMembershipRepository
{
    /** @var array<string,array<int,TableMember>> */
    public array $items = [];
    public function forTable(string $tableId): array { return array_values($this->items[$tableId] ?? []); }
    public function find(string $tableId, int $userId): ?TableMember { return $this->items[$tableId][$userId] ?? null; }
    public function save(TableMember $member): void { $this->items[$member->tableId()][$member->userId()] = $member; }
}

final class EncounterScenes implements TableSceneRepository
{
    /** @var array<string,array<string,TableScene>> */
    public array $items = [];
    public function forTable(string $tableId): array { return array_values($this->items[$tableId] ?? []); }
    public function find(string $tableId, string $sceneId): ?TableScene { return $this->items[$tableId][$sceneId] ?? null; }
    public function save(TableScene $scene): void { $this->items[$scene->tableId()][$scene->id()] = $scene; }
}

final class EncounterTokens implements TableTokenRepository
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

    public function delete(string $tableId, string $tokenId): void
    {
    }
}

final class EncounterStore implements EncounterRepository
{
    /** @var array<string,Encounter> */
    public array $items = [];
    public function forScene(string $tableId, string $sceneId): array
    {
        return array_values(array_filter($this->items, static fn (Encounter $encounter): bool => $encounter->tableId() === $tableId && $encounter->sceneId() === $sceneId));
    }
    public function forSession(string $tableId, string $sessionId): array
    {
        return array_values(array_filter($this->items, static fn (Encounter $encounter): bool => $encounter->tableId() === $tableId && $encounter->sessionId() === $sessionId));
    }
    public function find(string $tableId, string $encounterId): ?Encounter
    {
        $encounter = $this->items[$encounterId] ?? null;
        return $encounter !== null && $encounter->tableId() === $tableId ? $encounter : null;
    }
    public function currentForScene(string $tableId, string $sceneId): ?Encounter
    {
        foreach (array_reverse($this->forScene($tableId, $sceneId)) as $encounter) {
            if (! $encounter->isEnded()) return $encounter;
        }
        return null;
    }
    public function save(Encounter $encounter): void { $this->items[$encounter->id()] = $encounter; }
}

final class EncounterIds implements EncounterIdGenerator
{
    public function generate(): string { return 'encounter-1'; }
}

final class EncounterClock implements TableClock
{
    public function __construct(private DateTimeImmutable $now) {}
    public function now(): DateTimeImmutable { return $this->now; }
}
