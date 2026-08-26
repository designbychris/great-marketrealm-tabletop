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
        private array $tokens
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

    public function isDungeonMaster(): bool
    {
        return ($this->viewer['role'] ?? '') === 'dungeon-master';
    }
}
