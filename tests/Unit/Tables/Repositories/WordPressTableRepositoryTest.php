<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Repositories;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;
use PHPUnit\Framework\TestCase;

final class WordPressTableRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_options'] = [];
    }

    public function testTablePersistsAndCanBeFound(): void
    {
        $repository = new WordPressTableRepository();
        $table = $this->table();

        $repository->save($table);

        self::assertSame(
            $table->toArray(),
            $repository->find('table-1')?->toArray()
        );
    }

    public function testActiveCountCountsOnlyActiveTables(): void
    {
        $repository = new WordPressTableRepository();

        $preparing = $this->table('table-1');
        $active = $this->table('table-2');
        $ended = $this->table('table-3');

        $active->activate(new DateTimeImmutable());
        $ended->activate(new DateTimeImmutable());
        $ended->end(new DateTimeImmutable());

        $repository->save($preparing);
        $repository->save($active);
        $repository->save($ended);

        self::assertSame(1, $repository->activeCount());
        self::assertCount(3, $repository->all());
    }

    private function table(string $id = 'table-1'): Table
    {
        return Table::prepare(
            $id,
            42,
            'Test Table',
            new DateTimeImmutable()
        );
    }
}
