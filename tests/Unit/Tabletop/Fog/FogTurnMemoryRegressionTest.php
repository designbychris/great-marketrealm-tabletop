<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Fog;

use GreatMarketrealmTabletop\Tabletop\Fog\Models\FogOfWarState;
use GreatMarketrealmTabletop\Tabletop\Fog\Repositories\WordPressFogOfWarRepository;
use PHPUnit\Framework\TestCase;

final class FogTurnMemoryRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_options'] = [];
    }

    public function testExplorationSurvivesUnrelatedEncounterPersistenceAndReload(): void
    {
        $repository = new WordPressFogOfWarRepository();
        $state = new FogOfWarState(
            'scene-veil',
            true,
            ['3:4', '4:4', '5:4']
        );

        $repository->save('table-market', $state);

        update_option(
            'gmrt_encounters',
            ['table-market' => ['revision' => 9]],
            false
        );

        $reloaded = (new WordPressFogOfWarRepository())
            ->forScene('table-market', 'scene-veil');

        self::assertTrue($reloaded->enabled());
        self::assertSame(
            ['3:4', '4:4', '5:4'],
            $reloaded->explored()
        );
    }

    public function testOnlyExplicitClearRemovesExploration(): void
    {
        $repository = new WordPressFogOfWarRepository();
        $state = new FogOfWarState(
            'scene-veil',
            true,
            ['1:1', '1:2']
        );
        $repository->save('table-market', $state);

        $reloaded = $repository->forScene(
            'table-market',
            'scene-veil'
        );
        $reloaded->clear();
        $repository->save('table-market', $reloaded);

        self::assertSame(
            [],
            (new WordPressFogOfWarRepository())
                ->forScene('table-market', 'scene-veil')
                ->explored()
        );
    }
}
