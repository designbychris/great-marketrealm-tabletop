<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Campaigns;

use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Scenes\Services\TableSceneManager;
use GreatMarketrealmTabletop\Tables\Services\TableRegistry;
use RuntimeException;

defined('ABSPATH') || exit;

final class TabletopCreator
{
    public const FIRST_MAP_BLANK = 'blank';
    public const FIRST_MAP_ATLAS = 'atlas';
    public const FIRST_MAP_FORGE = 'forge';

    public function __construct(
        private TableRegistry $tables,
        private TableSceneManager $scenes
    ) {}

    public function create(
        int $dungeonMasterUserId,
        string $name,
        string $description = '',
        string $firstMap = self::FIRST_MAP_BLANK,
        string $sourceTableId = '',
        string $sourceSceneId = ''
    ): Table {
        $name = trim($name);
        $description = trim($description);
        $firstMap = trim($firstMap);
        $sourceTableId = trim($sourceTableId);
        $sourceSceneId = trim($sourceSceneId);

        if ($dungeonMasterUserId < 1) {
            throw new RuntimeException('A signed-in Dungeon Master is required to create a Tabletop.');
        }
        if ($name === '') {
            throw new RuntimeException('Give your Tabletop a campaign name before setting the Table.');
        }
        if (strlen($name) > 120) {
            throw new RuntimeException('Keep the campaign name to 120 characters or fewer.');
        }
        if (strlen($description) > 500) {
            throw new RuntimeException('Keep the campaign description to 500 characters or fewer.');
        }
        if (! in_array($firstMap, [self::FIRST_MAP_BLANK, self::FIRST_MAP_ATLAS, self::FIRST_MAP_FORGE], true)) {
            throw new RuntimeException('Choose how the first map should reach the Table.');
        }

        $atlasScene = null;
        if ($firstMap === self::FIRST_MAP_ATLAS) {
            $atlasScene = $this->atlasSceneForKeeper(
                $dungeonMasterUserId,
                $sourceTableId,
                $sourceSceneId
            );
        }

        $table = $this->tables->prepare($dungeonMasterUserId, $name, $description);
        $this->tables->activate($table->id());

        if ($atlasScene instanceof TableScene) {
            $scene = $this->scenes->cloneForTable(
                $sourceTableId,
                $atlasScene->id(),
                $table->id()
            );
        } else {
            $scene = $this->scenes->createGenerated(
                $table->id(),
                $firstMap === self::FIRST_MAP_FORGE
                    ? 'Pippin\'s Forge Workbench'
                    : 'The First Blank Page',
                960,
                640,
                GridType::SQUARE,
                64
            );
        }

        $this->scenes->activate($table->id(), $scene->id());

        return $table;
    }

    private function atlasSceneForKeeper(
        int $dungeonMasterUserId,
        string $sourceTableId,
        string $sourceSceneId
    ): TableScene {
        if ($sourceTableId === '' || $sourceSceneId === '') {
            throw new RuntimeException('Choose a saved Atlas map for the first Scene.');
        }

        $sourceTable = $this->tables->find($sourceTableId);
        if ($sourceTable === null || $sourceTable->dungeonMasterUserId() !== $dungeonMasterUserId) {
            throw new RuntimeException('That Atlas map does not belong to one of your Tables.');
        }

        foreach ($this->scenes->scenes($sourceTableId) as $scene) {
            if ($scene->id() !== $sourceSceneId) {
                continue;
            }
            if ($scene->isGeneratedSurface()) {
                throw new RuntimeException('Forged Scenes should begin through Pippin\'s Forge so their world can be built fresh.');
            }
            return $scene;
        }

        throw new RuntimeException('That saved Atlas map could not be found.');
    }
}
