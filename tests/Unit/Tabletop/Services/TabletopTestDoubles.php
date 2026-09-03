<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Services;

use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;

final class ChamberTables implements TableRepository
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

final class ChamberMembers implements TableMembershipRepository
{
    /** @var array<string,array<int,TableMember>> */
    public array $items = [];

    public function forTable(string $tableId): array
    {
        return array_values(
            $this->items[$tableId] ?? []
        );
    }

    public function find(
        string $tableId,
        int $userId
    ): ?TableMember {
        return $this->items[$tableId][$userId]
            ?? null;
    }

    public function save(TableMember $member): void
    {
        $this->items[$member->tableId()]
            [$member->userId()] = $member;
    }
}

final class ChamberScenes implements TableSceneRepository
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

final class ChamberTokens implements TableTokenRepository
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


final class ChamberEncounters implements \GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository
{
    /** @var array<string,\GreatMarketrealmTabletop\Tabletop\Encounters\Models\Encounter> */
    public array $items = [];

    public function forScene(string $tableId, string $sceneId): array
    {
        return array_values(array_filter(
            $this->items,
            static fn ($encounter): bool =>
                $encounter->tableId() === $tableId
                && $encounter->sceneId() === $sceneId
        ));
    }

    public function forSession(string $tableId, string $sessionId): array
    {
        return array_values(array_filter(
            $this->items,
            static fn ($encounter): bool =>
                $encounter->tableId() === $tableId
                && $encounter->sessionId() === $sessionId
        ));
    }

    public function find(string $tableId, string $encounterId): ?\GreatMarketrealmTabletop\Tabletop\Encounters\Models\Encounter
    {
        $encounter = $this->items[$encounterId] ?? null;
        return $encounter !== null && $encounter->tableId() === $tableId
            ? $encounter
            : null;
    }

    public function currentForScene(string $tableId, string $sceneId): ?\GreatMarketrealmTabletop\Tabletop\Encounters\Models\Encounter
    {
        foreach (array_reverse($this->forScene($tableId, $sceneId)) as $encounter) {
            if (! $encounter->isEnded()) {
                return $encounter;
            }
        }

        return null;
    }

    public function save(\GreatMarketrealmTabletop\Tabletop\Encounters\Models\Encounter $encounter): void
    {
        $this->items[$encounter->id()] = $encounter;
    }
}


final class ChamberVitality implements \GreatMarketrealmTabletop\Tabletop\Battle\Contracts\VitalityRepository
{
    /** @var array<string,\GreatMarketrealmTabletop\Tabletop\Battle\Models\Vitality> */
    public array $items = [];

    public function forToken(
        string $tableId,
        string $tokenId
    ): \GreatMarketrealmTabletop\Tabletop\Battle\Models\Vitality {
        return $this->items[$tokenId]
            ?? new \GreatMarketrealmTabletop\Tabletop\Battle\Models\Vitality(
                $tokenId,
                10,
                10
            );
    }

    public function save(
        string $tableId,
        \GreatMarketrealmTabletop\Tabletop\Battle\Models\Vitality $vitality
    ): void {
        $this->items[$vitality->tokenId()] = $vitality;
    }
}


final class ChamberDeathSaves implements \GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DeathSaveRepository
{
    public array $items = [];

    public function forToken(
        string $tableId,
        string $tokenId
    ): \GreatMarketrealmTabletop\Tabletop\Battle\Models\DeathSaveState {
        return $this->items[$tokenId]
            ?? new \GreatMarketrealmTabletop\Tabletop\Battle\Models\DeathSaveState(
                $tokenId
            );
    }

    public function save(
        string $tableId,
        \GreatMarketrealmTabletop\Tabletop\Battle\Models\DeathSaveState $state
    ): void {
        $this->items[$state->tokenId()] = $state;
    }
}


final class ChamberConditions implements \GreatMarketrealmTabletop\Tabletop\Conditions\Contracts\ConditionRepository
{
    /** @var array<string,array<string,\GreatMarketrealmTabletop\Tabletop\Conditions\Models\TokenCondition>> */
    public array $items = [];

    public function forToken(
        string $tableId,
        string $tokenId
    ): array {
        return array_values(
            $this->items[$tokenId] ?? []
        );
    }

    public function save(
        string $tableId,
        \GreatMarketrealmTabletop\Tabletop\Conditions\Models\TokenCondition $condition
    ): void {
        $this->items[$condition->tokenId()]
            [$condition->condition()] = $condition;
    }

    public function remove(
        string $tableId,
        string $tokenId,
        string $condition
    ): void {
        unset(
            $this->items[$tokenId][$condition]
        );
    }
}

final class ChamberFog implements \GreatMarketrealmTabletop\Tabletop\Fog\Contracts\FogOfWarRepository
{
    /** @var array<string,\GreatMarketrealmTabletop\Tabletop\Fog\Models\FogOfWarState> */
    public array $items = [];

    public function forScene(
        string $tableId,
        string $sceneId
    ): \GreatMarketrealmTabletop\Tabletop\Fog\Models\FogOfWarState {
        return $this->items[$tableId . ':' . $sceneId]
            ?? new \GreatMarketrealmTabletop\Tabletop\Fog\Models\FogOfWarState($sceneId);
    }

    public function save(
        string $tableId,
        \GreatMarketrealmTabletop\Tabletop\Fog\Models\FogOfWarState $state
    ): void {
        $this->items[$tableId . ':' . $state->sceneId()] = $state;
    }
}
