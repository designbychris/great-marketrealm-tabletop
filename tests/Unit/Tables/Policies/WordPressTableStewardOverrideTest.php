<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Policies;

use GreatMarketrealmTabletop\Tables\Policies\WordPressTableStewardOverride;
use PHPUnit\Framework\TestCase;

final class WordPressTableStewardOverrideTest extends TestCase
{
    protected function setUp(): void { $GLOBALS['gmrt_test_options'] = []; }

    public function testOverrideDefaultsToNobody(): void
    {
        self::assertFalse((new WordPressTableStewardOverride())->mayBypassCapacity(42));
    }

    public function testConfiguredDungeonMasterMayBypassCapacity(): void
    {
        update_option('gmrt_capacity_override_user_ids', [42], false);
        self::assertTrue((new WordPressTableStewardOverride())->mayBypassCapacity(42));
    }
}
