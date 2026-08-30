<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Bestiary\Services;

use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberRole;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use GreatMarketrealmTabletop\Tables\Tokens\Services\TableTokenManager;
use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Contracts\ThresholdRepository;
use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Models\ThresholdMarker;
use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Models\ThresholdType;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Contracts\BestiaryRepository;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Exceptions\BestiaryDeploymentDenied;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Models\BestiaryCreature;

defined('ABSPATH') || exit;

/**
 * IV.29B — crosses the deliberate definition/instance boundary.
 *
 * Bestiary records remain reusable catalogue definitions. This service alone
 * forges Scene-owned Creature tokens from them, keeping deployment DM-only and
 * allowing the same creature definition to be summoned any number of times.
 */
final class BestiaryDeploymentManager
{
    public function __construct(
        private TableMembershipRepository $members,
        private TableSceneRepository $scenes,
        private ThresholdRepository $thresholds,
        private TableTokenRepository $tokens,
        private TableTokenManager $tokenManager,
        private BestiaryRepository $bestiary,
        private BestiaryCombatProvisioner $combatProvisioner
    ) {}

    /** @return array<int,TableToken> */
    public function deployAtPoint(
        string $tableId,
        int $viewerUserId,
        string $sceneId,
        string $creatureId,
        float $x,
        float $y,
        int $quantity = 1,
        bool $hidden = false
    ): array {
        $this->assertDungeonMaster($tableId, $viewerUserId);
        $scene = $this->requiredScene($tableId, $sceneId);
        $creature = $this->requiredCreature($creatureId);
        $scene->coordinates($x, $y);

        return $this->forgeGroup(
            $tableId,
            $scene,
            $creature,
            $x,
            $y,
            $this->quantity($quantity),
            $hidden
        );
    }

    /** @return array<int,TableToken> */
    public function deployAtMonsterThreshold(
        string $tableId,
        int $viewerUserId,
        string $sceneId,
        string $creatureId,
        int $quantity = 1,
        bool $hidden = false
    ): array {
        $this->assertDungeonMaster($tableId, $viewerUserId);
        $scene = $this->requiredScene($tableId, $sceneId);
        $creature = $this->requiredCreature($creatureId);
        $markers = array_values(array_filter(
            $this->thresholds->forScene($tableId, $sceneId),
            static fn (ThresholdMarker $marker): bool => $marker->type() === ThresholdType::MONSTER
        ));

        if ($markers === []) {
            throw new BestiaryDeploymentDenied(
                'Place a Monster Deployment Threshold on this Scene first, or choose Place on Map.'
            );
        }

        $quantity = $this->quantity($quantity);
        $created = [];
        for ($index = 0; $index < $quantity; ++$index) {
            $marker = $markers[$index % count($markers)];
            $point = $this->offsetPoint(
                $scene,
                $marker->x(),
                $marker->y(),
                intdiv($index, count($markers))
            );
            $created[] = $this->forge(
                $tableId,
                $scene,
                $creature,
                $point['x'],
                $point['y'],
                $hidden
            );
        }

        return $created;
    }

    /** @return array<int,TableToken> */
    private function forgeGroup(
        string $tableId,
        TableScene $scene,
        BestiaryCreature $creature,
        float $x,
        float $y,
        int $quantity,
        bool $hidden
    ): array {
        $created = [];
        for ($index = 0; $index < $quantity; ++$index) {
            $point = $this->offsetPoint($scene, $x, $y, $index);
            $created[] = $this->forge(
                $tableId,
                $scene,
                $creature,
                $point['x'],
                $point['y'],
                $hidden
            );
        }
        return $created;
    }

    private function forge(
        string $tableId,
        TableScene $scene,
        BestiaryCreature $creature,
        float $x,
        float $y,
        bool $hidden
    ): TableToken {
        $source = 'gmrt-bestiary:' . $creature->id();
        $ordinal = 1;
        foreach ($this->tokens->forScene($tableId, $scene->id()) as $token) {
            if (
                $token->type() === TableTokenType::CREATURE
                && (string) ($token->sourceReference() ?? '') === $source
            ) {
                ++$ordinal;
            }
        }

        $token = $this->tokenManager->place(
            $tableId,
            $scene->id(),
            $ordinal === 1 ? $creature->name() : $creature->name() . ' ' . $ordinal,
            TableTokenType::CREATURE,
            $source,
            null,
            $x,
            $y,
            1,
            1,
            $hidden ? TableTokenVisibility::HIDDEN : TableTokenVisibility::VISIBLE
        );

        $this->combatProvisioner->provision(
            $tableId,
            $token,
            $creature
        );

        return $token;
    }

    /** @return array{x:float,y:float} */
    private function offsetPoint(TableScene $scene, float $x, float $y, int $index): array
    {
        $offsets = [[0,0],[1,0],[-1,0],[0,1],[0,-1],[1,1],[-1,1],[1,-1],[-1,-1]];
        [$dx, $dy] = $offsets[$index % count($offsets)];
        $ring = intdiv($index, count($offsets)) + 1;
        if ($index === 0) {
            $ring = 0;
        }
        $stepX = $scene->width() > 0 ? $scene->gridSize() / $scene->width() : 0.02;
        $stepY = $scene->height() > 0 ? $scene->gridSize() / $scene->height() : 0.02;

        return [
            'x' => max(0, min(1, $x + ($dx * $stepX * $ring))),
            'y' => max(0, min(1, $y + ($dy * $stepY * $ring))),
        ];
    }

    private function quantity(int $quantity): int
    {
        if ($quantity < 1 || $quantity > 12) {
            throw new BestiaryDeploymentDenied('Summon between 1 and 12 creatures at a time.');
        }
        return $quantity;
    }

    private function requiredCreature(string $creatureId): BestiaryCreature
    {
        $creature = $this->bestiary->find(trim($creatureId));
        if ($creature === null) {
            throw new BestiaryDeploymentDenied('That creature is not recorded in the Keeper\'s Bestiary.');
        }
        return $creature;
    }

    private function requiredScene(string $tableId, string $sceneId): TableScene
    {
        $scene = $this->scenes->find($tableId, trim($sceneId));
        if ($scene === null) {
            throw new BestiaryDeploymentDenied('That Scene could not be found at this Table.');
        }
        return $scene;
    }

    private function assertDungeonMaster(string $tableId, int $viewerUserId): void
    {
        $member = $this->members->find($tableId, $viewerUserId);
        if (
            $member === null
            || $member->status() !== TableMemberStatus::ACTIVE
            || $member->role() !== TableMemberRole::DUNGEON_MASTER
        ) {
            throw new BestiaryDeploymentDenied('Only the Dungeon Master may summon creatures from the Bestiary.');
        }
    }
}
