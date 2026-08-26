<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Tokens\Repositories;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;
use PHPUnit\Framework\TestCase;

final class WordPressTableTokenRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_options'] = [];
    }

    public function testTokensArePartitionedByTableAndScene(): void
    {
        $repository = new WordPressTableTokenRepository();

        $repository->save(
            $this->token(
                'token-a',
                'table-1',
                'scene-1'
            )
        );
        $repository->save(
            $this->token(
                'token-b',
                'table-1',
                'scene-2'
            )
        );
        $repository->save(
            $this->token(
                'token-c',
                'table-2',
                'scene-1'
            )
        );

        self::assertCount(
            1,
            $repository->forScene(
                'table-1',
                'scene-1'
            )
        );
        self::assertSame(
            'token-b',
            $repository->find(
                'table-1',
                'token-b'
            )?->id()
        );
        self::assertNull(
            $repository->find(
                'table-2',
                'token-a'
            )
        );
        self::assertFalse(
            $GLOBALS['gmrt_test_options']
                ['gmrt_table_tokens']
                ['autoload']
        );
    }

    private function token(
        string $id,
        string $tableId,
        string $sceneId
    ): TableToken {
        return TableToken::create(
            $id,
            $tableId,
            $sceneId,
            'Marker',
            TableTokenType::OBJECT,
            null,
            null,
            .5,
            .5,
            1,
            1,
            TableTokenVisibility::VISIBLE,
            new DateTimeImmutable()
        );
    }
}
