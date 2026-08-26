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

    public function testActivationSeedsLeaseSafetyDefaults(): void
    {
        Activation::activate();
        self::assertSame(900, $GLOBALS['gmrt_test_options']['gmrt_table_lease_seconds']['value']);
        self::assertSame(120, $GLOBALS['gmrt_test_options']['gmrt_table_heartbeat_grace_seconds']['value']);
        self::assertSame([], $GLOBALS['gmrt_test_options']['gmrt_capacity_override_user_ids']['value']);
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
