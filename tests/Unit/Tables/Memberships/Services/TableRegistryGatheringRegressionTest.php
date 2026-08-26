<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Memberships\Services;

use PHPUnit\Framework\TestCase;

final class TableRegistryGatheringRegressionTest extends TestCase
{
    public function testTableRegistryCanSeatDungeonMasterOnPreparation(): void
    {
        $root = dirname(__DIR__, 5);
        $source = file_get_contents(
            $root . '/app/Tables/Services/TableRegistry.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString(
            '$this->gathering->seatDungeonMaster($table);',
            $source
        );
    }

    public function testRegistryFactoryWiresMembershipRepository(): void
    {
        $root = dirname(__DIR__, 5);
        $source = file_get_contents(
            $root . '/app/Tables/Services/TableRegistryFactory.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString(
            'WordPressTableMembershipRepository',
            $source
        );
        self::assertStringContainsString(
            'new TableGathering(',
            $source
        );
    }
}
