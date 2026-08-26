<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Scenes\Repositories;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Scenes\Repositories\WordPressTableSceneRepository;
use PHPUnit\Framework\TestCase;

final class WordPressTableSceneRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_options'] = [];
    }

    public function testScenesArePartitionedByTable(): void
    {
        $repo = new WordPressTableSceneRepository();

        foreach ([['a','table-a'], ['b','table-b']] as [$id,$table]) {
            $repo->save(TableScene::create(
                $id, $table, 'Map '.$id, 1, 100, 100,
                GridType::NONE, 0, new DateTimeImmutable()
            ));
        }

        self::assertCount(1, $repo->forTable('table-a'));
        self::assertSame('a', $repo->find('table-a', 'a')?->id());
        self::assertNull($repo->find('table-a', 'b'));
        self::assertFalse(
            $GLOBALS['gmrt_test_options']['gmrt_table_scenes']['autoload']
        );
    }
}
