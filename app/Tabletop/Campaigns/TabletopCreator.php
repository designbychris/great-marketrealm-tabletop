<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Campaigns;

use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Services\TableSceneManager;
use GreatMarketrealmTabletop\Tables\Services\TableRegistry;
use RuntimeException;

defined('ABSPATH') || exit;

final class TabletopCreator
{
    public function __construct(
        private TableRegistry $tables,
        private TableSceneManager $scenes
    ) {}

    public function create(int $dungeonMasterUserId, string $name, string $description = ''): Table
    {
        $name = trim($name);
        $description = trim($description);

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

        $table = $this->tables->prepare($dungeonMasterUserId, $name, $description);
        $this->tables->activate($table->id());

        $scene = $this->scenes->createGenerated(
            $table->id(),
            'The First Blank Page',
            960,
            640,
            GridType::SQUARE,
            64
        );
        $this->scenes->activate($table->id(), $scene->id());

        return $table;
    }
}
