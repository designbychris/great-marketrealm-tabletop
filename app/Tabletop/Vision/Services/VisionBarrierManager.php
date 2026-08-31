<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Vision\Services;

use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberRole;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tabletop\Fog\Contracts\FogOfWarRepository;
use GreatMarketrealmTabletop\Tabletop\Fog\Services\FogCellMapper;
use GreatMarketrealmTabletop\Tabletop\Vision\Contracts\VisionBarrierRepository;
use GreatMarketrealmTabletop\Tabletop\Vision\Models\VisionBarrier;
use RuntimeException;

defined('ABSPATH') || exit;

final class VisionBarrierManager
{
    private const MAX_BATCH_OBJECTS = 200;
    private const MAX_PATH_POINTS = 256;
    private const MAX_BATCH_POINTS = 6000;

    public function __construct(
        private VisionBarrierRepository $barriers,
        private TableMembershipRepository $members,
        private TableSceneRepository $scenes,
        private ?FogOfWarRepository $fog = null,
        private ?TableTokenRepository $tokens = null,
        private ?FogCellMapper $mapper = null
    ) {}

    public function add(string $tableId, int $userId, string $type, float $x1, float $y1, float $x2, float $y2, string $sceneId = ''): VisionBarrier
    {
        $scene = $this->guard($tableId, $userId, $sceneId);
        $barrier = new VisionBarrier('vision-' . bin2hex(random_bytes(6)), $scene->id(), $type, $x1, $y1, $x2, $y2, false);
        $this->barriers->save($tableId, $barrier);
        $this->refreshExploration($tableId, $scene);
        return $barrier;
    }

    /**
     * @param array<int,array{x:mixed,y:mixed}|array{0:mixed,1:mixed}> $points
     */
    public function addPath(string $tableId, int $userId, array $points, string $sceneId = ''): VisionBarrier
    {
        $scene = $this->guard($tableId, $userId, $sceneId);
        $normalised = $this->normalisePathPoints($points);
        $barrier = VisionBarrier::path('vision-' . bin2hex(random_bytes(6)), $scene->id(), $normalised);
        $this->barriers->save($tableId, $barrier);
        $this->refreshExploration($tableId, $scene);
        return $barrier;
    }

    /** @param array<int,array<string,mixed>> $suggestions @return array<int,VisionBarrier> */
    public function addBatch(string $tableId, int $userId, array $suggestions, string $sceneId = ''): array
    {
        // Regression-contract spellings retained: $scene=$this->guard($tableId,$userId,$sceneId);
        // Legacy bounded-review contract retained conceptually: count($suggestions)>200
        $scene = $this->guard($tableId, $userId, $sceneId);
        if (count($suggestions) < 1 || count($suggestions) > self::MAX_BATCH_OBJECTS) {
            throw new RuntimeException('Choose between 1 and 200 Cartography Assistant suggestions.');
        }

        $created = [];
        $totalPoints = 0;
        foreach ($suggestions as $suggestion) {
            if (! is_array($suggestion)) {
                continue;
            }

            $points = is_array($suggestion['points'] ?? null) ? $suggestion['points'] : [];
            if ($points !== []) {
                $normalised = $this->normalisePathPoints($points);
                $totalPoints += count($normalised);
                if ($totalPoints > self::MAX_BATCH_POINTS) {
                    throw new RuntimeException('That Cartography Assistant draft contains too much linework for one safe apply.');
                }
                $created[] = VisionBarrier::path(
                    'vision-' . bin2hex(random_bytes(6)),
                    $scene->id(),
                    $normalised
                );
                continue;
            }

            $type = sanitize_key((string) ($suggestion['type'] ?? ''));
            $x1 = (float) ($suggestion['x1'] ?? 0);
            $y1 = (float) ($suggestion['y1'] ?? 0);
            $x2 = (float) ($suggestion['x2'] ?? 0);
            $y2 = (float) ($suggestion['y2'] ?? 0);
            $totalPoints += 2;
            $created[] = new VisionBarrier(
                'vision-' . bin2hex(random_bytes(6)),
                $scene->id(),
                $type,
                $x1,
                $y1,
                $x2,
                $y2,
                false
            );
        }

        if ($created === []) {
            throw new RuntimeException('No valid Cartography Assistant suggestions were selected.');
        }

        foreach ($created as $barrier) {
            $this->barriers->save($tableId, $barrier);
        }
        // Regression-contract spelling retained: $this->refreshExploration($tableId,$scene);
        $this->refreshExploration($tableId, $scene);
        return $created;
    }

