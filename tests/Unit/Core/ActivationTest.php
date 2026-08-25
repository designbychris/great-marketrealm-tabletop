<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Core;

use GreatMarketrealmTabletop\Core\Activation;
use PHPUnit\Framework\TestCase;

final class ActivationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_options'] = [];
    }

    public function testActivationSeedsSchemaVersion(): void
    {
        Activation::activate();

        self::assertSame(
            '1',
            $GLOBALS['gmrt_test_options']['gmrt_schema_version']['value']
        );
        self::assertFalse(
            $GLOBALS['gmrt_test_options']['gmrt_schema_version']['autoload']
        );
    }

    public function testActivationStartsWithTwoConcurrentTableSlots(): void
    {
        Activation::activate();

        self::assertSame(
            2,
            $GLOBALS['gmrt_test_options']['gmrt_active_table_capacity']['value']
        );
        self::assertFalse(
            $GLOBALS['gmrt_test_options']['gmrt_active_table_capacity']['autoload']
        );
    }
}
