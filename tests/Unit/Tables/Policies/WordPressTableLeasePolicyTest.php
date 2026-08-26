<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Policies;

use GreatMarketrealmTabletop\Tables\Policies\WordPressTableLeasePolicy;
use PHPUnit\Framework\TestCase;

final class WordPressTableLeasePolicyTest extends TestCase
{
    protected function setUp(): void { $GLOBALS['gmrt_test_options'] = []; }

    public function testDefaultLeaseIsFifteenMinutes(): void
    {
        self::assertSame(900, (new WordPressTableLeasePolicy())->leaseSeconds());
    }

    public function testLeaseCannotFallBelowFiveMinutes(): void
    {
        update_option('gmrt_table_lease_seconds', 30, false);
        self::assertSame(300, (new WordPressTableLeasePolicy())->leaseSeconds());
    }

    public function testDefaultHeartbeatGraceIsTwoMinutes(): void
    {
        self::assertSame(120, (new WordPressTableLeasePolicy())->heartbeatGraceSeconds());
    }

    public function testGraceCannotFallBelowOneMinute(): void
    {
        update_option('gmrt_table_heartbeat_grace_seconds', 10, false);
        self::assertSame(60, (new WordPressTableLeasePolicy())->heartbeatGraceSeconds());
    }
}