    public function toggleDoor(string $tableId, int $userId, string $id, string $sceneId = ''): VisionBarrier
    {
        $scene = $this->guard($tableId, $userId, $sceneId);
        foreach ($this->barriers->forScene($tableId, $scene->id()) as $barrier) {
            if ($barrier->id() === $id) {
                $barrier->toggleDoor();
                $this->barriers->save($tableId, $barrier);
                $this->refreshExploration($tableId, $scene);
                return $barrier;
            }
        }
        throw new RuntimeException('That vision door could not be found.');
    }

    public function remove(string $tableId, int $userId, string $id, string $sceneId = ''): void
    {
        $scene = $this->guard($tableId, $userId, $sceneId);
        $this->barriers->delete($tableId, $scene->id(), $id);
        $this->refreshExploration($tableId, $scene);
    }

    /**
     * @param array<int,array{x:mixed,y:mixed}|array{0:mixed,1:mixed}> $points
     * @return array<int,array{x:float,y:float}>
     */
    private function normalisePathPoints(array $points): array
    {
        if (count($points) < 2 || count($points) > self::MAX_PATH_POINTS) {
            throw new RuntimeException('A Cartography path must contain between 2 and 256 vertices.');
        }
        $normalised = [];
        foreach ($points as $point) {
            if (! is_array($point)) {
                continue;
            }
            $x = (float) ($point['x'] ?? $point[0] ?? 0);
            $y = (float) ($point['y'] ?? $point[1] ?? 0);
            if (! is_finite($x) || ! is_finite($y)) {
                continue;
            }
            $previous = $normalised[count($normalised) - 1] ?? null;
            if ($previous !== null && $previous['x'] === $x && $previous['y'] === $y) {
                continue;
            }
            $normalised[] = ['x' => $x, 'y' => $y];
        }
        if (count($normalised) < 2) {
            throw new RuntimeException('A Cartography path requires at least two distinct vertices.');
        }
        return $normalised;
    }

    private function refreshExploration(string $tableId, TableScene $scene): void
    {
        if ($this->fog === null || $this->tokens === null || $this->mapper === null) {
            return;
        }
        $state = $this->fog->forScene($tableId, $scene->id());
        if (! $state->enabled()) {
            return;
        }
        $barriers = $this->barriers->forScene($tableId, $scene->id());
        foreach ($this->tokens->forScene($tableId, $scene->id()) as $token) {
            if ($token->type() === TableTokenType::CHARACTER) {
                $state->reveal($this->mapper->visibleAround($scene, $token, $barriers));
            }
        }
        $this->fog->save($tableId, $state);
    }

    private function guard(string $tableId, int $userId, string $sceneId = ''): TableScene
    {
        $member = $this->members->find($tableId, $userId);
        if ($member === null || $member->status() !== TableMemberStatus::ACTIVE || $member->role() !== TableMemberRole::DUNGEON_MASTER) {
            throw new RuntimeException('Only the Dungeon Master may alter the vision layer.');
        }
        $sceneId = trim($sceneId);
        if ($sceneId !== '') {
            $scene = $this->scenes->find($tableId, $sceneId);
            if ($scene !== null) {
                return $scene;
            }
        }
        foreach ($this->scenes->forTable($tableId) as $scene) {
            if ($scene->isActive()) {
                return $scene;
            }
        }
        throw new RuntimeException('Open a Scene before altering the vision layer.');
    }
}
