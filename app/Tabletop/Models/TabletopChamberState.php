<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Models;

defined('ABSPATH') || exit;

final class TabletopChamberState
{
    /**
     * @param array<int,array<string,mixed>> $members
     * @param array<int,array<string,mixed>> $tokens
     */
    public function __construct(
        private array $table,
        private array $viewer,
        private array $members,
        private ?array $scene,
        private array $tokens,
        private ?array $encounter = null,
        private array $vitality = [],
        private array $deathSaves = [],
        private array $conditions = [],
        private array $battleLog = [],
        private array $combatantStates = [],
        private array $arsenals = []
    ) {}

    /** @return array<string,mixed> */
    public function table(): array
    {
        return $this->table;
    }

    /** @return array<string,mixed> */
    public function viewer(): array
    {
        return $this->viewer;
    }

    /** @return array<int,array<string,mixed>> */
    public function members(): array
    {
        return $this->members;
    }

    /** @return array<string,mixed>|null */
    public function scene(): ?array
    {
        return $this->scene;
    }

    /** @return array<int,array<string,mixed>> */
    public function tokens(): array
    {
        return $this->tokens;
    }

    /** @return array<string,mixed>|null */
    public function encounter(): ?array
    {
        return $this->encounter;
    }

    /** @return array<string,array<string,mixed>> */
    public function vitality(): array
    {
        return $this->vitality;
    }

    /** @return array<string,array<string,mixed>> */
    public function deathSaves(): array
    {
        return $this->deathSaves;
    }

    /** @return array<string,array<int,array<string,mixed>>> */
    public function conditions(): array
    {
        return $this->conditions;
    }

    /** @return array<int,array<string,mixed>> */
    public function battleLog(): array
    {
        return $this->battleLog;
    }

    /** @return array<string,string> */
    public function combatantStates(): array
    {
        return $this->combatantStates;
    }

    /** @return array<string,array<string,mixed>> */
    public function arsenals(): array
    {
        return $this->arsenals;
    }

    public function isDungeonMaster(): bool
    {
        return ($this->viewer['role'] ?? '') === 'dungeon-master';
    }
}
