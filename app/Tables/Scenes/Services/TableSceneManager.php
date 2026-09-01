<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Scenes\Services;

use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Models\TableStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneIdGenerator;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Exceptions\TableSceneException;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use RuntimeException;

defined('ABSPATH') || exit;

final class TableSceneManager
{
    public function __construct(
        private TableRepository $tables,
        private TableSceneRepository $scenes,
        private TableSceneIdGenerator $ids,
        private TableClock $clock
    ) {}

    public function create(
        string $tableId,
        string $name,
        int $mapAttachmentId,
        int $width,
        int $height,
        string $gridType,
        int $gridSize
    ): TableScene {
        $this->openTable($tableId);

        $scene = TableScene::create(
            $this->ids->generate(),
            $tableId,
            $name,
            $mapAttachmentId,
            $width,
            $height,
            $gridType,
            $gridSize,
            $this->clock->now()
        );

        $this->scenes->save($scene);
        return $scene;
    }

    public function createGenerated(
        string $tableId,
        string $name,
        int $width,
        int $height,
        string $gridType,
        int $gridSize
    ): TableScene {
        $this->openTable($tableId);

        $scene = TableScene::create(
            $this->ids->generate(),
            $tableId,
            $name,
            0,
            $width,
            $height,
            $gridType,
            $gridSize,
            $this->clock->now(),
            'generated'
        );
        $scene->calibrateGrid($gridSize, 0, 0, 13, true, $width);

        $this->scenes->save($scene);
        return $scene;
    }

    public function activate(string $tableId, string $sceneId): TableScene
    {
        $this->openTable($tableId);
        $selected = $this->requiredScene($tableId, $sceneId);

        foreach ($this->scenes->forTable($tableId) as $scene) {
            if ($scene->isActive() && $scene->id() !== $selected->id()) {
                $scene->deactivate();
                $this->scenes->save($scene);
            }
        }

        $selected->activate();
        $this->scenes->save($selected);

        return $selected;
    }

    public function active(string $tableId): ?TableScene
    {
        $this->requiredTable($tableId);

        foreach ($this->scenes->forTable($tableId) as $scene) {
            if ($scene->isActive()) {
                return $scene;
            }
        }

        return null;
    }

    /** @return array<int,TableScene> */
    public function scenes(string $tableId): array
    {
        $this->requiredTable($tableId);
        return $this->scenes->forTable($tableId);
    }

    private function openTable(string $tableId): void
    {
        $table = $this->requiredTable($tableId);

        if ($table->status() === TableStatus::ENDED) {
            throw new TableSceneException(
                'Battlemap scenes cannot be changed after a Table has ended.'
            );
        }
    }

    private function requiredTable(string $tableId): \GreatMarketrealmTabletop\Tables\Models\Table
    {
        $table = $this->tables->find($tableId);

        if ($table === null) {
            throw new RuntimeException('The requested Table could not be found.');
        }

        return $table;
    }

    private function requiredScene(string $tableId, string $sceneId): TableScene
    {
        $scene = $this->scenes->find($tableId, $sceneId);

        if ($scene === null) {
            throw new TableSceneException(
                'The requested battlemap scene could not be found at this Table.'
            );
        }

        return $scene;
    }
}
