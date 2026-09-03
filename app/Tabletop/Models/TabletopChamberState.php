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
        private array $arsenals = [],
        private array $fog = [],
        private array $visionLayer = [],
        private array $integrations = [],
        private array $chamberLog = [],
        private array $footsteps = [],
        private array $scenes = [],
        private array $preparation = [],
        private array $thresholds = [],
        private array $bestiary = [],
        private ?array $session = null
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

    /** @return array<int,array<string,mixed>> */
    public function chamberLog(): array
    {
        return $this->chamberLog;
    }

    /** @return array<int,array<string,mixed>> */
    public function footsteps(): array
    {
        return $this->footsteps;
    }

    /** @return array<int,array<string,mixed>> */
    public function scenes(): array
    {
        return $this->scenes;
    }

    /** @return array<int,array<string,mixed>> */
    public function thresholds(): array
    {
        return $this->thresholds;
    }

    /** @return array<int,array<string,mixed>> */
    public function bestiary(): array
    {
        return $this->bestiary;
    }

    /** @return array<string,mixed>|null */
    public function session(): ?array
    {
        return $this->session;
    }

    /** @return array<string,mixed> */
    public function preparation(): array
    {
        return $this->preparation;
    }

    public function isPreparingScene(): bool
    {
        return ! empty($this->preparation['active']);
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

    /** @return array<string,mixed> */
    public function fog(): array
    {
        return $this->fog;
    }

    /** @return array<int,array<string,mixed>> */
    public function visionLayer(): array
    {
        return $this->visionLayer;
    }

    /** @return array<string,mixed> */
    public function integrations(): array
    {
        return $this->integrations;
    }

    public function syncRevision(): string
    {
        $shared = [
            'table' => $this->table,
            'members' => $this->members,
            'scene' => $this->scene,
            'tokens' => $this->tokens,
            'encounter' => $this->encounter,
            'vitality' => $this->vitality,
            'death_saves' => $this->deathSaves,
            'conditions' => $this->conditions,
            'battle_log' => $this->battleLog,
            'chamber_log' => $this->chamberLog,
            'combatant_states' => $this->combatantStates,
            'arsenals' => $this->arsenals,
            'fog' => $this->fog,
            'vision_layer' => $this->visionLayer,
            'footsteps' => $this->footsteps,
            'scenes' => $this->scenes,
            'preparation' => $this->preparation,
            'thresholds' => $this->thresholds,
            'dungeon_forge' => $this->integrations['dungeon_forge'] ?? [],
            'session' => $this->session,
        ];

        return hash(
            'sha256',
            (string) json_encode($shared, JSON_UNESCAPED_SLASHES)
        );
    }

    public function isDungeonMaster(): bool
    {
        return ($this->viewer['role'] ?? '') === 'dungeon-master';
    }
}
