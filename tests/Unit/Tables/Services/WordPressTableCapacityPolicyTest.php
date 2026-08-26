<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Services;

use GreatMarketrealmTabletop\Tables\Services\WordPressTableCapacityPolicy;
use PHPUnit\Framework\TestCase;

final class WordPressTableCapacityPolicyTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_options'] = [];
    }

    public function testDefaultCapacityIsTwo(): void
    {
        self::assertSame(
            2,
            (new WordPressTableCapacityPolicy())->limit()
        );
    }

    public function testConfiguredCapacityIsReadFromWordPress(): void
    {
        update_option(
            'gmrt_active_table_capacity',
            5,
            false
        );

        self::assertSame(
            5,
            (new WordPressTableCapacityPolicy())->limit()
        );
    }

    public function testCapacityCanNeverFallBelowOne(): void
    {
        update_option(
            'gmrt_active_table_capacity',
            0,
            false
        );

        self::assertSame(
            1,
            (new WordPressTableCapacityPolicy())->limit()
        );
    }
}
