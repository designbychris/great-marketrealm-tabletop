<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Tokens\Services;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenIdGenerator;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;

final class TokenTableRepository implements TableRepository
{
    /** @var array<string,Table> */
    public array $items = [];

    public function all(): array
    {
        return array_values($this->items);
    }

    public function find(string $id): ?Table
    {
        return $this->items[$id] ?? null;
    }

    public function save(Table $table): void
    {
        $this->items[$table->id()] = $table;
    }

    public function activeCount(): int
    {
        return count(
            array_filter(
                $this->items,
                static fn (Table $table): bool =>
                    $table->isActive()
            )
        );
    }
}

final class TokenSceneRepository implements TableSceneRepository
{
    /** @var array<string,array<string,TableScene>> */
    public array $items = [];

    public function forTable(string $tableId): array
    {
        return array_values(
            $this->items[$tableId] ?? []
        );
    }

    public function find(
        string $tableId,
        string $sceneId
    ): ?TableScene {
        return $this->items[$tableId][$sceneId]
            ?? null;
    }

    public function save(TableScene $scene): void
    {
        $this->items[$scene->tableId()]
            [$scene->id()] = $scene;
    }
}

final class TokenRepository implements TableTokenRepository
{
    /** @var array<string,TableToken> */
    public array $items = [];

    public function forScene(
        string $tableId,
        string $sceneId
    ): array {
        return array_values(
            array_filter(
                $this->items,
                static fn (TableToken $token): bool =>
                    $token->tableId() === $tableId
                    && $token->sceneId() === $sceneId
            )
        );
    }

    public function find(
        string $tableId,
        string $tokenId
    ): ?TableToken {
        $token = $this->items[$tokenId] ?? null;

        return $token !== null
            && $token->tableId() === $tableId
            ? $token
            : null;
    }

    public function save(TableToken $token): void
    {
        $this->items[$token->id()] = $token;
    }

    public function delete(string $tableId, string $tokenId): void
    {
    }
}

final class TokenIds implements TableTokenIdGenerator
{
    private int $next = 1;

    public function generate(): string
    {
        return 'token-' . $this->next++;
    }
}

final class TokenClock implements TableClock
{
    public function __construct(
        private DateTimeImmutable $now
    ) {}

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
